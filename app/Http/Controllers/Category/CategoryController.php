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

            // Use cached tree for better performance
            $parent = PartnerCategory::getTree();
            
            // Load additional media relationships
            $parent->load(['media', 'children.media']);
            
            // Filter children if search term is provided
            if ($search !== '') {
                $parent->each(function ($category) use ($search) {
                    $category->setRelation('children', 
                        $category->children->filter(function ($child) use ($search) {
                            return stripos($child->name, $search) !== false;
                        })->sortBy('name')->values()
                    );
                });
            } else {
                $parent->each(function ($category) {
                    $category->setRelation('children', 
                        $category->children->sortBy('name')->values()
                    );
                });
            }

            // Chuẩn hóa dữ liệu gửi sang Inertia (tránh gửi cả model kèm thuộc tính không cần)
            $expireAt = now()->addMinutes(3600);

            $payload = [
                'parent' => [],
                'children' => $parent->map(function (PartnerCategory $cat) use ($expireAt) {
                    return [
                        'id' => $cat->id,
                        'name' => $cat->name,
                        'slug' => $cat->slug,
                        'description' => $cat->description,
                        'partner_categories' => $cat->children->map(function ($pc) use ($expireAt) {
                            return [
                                'id' => $pc->id,
                                'name' => $pc->name,
                                'slug' => $pc->slug,
                                'min_price' => $pc->min_price,
                                'max_price' => $pc->max_price,
                                // Ảnh lấy từ media library (collection 'images') nếu có
                                'image' => $this->getTemporaryImageUrl($pc, $expireAt),
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
            // dd("Slug này chưa tích hợp");
        }
    }

    private function getTemporaryImageUrl($model, $expireAt)
    {
        if (! method_exists($model, 'getFirstTemporaryUrl')) {
            return null;
        }

        try {
            return $model->getFirstTemporaryUrl($expireAt, 'images');
        } catch (\Throwable $e) {
            return method_exists($model, 'getFirstMediaUrl')
                ? $model->getFirstMediaUrl('images')
                : null;
        }
    }
}
