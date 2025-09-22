<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Models\Category;
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
        $parent = Category::query()
            ->whereNull('parent_id')
            ->where('slug', $slug)
            ->firstOrFail();

        $search = trim((string) $request->query('q', ''));

        // Lấy toàn bộ danh mục con của parent
        // và eager load partner_categories (lọc theo q nếu có)
        $children = Category::query()
            ->with(['partnerCategories' => function ($q) use ($search) {
                if ($search !== '') {
                    $q->where('name', 'like', "%{$search}%");
                }
                $q->orderBy('name');
            }])
            ->where('parent_id', $parent->id)
            ->orderBy('name')
            ->get();

        // Chuẩn hóa dữ liệu gửi sang Inertia (tránh gửi cả model kèm thuộc tính không cần)
        $payload = [
            'parent' => [
                'id'   => $parent->id,
                'name' => $parent->name,
                'slug' => $parent->slug,
                'description' => $parent->description,
            ],
            'children' => $children->map(function (Category $cat) {
                return [
                    'id'   => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'description' => $cat->description,
                    'partner_categories' => $cat->partnerCategories->map(function ($pc) {
                        return [
                            'id' => $pc->id,
                            'name' => $pc->name,
                            'slug' => $pc->slug,
                            'min_price' => $pc->min_price,
                            'max_price' => $pc->max_price,
                            // Ảnh dùng placeholder theo yêu cầu leader
                            'image' => null,
                        ];
                    }),
                ];
            }),
            'filters' => [
                'q' => $search,
            ],
        ];

        return Inertia::render('categories/Parent', $payload);
    }
}
