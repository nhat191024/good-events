<?php

namespace App\Http\Controllers\Api\Partner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Partner\PartnerReviewResource;
use App\Models\Partner;
use App\Models\PartnerBill;
use App\Models\User;
use Codebyray\ReviewRateable\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    private const int DEFAULT_PER_PAGE = 10;
    private const int MAX_PER_PAGE = 10;

    /**
     * GET /api/partner/reviews
     *
     * Query: page, per_page
     * Response: { reviews: PartnerReviewResource[] (paginated) }
     */
    public function index(Request $request): JsonResponse
    {
        $partnerId = (int) $request->user()->id;
        $page = max(1, (int) $request->query('page', 1));
        $perPage = $this->resolvePerPage($request);

        $paginator = Review::query()
            ->where('reviewable_type', Partner::class)
            ->where('reviewable_id', $partnerId)
            ->whereNotNull('partner_bill_id')
            ->with('ratings')
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);

        $reviews = $paginator->getCollection();

        $bills = PartnerBill::query()
            ->where('partner_id', $partnerId)
            ->whereIn('id', $reviews->pluck('partner_bill_id')->filter()->unique())
            ->with([
                'category' => fn($query) => $query->withTrashed(),
                'event',
                'client:id,name,avatar',
            ])
            ->get()
            ->keyBy('id');

        $paginator->setCollection(
            $reviews
                ->filter(fn(Review $review): bool => $bills->has($review->partner_bill_id))
                ->values()
        );

        return response()->json([
            'reviews' => [
                'data' => $paginator->getCollection()
                    ->map(fn(Review $review): array => (new PartnerReviewResource($review, $bills))->resolve())
                    ->all(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                ],
            ],
        ]);
    }

    private function resolvePerPage(Request $request): int
    {
        $perPage = (int) $request->query('per_page', self::DEFAULT_PER_PAGE);
        $perPage = max(1, $perPage);

        return min(self::MAX_PER_PAGE, $perPage);
    }
}
