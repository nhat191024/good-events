<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\PartnerCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * Hiển thị trang "Danh mục cha" (parent category + các danh mục con + items)
     * - slug là slug của category parent (parent_id = null)
     * - q (GET) là từ khóa tìm kiếm partner_categories theo tên
     */
    public function showParent(Request $request, string $slug)
    {
        if ($slug == 'su-kien') {

            $search = trim((string) $request->query('q', ''));

            // Lấy toàn bộ danh mục con của parent
            // và eager load partner_categories (lọc theo q nếu có)
            $parent = PartnerCategory::query()
                ->with(['children' => function ($q) use ($search) {
                    if ($search !== '') {
                        $q->where('name', 'like', "%{$search}%");
                    }
                    $q->orderBy('name');
                }])->with([
                    'media',
                    'children' => function ($query) {
                        $query->orderBy('min_price')
                            ->limit(8)
                            ->with('media');
                    }
                ])
                ->whereNull('parent_id')
                ->orderBy('name')
                ->get();

            // Chuẩn hóa dữ liệu gửi sang Inertia (tránh gửi cả model kèm thuộc tính không cần)
            $payload = [
                'parent' => [],
                'children' => $parent->map(function (PartnerCategory $cat) {
                    return [
                        'id' => $cat->id,
                        'name' => $cat->name,
                        'slug' => $cat->slug,
                        'description' => $cat->description,
                        'partner_categories' => $cat->children->map(function ($pc) {
                            return [
                                'id' => $pc->id,
                                'name' => $pc->name,
                                'slug' => $pc->slug,
                                'min_price' => $pc->min_price,
                                'max_price' => $pc->max_price,
                                // Ảnh lấy từ media library (collection 'images') nếu có
                                'image' => $pc->getFirstTemporaryUrl(now()->addMinutes(5), 'images'),
                            ];
                        }),
                    ];
                }),
                'filters' => [
                    'q' => $search,
                ],
            ];

            return Inertia::render('categories/Parent', $payload);
        } else {
            dd("Slug này chưa tích hợp");
        }
    }
}
