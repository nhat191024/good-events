<?php

namespace App\Filament\Partner\Pages;

use App\Models\User;
use App\Models\PartnerBill;
use App\Models\PartnerCategory;
use App\Enum\PartnerBillStatus;

use BackedEnum;
use UnitEnum;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

use Illuminate\Contracts\Support\Htmlable;

class RealtimePartnerBill extends Page
{
    protected string $view = 'filament.partner.pages.realtime-partner-bill';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Calendar;

    // Livewire listeners for auto-update
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

    public $partnerBills = [];

    public $partnerId = null;

    public $categoryIds = [];

    public $availableCategories = [];

    public $lastUpdated;

    // Filter properties (removed statusFilter - only show pending orders)
    public $dateFilter = 'all';

    public $categoryFilter = 'all';

    public $searchQuery = '';

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

        // Get category_id from partner services and load available categories in one go
        $partnerServices = $user->partnerServices()->with('category')->get();
        $this->categoryIds = $partnerServices->pluck('category_id')->toArray();

        // Build available categories from the already loaded data
        $this->availableCategories = $partnerServices
            ->filter(fn($service) => $service->category !== null)
            ->map(fn($service) => [
                'id' => $service->category->id,
                'name' => $service->category->name
            ])
            ->unique('id')
            ->values()
            ->toArray();

        $query = PartnerBill::whereIn('category_id', $this->categoryIds)
            ->with(['client', 'event', 'category'])
            ->where('status', PartnerBillStatus::PENDING); // Only show pending orders

        // Apply date filter
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

        // Apply category filter
        if ($this->categoryFilter !== 'all') {
            $query->where('category_id', $this->categoryFilter);
        }

        // Apply search filter
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

        $this->partnerBills = $query->latest()
            ->limit(20)
            ->get()
            ->toArray();

        $this->lastUpdated = now()->format('H:i:s');

        // Debug log
        logger('Partner Bills loaded: ' . count($this->partnerBills) . ' bills found for categories: ' . implode(', ', $this->categoryIds));
    }

    public function refreshBills(): void
    {
        $this->loadPartnerBills();
    }    // Auto refresh method - called every 30 seconds
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

    public function acceptOrder($billId): void
    {
        try {
            $bill = PartnerBill::find($billId);
            if ($bill && $bill->status === PartnerBillStatus::PENDING) {
                // Here you would implement your order acceptance logic
                // For now, just update status or create a partner bill detail

                session()->flash('success', __('partner/bill.order_accepted'));
                $this->loadPartnerBills();
            }
        } catch (\Exception $e) {
            session()->flash('error', __('partner/bill.order_accept_error'));
        }
    }

    public function viewDetails($billId): void
    {
        // For now, just show a message. You can implement modal or redirect later
        session()->flash('info', "Xem chi tiết đơn hàng #$billId - Tính năng đang phát triển");

        // Alternative: You could store the bill ID and show details in a modal
        // $this->selectedBillId = $billId;
        // $this->showDetailsModal = true;
    }

    // Livewire updated methods for automatic filtering
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
