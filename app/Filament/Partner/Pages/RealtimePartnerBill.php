<?php

namespace App\Filament\Partner\Pages;

use App\Models\User;
use App\Models\PartnerBill;
use App\Models\PartnerBillDetail;

use App\Enum\PartnerBillStatus;
use App\Enum\PartnerBillDetailStatus;

use BackedEnum;
use UnitEnum;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

use App\Settings\PartnerSettings;

class RealtimePartnerBill extends Page
{
    protected string $view = 'filament.partner.pages.realtime-partner-bill';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Calendar;

    protected $listeners = [
        'refreshBills' => 'loadPartnerBills',
    ];

    public static function getNavigationLabel(): string
    {
        return __('partner/bill.new_bill');
    }

    public function getTitle(): string|Htmlable
    {
        return __('partner/bill.new_bill');
    }

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        $partnerServices = $user->partnerServices()
            ->where('status', 'approved')
            ->pluck('category_id')
            ->unique()
            ->toArray();

        if (empty($partnerServices)) {
            return null;
        }

        $count = PartnerBill::whereIn('category_id', $partnerServices)
            ->where('status', PartnerBillStatus::PENDING)
            ->whereDoesntHave('details', function ($query) use ($user) {
                $query->where('partner_id', $user->id);
            })
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public $partnerBills = [];

    public $partnerId = null;

    public $categoryIds = [];

    public $availableCategories = [];

    public $lastUpdated;

    public $dateFilter = 'all';

    public $categoryFilter = 'all';

    public $searchQuery = '';

    // Modal properties
    public $showAcceptModal = false;

    public $selectedBillId = null;

    public $selectedBillCode = '';

    public $priceInput = '';

    // Client detail modal properties
    public $showClientModal = false;

    public $selectedClient = null;

    public function mount(): void
    {
        $this->partnerId = auth()->id();
        $this->loadPartnerBills();
        $this->lastUpdated = now()->format('H:i:s');
    }

    public function loadPartnerBills(): void
    {
        $user = User::find($this->partnerId);
        if (! $user || ! $user->partnerServices()->exists()) {
            $this->partnerBills = [];
            $this->categoryIds = [];
            $this->availableCategories = [];

            return;
        }

        $partnerServices = $user->partnerServices()
            ->select('id', 'category_id', 'status')
            ->where('status', 'approved')
            ->with('category:id,name')
            ->get();

        $this->categoryIds = $partnerServices->pluck('category_id')->unique()->toArray();

        $categoriesMap = $partnerServices
            ->filter(fn($service) => $service->category !== null)
            ->pluck('category', 'category.id')
            ->unique('id');

        $this->availableCategories = $categoriesMap
            ->map(fn($category) => [
                'id' => $category->id,
                'name' => $category->name
            ])
            ->values()
            ->toArray();

        $query = PartnerBill::whereIn('category_id', $this->categoryIds)
            ->with([
                'client:id,name,email,avatar',
                'client.partnerProfile:id,user_id,partner_name',
                'event:id,name'
            ])
            ->where('status', PartnerBillStatus::PENDING)
            ->whereDoesntHave('details', function ($query) {
                $query->where('partner_id', $this->partnerId);
            });

        if ($this->dateFilter !== 'all') {
            switch ($this->dateFilter) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'this_week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year);
                    break;
            }
        }

        if ($this->categoryFilter !== 'all') {
            $query->where('category_id', $this->categoryFilter);
        }

        if (! empty($this->searchQuery)) {
            $query->where(function ($q) {
                $q->where('code', 'like', '%' . $this->searchQuery . '%')
                    ->orWhereHas('client', function ($clientQuery) {
                        $clientQuery->where('name', 'like', '%' . $this->searchQuery . '%');
                    })
                    ->orWhereHas('event', function ($eventQuery) {
                        $eventQuery->where('name', 'like', '%' . $this->searchQuery . '%');
                    });
            });
        }

        $bills = $query->latest()
            ->limit(20)
            ->get();

        $bills->each(function ($bill) use ($categoriesMap) {
            if (isset($categoriesMap[$bill->category_id])) {
                $bill->setRelation('category', $categoriesMap[$bill->category_id]);
            }
        });

        $this->partnerBills = $bills->toArray();
        $this->lastUpdated = now()->format('H:i:s');

        logger('Partner Bills loaded: ' . count($this->partnerBills) . ' bills found for categories: ' . implode(', ', $this->categoryIds));
    }

    public function refreshBills(): void
    {
        $this->loadPartnerBills();
    }
    public function autoRefresh(): void
    {
        $this->loadPartnerBills();
    }

    public function clearFilters(): void
    {
        $this->dateFilter = 'all';
        $this->categoryFilter = 'all';
        $this->searchQuery = '';
        $this->loadPartnerBills();
    }

    public function openAcceptModal($billId): void
    {
        $bill = PartnerBill::find($billId);
        if ($bill && $bill->status === PartnerBillStatus::PENDING) {
            $this->selectedBillId = $billId;
            $this->selectedBillCode = $bill->code;
            $this->priceInput = '';
            $this->showAcceptModal = true;
        }
    }

    public function closeAcceptModal(): void
    {
        $this->showAcceptModal = false;
        $this->selectedBillId = null;
        $this->selectedBillCode = '';
        $this->priceInput = '';
        $this->resetErrorBag();
    }

    public function acceptOrder(): void
    {
        $this->validate([
            'priceInput' => ['required', 'numeric', 'min:1'],
        ], [
            'priceInput.required' => __('partner/bill.price_required'),
            'priceInput.numeric' => __('partner/bill.price_numeric'),
            'priceInput.min' => __('partner/bill.price_min'),
        ]);

        try {
            $user =  Auth::user();
            $balance = $user->balanceInt;
            $minimum_balance = app(PartnerSettings::class)->minimum_balance;
            if ($balance < $minimum_balance) {
                session()->flash('error', __('partner/bill.minimum_balance'));
                return;
            }

            $bill = PartnerBill::find($this->selectedBillId);
            if ($bill && $bill->status === PartnerBillStatus::PENDING) {

                PartnerBillDetail::create([
                    'partner_bill_id' => $bill->id,
                    'partner_id' => $this->partnerId,
                    'total' => $this->priceInput,
                    'status' => PartnerBillDetailStatus::NEW,
                ]);

                session()->flash('success', __('partner/bill.order_accepted'));
                $this->closeAcceptModal();
                $this->loadPartnerBills();
            }
        } catch (\Exception $e) {
            session()->flash('error', __('partner/bill.order_accept_error'));
        }
    }

    public function viewDetails($billId): void
    {
        session()->flash('info', "Xem chi tiết đơn hàng #$billId - Tính năng đang phát triển");
    }

    public function openClientModal($clientId): void
    {
        $client = User::with(['statistics', 'partnerProfile'])
            ->find($clientId);

        if ($client) {
            $this->selectedClient = $client->toArray();
            $this->showClientModal = true;
        }
    }

    public function closeClientModal(): void
    {
        $this->showClientModal = false;
        $this->selectedClient = null;
    }

    public function updatedDateFilter(): void
    {
        logger('Date filter updated: ' . $this->dateFilter);
        $this->loadPartnerBills();
    }

    public function updatedCategoryFilter(): void
    {
        logger('Category filter updated: ' . $this->categoryFilter);
        $this->loadPartnerBills();
    }

    public function updatedSearchQuery(): void
    {
        logger('Search query updated: ' . $this->searchQuery);
        $this->loadPartnerBills();
    }
}
