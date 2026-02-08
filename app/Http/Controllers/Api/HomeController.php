<?php

namespace App\Http\Controllers\Api;

use App\Enum\PartnerBillDetailStatus;
use App\Enum\PartnerBillStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BlogResource;
use App\Http\Resources\Api\UserResource;
use App\Models\Banner;
use App\Models\Blog;
use App\Models\PartnerBill;
use App\Models\PartnerBillDetail;
use App\Models\PartnerCategory;
use App\Settings\AppSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class HomeController extends Controller
{
    public const int INITIAL_EVENT_CATEGORY_LIMIT = 8;
    public const int EVENT_CATEGORY_BATCH_SIZE = 4;
    public const int CHILD_CATEGORY_LIMIT = 99;

    private ?int $parentCategoryCount = null;

    /**
     * GET /api/event/home
     *
     * Response: event_categories, partner_categories, pagination, blogs, settings,
     * and when authenticated: user, is_has_new_noti, current_money, pending_orders,
     * confirmed_orders, pending_partners.
     *
     * @param Request $request
     * @param AppSettings $settings
     * @return \Illuminate\Http\JsonResponse
     */
    public function eventHome(Request $request, AppSettings $settings)
    {
        $user = $request->user();

        $initialData = $this->fetchEventCategories(self::INITIAL_EVENT_CATEGORY_LIMIT, 0);

        $blogs = Blog::query()
            ->select([
                'id',
                'category_id',
                'user_id',
                'title',
                'slug',
                'content',
                'video_url',
                'location_id',
                'latitude',
                'longitude',
                'address',
                'max_people',
                'type',
                'order',
                'created_at',
                'updated_at',
            ])
            ->with([
                'category:id,name,slug,parent_id',
                'category.parent:id,name,slug',
                'author:id,name',
                'media',
                'tags',
                'location:id,name,parent_id,type',
                'location.province:id,name,parent_id',
            ])
            ->latest('created_at')
            ->take(6)
            ->get();

        $payload = [
            'event_categories' => $initialData['event_categories'],
            'partner_categories' => $initialData['partner_categories'],
            'pagination' => [
                'total' => $this->getParentCategoryCount(),
                'initial_limit' => self::INITIAL_EVENT_CATEGORY_LIMIT,
                'batch_size' => self::EVENT_CATEGORY_BATCH_SIZE,
                'child_limit' => self::CHILD_CATEGORY_LIMIT,
            ],
            'blogs' => BlogResource::collection($blogs),
        ];

        if ($user) {
            $user->loadMissing('partnerProfile');
            $pendingOrders = PartnerBill::query()
                ->where('client_id', $user->id)
                ->where('status', PartnerBillStatus::PENDING)
                ->count();

            $confirmedOrders = PartnerBill::query()
                ->where('client_id', $user->id)
                ->whereIn('status', [PartnerBillStatus::CONFIRMED, PartnerBillStatus::IN_JOB])
                ->count();

            $pendingPartners = PartnerBillDetail::query()
                ->where('status', PartnerBillDetailStatus::NEW)
                ->whereHas('partnerBill', fn ($query) => $query->where('client_id', $user->id))
                ->count();

            $payload = array_merge($payload, [
                'is_has_new_noti' => $user->unreadNotifications()->count() > 0,
                'user' => new UserResource($user),
                'current_money' => $user->balanceInt ?? 0,
                'pending_orders' => $pendingOrders,
                'confirmed_orders' => $confirmedOrders,
                'pending_partners' => $pendingPartners,
            ]);
        } else {
            $payload = array_merge($payload, [
                'is_has_new_noti' => false,
                'user' => null,
                'current_money' => 0,
                'pending_orders' => 0,
                'confirmed_orders' => 0,
                'pending_partners' => 0,
            ]);
        }

        $payload['settings'] = [
            'app_name' => $settings->app_name,
            'hero_title' => $settings->app_partner_title,
            'banner_images' => $this->bannerImages('partner'),
            'mobile_banner_images' => $this->bannerImages('mobile_partner'),
        ];

        return response()->json($payload);
    }

    /**
     * GET /api/event/home/categories
     *
     * Query: offset, limit
     * Response: { event_categories, partner_categories, has_more }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loadMoreCategories(Request $request)
    {
        $validated = $request->validate([
            'offset' => 'nullable|integer|min:0',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $offset = (int) ($validated['offset'] ?? 0);
        $limit = (int) ($validated['limit'] ?? self::EVENT_CATEGORY_BATCH_SIZE);
        $data = $this->fetchEventCategories($limit, $offset);

        return response()->json([
            'event_categories' => $data['event_categories'],
            'partner_categories' => $data['partner_categories'],
            'has_more' => ($offset + $limit) < $this->getParentCategoryCount(),
        ]);
    }

    /**
     * GET /api/event/home/children
     *
     * Query: category_slug, offset, limit
     * Response: { children, has_more, total }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loadMoreChildren(Request $request)
    {
        $validated = $request->validate([
            'category_slug' => 'required|string|exists:partner_categories,slug',
            'offset' => 'nullable|integer|min:0',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $categories = PartnerCategory::getTree();
        $category = $categories->where('slug', $validated['category_slug'])->firstOrFail();

        $offset = (int) ($validated['offset'] ?? 0);
        $limit = (int) ($validated['limit'] ?? self::CHILD_CATEGORY_LIMIT);

        $childrenQuery = $category->children()->orderBy('order', 'asc');
        $totalChildren = (clone $childrenQuery)->count();

        $children = $childrenQuery
            ->skip($offset)
            ->take($limit)
            ->with('media')
            ->get()
            ->map(function ($child) {
                return [
                    'id' => $child->id,
                    'name' => $child->name,
                    'slug' => $child->slug,
                    'description' => $child->description,
                    'min_price' => $child->min_price,
                    'max_price' => $child->max_price,
                    'image' => $this->getImageUrl($child),
                ];
            });

        $hasMore = ($offset + $limit) < $totalChildren;

        return response()->json([
            'children' => $children->values(),
            'has_more' => $hasMore,
            'total' => $totalChildren,
        ]);
    }

    /**
     * GET /api/event/home/search
     *
     * Query: q
     * Response: { event_categories, partner_categories, has_more?, filters? }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string|min:1|max:100',
        ]);

        $term = trim($validated['q']);
        if ($term === '') {
            return response()->json([
                'event_categories' => [],
                'partner_categories' => [],
            ]);
        }

        $allCategories = PartnerCategory::getTree();

        $eventCategories = $allCategories->filter(function ($category) use ($term) {
            if (stripos($category->name, $term) !== false || stripos($category->slug, $term) !== false) {
                return true;
            }

            return $category->children->contains(function ($child) use ($term) {
                return stripos($child->name, $term) !== false || stripos($child->slug, $term) !== false;
            });
        })->take(self::INITIAL_EVENT_CATEGORY_LIMIT)->values();

        $eventCategories->load('media');
        $eventCategories->each(function ($category) use ($term) {
            $category->setRelation(
                'children',
                $category->children
                    ->filter(function ($child) use ($term) {
                        return stripos($child->name, $term) !== false || stripos($child->slug, $term) !== false;
                    })
                    ->take(self::CHILD_CATEGORY_LIMIT)
                    ->values()
            );
            $category->children->load('media');
            $category->setAttribute('total_children_count', $category->children->count());
        });

        return response()->json($this->transformEventCategoryResponse($eventCategories));
    }

    private function fetchEventCategories(int $limit, int $offset): array
    {
        $allCategories = PartnerCategory::getTree();
        $eventCategories = $allCategories->slice($offset, $limit)->values();

        $eventCategories->load('media');
        $eventCategories->each(function ($category) {
            $category->setRelation(
                'children',
                $category->children->take(self::CHILD_CATEGORY_LIMIT)->values()
            );
            $category->children->load('media');
            $category->setAttribute('total_children_count', $category->children->count());
        });

        return $this->transformEventCategoryResponse($eventCategories);
    }

    private function transformEventCategoryResponse(Collection $eventCategories): array
    {
        $eventPayload = $eventCategories->values()->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'min_price' => $category->min_price,
                'max_price' => $category->max_price,
                'image' => $this->getImageUrl($category),
                'total_children_count' => $category->total_children_count ?? $category->children->count(),
            ];
        });

        $partnerCategories = [];
        foreach ($eventCategories as $category) {
            $partnerCategories[$category->id] = $category->children
                ->values()
                ->map(function ($child) {
                    return [
                        'id' => $child->id,
                        'name' => $child->name,
                        'slug' => $child->slug,
                        'description' => $child->description,
                        'min_price' => $child->min_price,
                        'max_price' => $child->max_price,
                        'image' => $this->getImageUrl($child),
                    ];
                })
                ->values();
        }

        return [
            'event_categories' => $eventPayload,
            'partner_categories' => $partnerCategories,
        ];
    }

    private function getParentCategoryCount(): int
    {
        if ($this->parentCategoryCount !== null) {
            return $this->parentCategoryCount;
        }

        $this->parentCategoryCount = PartnerCategory::getTree()->count();

        return $this->parentCategoryCount;
    }

    private function getImageUrl($model): ?string
    {
        if (!method_exists($model, 'getFirstMediaUrl')) {
            return null;
        }

        return $model->getFirstMediaUrl('images', 'thumb');
    }

    /**
     * @return array<int, string>
     */
    private function bannerImages(string $type): array
    {
        $banner = Banner::where('type', $type)->first();
        if (!$banner) {
            return [];
        }

        return $banner->getMedia('banners')
            ->map(function ($media) {
                try {
                    $url = $media->getUrl('thumb');
                } catch (\Throwable $e) {
                    $url = $media->getFullUrl();
                }

                return $url ?: null;
            })
            ->filter()
            ->values()
            ->all();
    }
}
