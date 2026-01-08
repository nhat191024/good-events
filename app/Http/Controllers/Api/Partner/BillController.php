<?php

namespace App\Http\Controllers\Api\Partner;

use App\Enum\PartnerBillDetailStatus;
use App\Enum\PartnerBillStatus;
use App\Http\Controllers\Api\Concerns\PaginatesApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PartnerBillResource;
use App\Models\PartnerBill;
use App\Models\PartnerBillDetail;
use App\Models\PartnerCategory;
use App\Settings\PartnerSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillController extends Controller
{
    use PaginatesApi;

    private const DEFAULT_PER_PAGE = 6;
    private const MAX_PER_PAGE = 50;

    public function realtime(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->partnerServices()->exists()) {
            return response()->json([
                'partner_bills' => [],
                'available_categories' => [],
            ]);
        }

        $partnerServices = $user->partnerServices()
            ->select('id', 'category_id', 'status')
            ->where('status', 'approved')
            ->with('category:id,name')
            ->get();

        $categoryIds = $partnerServices->pluck('category_id')->unique()->toArray();
        $categoriesMap = $partnerServices
            ->filter(fn ($service) => $service->category !== null)
            ->pluck('category', 'category.id')
            ->unique('id');

        $availableCategories = $categoriesMap
            ->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
            ])
            ->values()
            ->toArray();

        $query = PartnerBill::whereIn('category_id', $categoryIds)
            ->with([
                'client:id,name,email,avatar,created_at',
                'client.partnerProfile:id,user_id,partner_name',
                'event:id,name',
            ])
            ->where('status', PartnerBillStatus::PENDING)
            ->whereDoesntHave('details', function ($query) use ($user) {
                $query->where('partner_id', $user->id);
            });

        $this->applyFilters($query, $request, true);

        $bills = $query->latest()->limit(20)->get();

        $bills->each(function ($bill) use ($categoriesMap) {
            if (isset($categoriesMap[$bill->category_id])) {
                $bill->setRelation('category', $categoriesMap[$bill->category_id]);
            }
        });

        $partnerBills = $bills->map(function ($bill) {
            return [
                'id' => $bill->id,
                'code' => $bill->code,
                'address' => $bill->address,
                'phone' => $bill->phone,
                'date' => $bill->date?->format('d-m-Y'),
                'start_time' => $bill->start_time?->format('H:i'),
                'end_time' => $bill->end_time?->format('H:i'),
                'status' => $bill->status instanceof \BackedEnum ? $bill->status->value : (string) $bill->status,
                'client' => $bill->client ? [
                    'id' => $bill->client->id,
                    'name' => $bill->client->name,
                    'email' => $bill->client->email,
                    'avatar' => $bill->client->avatar_url,
                    'created_at' => $bill->client->created_at?->format('Y-m-d'),
                ] : null,
                'event' => $bill->event ? [
                    'id' => $bill->event->id,
                    'name' => $bill->event->name,
                ] : null,
                'category' => $bill->category ? [
                    'id' => $bill->category->id,
                    'name' => $bill->category->name,
                ] : null,
            ];
        })->values();

        return response()->json([
            'partner_bills' => $partnerBills,
            'available_categories' => $availableCategories,
            'last_updated' => now()->format('H:i:s'),
        ]);
    }

    public function accept(Request $request, PartnerBill $bill)
    {
        $request->validate([
            'price' => ['required', 'numeric', 'min:1'],
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (!$user->can_accept_shows) {
            return response()->json(['message' => 'Not allowed to accept orders.'], 403);
        }

        $balance = $user->balanceInt;
        $minimumBalance = app(PartnerSettings::class)->minimum_balance;
        if ($balance < $minimumBalance) {
            return response()->json(['message' => 'Insufficient balance.'], 422);
        }

        if ($bill->status !== PartnerBillStatus::PENDING) {
            return response()->json(['message' => 'Order is not pending.'], 422);
        }

        PartnerBillDetail::create([
            'partner_bill_id' => $bill->id,
            'partner_id' => $user->id,
            'total' => $request->input('price'),
            'status' => PartnerBillDetailStatus::NEW,
        ]);

        return response()->json(['success' => true]);
    }

    public function pending(Request $request)
    {
        $query = PartnerBill::query()
            ->where('status', PartnerBillStatus::PENDING)
            ->whereHas('details', function ($query) {
                $query->where('partner_id', auth()->id())
                    ->whereStatus(PartnerBillDetailStatus::NEW);
            })
            ->with([
                'client',
                'category',
                'event',
                'details' => function ($query) {
                    $query->where('partner_id', auth()->id())
                        ->whereStatus(PartnerBillDetailStatus::NEW);
                },
            ]);

        $this->applyFilters($query, $request, false);

        $perPage = $this->resolvePerPage($request, self::DEFAULT_PER_PAGE);
        $page = max(1, (int) $request->query('page', 1));

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'bills' => $this->paginatedData($paginator, PartnerBillResource::class),
        ]);
    }

    public function confirmed(Request $request)
    {
        $query = PartnerBill::query()
            ->whereIn('status', [
                PartnerBillStatus::CONFIRMED,
                PartnerBillStatus::IN_JOB,
            ])
            ->whereHas('details', function ($query) {
                $query->where('partner_id', auth()->id())
                    ->whereStatus(PartnerBillDetailStatus::CLOSED);
            })
            ->with([
                'client',
                'category',
                'event',
                'details' => function ($query) {
                    $query->where('partner_id', auth()->id());
                },
            ]);

        $this->applyFilters($query, $request, false);

        $perPage = $this->resolvePerPage($request, self::DEFAULT_PER_PAGE);
        $page = max(1, (int) $request->query('page', 1));

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'bills' => $this->paginatedData($paginator, PartnerBillResource::class),
        ]);
    }

    public function show(Request $request, PartnerBill $bill)
    {
        if (!$bill->details()->where('partner_id', auth()->id())->exists()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $bill->load(['client', 'category', 'event', 'details' => function ($q) {
            $q->where('partner_id', auth()->id());
        }]);

        return response()->json([
            'bill' => new PartnerBillResource($bill),
        ]);
    }

    public function markInJob(Request $request, PartnerBill $bill)
    {
        if (!$bill->details()->where('partner_id', auth()->id())->exists()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($bill->status !== PartnerBillStatus::CONFIRMED) {
            return response()->json(['message' => 'Order must be confirmed.'], 422);
        }

        $validated = $request->validate([
            'arrival_photo' => 'required|image|max:5120|mimes:jpeg,png,jpg,webp',
        ]);

        if ($request->hasFile('arrival_photo')) {
            $file = $request->file('arrival_photo');
            $bill->addMedia($file->getRealPath())
                ->usingName('Arrival Photo - ' . $bill->code)
                ->usingFileName($file->getClientOriginalName())
                ->toMediaCollection('arrival_photo');
        }

        $bill->status = PartnerBillStatus::IN_JOB;
        $bill->save();

        return response()->json(['success' => true]);
    }

    public function complete(Request $request, PartnerBill $bill)
    {
        if (!$bill->details()->where('partner_id', auth()->id())->exists()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($bill->status !== PartnerBillStatus::IN_JOB) {
            return response()->json(['message' => 'Order must be in job.'], 422);
        }

        $user = auth()->user();
        $balance = $user->balanceInt;
        $feePercentage = app(PartnerSettings::class)->fee_percentage;
        $withdrawAmount = floor($bill->total * ($feePercentage / 100));

        if ($balance < $withdrawAmount) {
            return response()->json(['message' => 'Insufficient balance.'], 422);
        }

        $bill->status = PartnerBillStatus::COMPLETED;
        $bill->save();

        return response()->json(['success' => true]);
    }

    private function resolvePerPage(Request $request, int $default): int
    {
        $perPage = (int) $request->query('per_page', $default);
        $perPage = max(1, $perPage);

        return min(self::MAX_PER_PAGE, $perPage);
    }

    private function applyFilters($query, Request $request, bool $includesCategoryFilter): void
    {
        $search = trim((string) $request->query('search', ''));
        $dateFilter = $request->query('date_filter', 'all');
        $sortBy = $request->query('sort', 'date_asc');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhereHas('client', function ($clientQuery) use ($search) {
                        $clientQuery->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('event', function ($eventQuery) use ($search) {
                        $eventQuery->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($dateFilter !== 'all') {
            match ($dateFilter) {
                'today' => $query->whereDate('date', today()),
                'tomorrow' => $query->whereDate('date', today()->addDay()),
                'this_week' => $query->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]),
                'next_week' => $query->whereBetween('date', [now()->addWeek()->startOfWeek(), now()->addWeek()->endOfWeek()]),
                'this_month' => $query->whereMonth('date', now()->month)->whereYear('date', now()->year),
                default => null,
            };
        }

        if ($includesCategoryFilter && $request->filled('category_id') && $request->query('category_id') !== 'all') {
            $query->where('category_id', $request->query('category_id'));
        }

        if (!$includesCategoryFilter) {
            match ($sortBy) {
                'oldest' => $query->orderBy('updated_at', 'asc'),
                'date_desc' => $query->orderBy('date', 'desc')->orderBy('start_time', 'desc'),
                'newest' => $query->orderByDesc('updated_at'),
                default => $query->orderBy('date', 'asc')->orderBy('start_time', 'asc'),
            };
        }
    }
}
