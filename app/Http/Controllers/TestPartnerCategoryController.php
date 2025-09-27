<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PartnerCategory;
use Spatie\MediaLibrary\MediaCollections\Models\Media as MediaModel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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
            'image' => 'nullable|image|max:5120',       // single file
            'photos.*' => 'nullable|image|max:5120',     // multiple files
        ]);

        // create model
        $category = PartnerCategory::create([
            'name' => $validated['name'],
        ]);

        // add single file nếu có (collection 'images')
        if ($request->hasFile('image')) {
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
                            'uploaded_by' => auth()->id() ?? null,
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
                \Log::error('upload photos failed', ['error' => $e->getMessage()]);
                // tuỳ context, bạn có thể throw, return error json, hoặc redirect with errors
                return back()->withErrors(['photos' => 'upload failed: ' . $e->getMessage()]);
            }

            // nếu request ajax / expects json, trả json để frontend cập nhật UI mà ko reload
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'media' => $addedMedia,
                ]);
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
}
