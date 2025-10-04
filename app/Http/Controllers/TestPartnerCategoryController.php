<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PartnerCategory;
use Spatie\MediaLibrary\MediaCollections\Models\Media as MediaModel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TestPartnerCategoryController extends Controller
{
    public function create()
    {
        // lấy danh sách categories kèm media (collection 'images')
        $categories = PartnerCategory::with('media')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($cat) {
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'created_at' => optional($cat->created_at)->toDateTimeString(),
                    'media' => $cat->getMedia('images')->map(function ($m) {
                        return [
                            'id' => $m->id,
                            'name' => $m->name,
                            'file_name' => $m->file_name,
                            'mime_type' => $m->mime_type,
                            'size' => $m->size,
                            // use original full url only
                            'url' => $m->getFullUrl(),
                            'created_at' => optional($m->created_at)->toDateTimeString(),
                        ];
                    })->values(),
                ];
            });

        return Inertia::render('Upload', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        // validate cơ bản (files sẽ được validate như bình thường)
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // validate as file and size first; we'll do a real image check below
            'image' => 'nullable|file|max:5120',       // single file up to 5MB
            'photos.*' => 'nullable|file|max:5120',     // multiple files up to 5MB each
        ]);

        // create model
        $category = PartnerCategory::create([
            'name' => $validated['name'],
        ]);

        // add single file nếu có (collection 'images') with strict image check
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if (!@getimagesize($file->getPathname())) {
                return back()->withErrors(['image' => 'File tải lên không phải là ảnh hợp lệ.']);
            }
            $category->addMediaFromRequest('image')->toMediaCollection('images');
        }

        // add multiple files nếu có (input name = photos[])
        if ($request->hasFile('photos')) {
            // optional: nếu frontend gửi data meta cho từng file (ví dụ captions)
            // send photos_meta as array: photos_meta[0][caption], photos_meta[1][caption], ...
            $photosMeta = $request->input('photos_meta', []); // array hoặc []

            $files = $request->file('photos');
            $addedMedia = [];

            // wrap in transaction: nếu 1 file fail, rollback tất cả (tuỳ requirement)
            DB::beginTransaction();
            try {
                foreach ($files as $index => $file) {
                    if (!@getimagesize($file->getPathname())) {
                        throw new \RuntimeException('Một file trong photos không phải là ảnh hợp lệ.');
                    }
                    // optional: lấy meta tương ứng với file (nếu gửi kèm)
                    $meta = $photosMeta[$index] ?? [];

                    // make a safer unique file name
                    $fileName = now()->format('YmdHis') . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();

                    // add media: bạn có thể ->nonQueued() nếu muốn conversion chạy ngay (dev)
                    $media = $category
                        ->addMedia($file) // add bằng UploadedFile instance
                        ->usingFileName($fileName) // đặt tên file trên storage
                        ->withCustomProperties([
                            'original_name' => $file->getClientOriginalName(),
                            'caption' => $meta['caption'] ?? null,
                            'uploaded_by' => Auth::id() ?? null,
                            'sort_order' => $index,
                        ])
                        ->withResponsiveImages() // tạo responsive images nếu config bật
                        // ->nonQueued() // bỏ comment nếu muốn tạo conversion ngay, ko queue
                        ->toMediaCollection('images'); // tên collection bạn muốn

                    // collect thông tin để trả về cho frontend (dùng Inertia/json)
                    $addedMedia[] = [
                        'id' => $media->id,
                        'file_name' => $media->file_name,
                        'original_name' => $media->getCustomProperty('original_name'),
                        'caption' => $media->getCustomProperty('caption'),
                        'size' => $media->size,
                        'mime_type' => $media->mime_type,
                        'url' => $media->getFullUrl(), // trả về url gốc
                        'created_at' => optional($media->created_at)->toDateTimeString(),
                    ];
                }

                DB::commit();
            } catch (\Throwable $e) {
                // rollback nếu có lỗi, log để debug
                DB::rollBack();
                Log::error('upload photos failed', ['error' => $e->getMessage()]);
                // tuỳ context, bạn có thể throw, return error json, hoặc redirect with errors
                return back()->withErrors(['photos' => 'upload failed: ' . $e->getMessage()]);
            }

            // nếu dùng Inertia và redirect mà muốn truyền media mới để render ngay
            // nhớ: Inertia sẽ kèm flash session, hoặc bạn có thể Inertia::render lại page với props cập nhật
            return redirect()->back()->with('success', 'uploaded')->with('added_media', $addedMedia);
        }

        // redirect về list hoặc trang cần thiết
        return redirect()->route('partner-categories.create')
            ->with('success', 'category created');
    }

    // delete media bằng id
    public function destroyMedia($id)
    {
        $media = MediaModel::findOrFail($id);
        $media->delete();

        // trả json để frontend xử lý async
        return response()->json(['success' => true]);
    }

    /**
     * Upload media to an existing PartnerCategory
     */
    public function addMedia(Request $request, $id)
    {
        $validated = $request->validate([
            'image' => 'nullable|file|max:5120',
            'photos.*' => 'nullable|file|max:5120',
        ]);

        $category = PartnerCategory::findOrFail($id);

        // add single file nếu có (collection 'images')
        if ($request->hasFile('image')) {
            $category->addMediaFromRequest('image')->toMediaCollection('images');
        }

        $addedMedia = [];

        if ($request->hasFile('photos')) {
            $files = $request->file('photos');
            DB::beginTransaction();
            try {
                foreach ($files as $index => $file) {
                    if (!@getimagesize($file->getPathname())) {
                        throw new \RuntimeException('Một file trong photos không phải là ảnh hợp lệ.');
                    }
                    $fileName = now()->format('YmdHis') . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
                    $media = $category
                        ->addMedia($file)
                        ->usingFileName($fileName)
                        ->withCustomProperties([
                            'original_name' => $file->getClientOriginalName(),
                            'uploaded_by' => Auth::id() ?? null,
                            'sort_order' => $index,
                        ])
                        ->withResponsiveImages()
                        ->toMediaCollection('images');

                    $addedMedia[] = [
                        'id' => $media->id,
                        'file_name' => $media->file_name,
                        'original_name' => $media->getCustomProperty('original_name'),
                        'size' => $media->size,
                        'mime_type' => $media->mime_type,
                        'url' => $media->getFullUrl(),
                        'created_at' => optional($media->created_at)->toDateTimeString(),
                    ];
                }
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('add media failed', ['error' => $e->getMessage()]);
                return back()->withErrors(['photos' => 'upload failed: ' . $e->getMessage()]);
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'media' => $addedMedia,
            ]);
        }

        return redirect()->back()->with('success', 'media added')->with('added_media', $addedMedia);
    }
}
