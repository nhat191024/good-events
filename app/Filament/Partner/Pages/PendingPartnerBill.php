<?php

namespace App\Filament\Partner\Pages;

use BackedEnum;

use App\Models\PartnerBill;
use App\Enum\PartnerBillStatus;
use App\Enum\PartnerBillDetailStatus;

use Filament\Pages\Page;
use Filament\Notifications\Notification;

use Filament\Support\Icons\Heroicon;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

use Livewire\WithPagination;

class PendingPartnerBill extends Page
{
    use WithPagination;

    protected static ?int $navigationSort = 0;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CalendarDateRange;

    protected string $view = 'filament.partner.pages.pending-partner-bill';

    public static function getNavigationLabel(): string
    {
        return __('partner/bill.bill_pending');
    }

    public function getTitle(): string
    {
        return __('partner/bill.bill_pending');
    }

    protected string $paginationTheme = 'tailwind';

    public $statusFilter = 'all';

    public $searchQuery = '';

    public $dateFilter = 'all';

    public $sortBy = 'date_asc';

    public $perPage = 6;

    public $arrivalPhoto;

    protected $listeners = [
        'refreshBills' => '$refresh',
    ];

    public function getBillsProperty()
    {
        $query = PartnerBill::query()
            ->whereIn('status', [
                PartnerBillStatus::PENDING,
            ])
            ->whereHas('details', function (Builder $query) {
                $query->where('partner_id', auth()->id());
            })
            ->with(['client', 'category', 'event', 'details' => function ($query) {
                $query->where('partner_id', auth()->id())
                    ->whereStatus(PartnerBillDetailStatus::NEW);
            }]);

        // Apply search
        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('code', 'like', "%{$this->searchQuery}%")
                    ->orWhere('address', 'like', "%{$this->searchQuery}%")
                    ->orWhere('phone', 'like', "%{$this->searchQuery}%")
                    ->orWhereHas('client', function ($clientQuery) {
                        $clientQuery->where('name', 'like', "%{$this->searchQuery}%");
                    });
            });
        }

        // Apply date filter
        if ($this->dateFilter !== 'all') {
            match ($this->dateFilter) {
                'today' => $query->whereDate('date', today()),
                'tomorrow' => $query->whereDate('date', today()->addDay()),
                'this_week' => $query->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]),
                'next_week' => $query->whereBetween('date', [now()->addWeek()->startOfWeek(), now()->addWeek()->endOfWeek()]),
                'this_month' => $query->whereMonth('date', now()->month)->whereYear('date', now()->year),
                default => null,
            };
        }

        // Apply sorting
        $query = match ($this->sortBy) {
            'oldest' => $query->orderBy('updated_at', 'asc'),
            'date_asc' => $query->orderBy('date', 'asc')->orderBy('start_time', 'asc'),
            'date_desc' => $query->orderBy('date', 'desc')->orderBy('start_time', 'desc'),
            'newest' => $query->orderByDesc('updated_at'),
            default => $query->orderBy('date', 'asc'),
        };

        return $query->paginate($this->perPage);
    }

    public function updatedSearchQuery(): void
    {
        $this->resetPage();
    }

    public function updatedDateFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSortBy(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->searchQuery = '';
        $this->dateFilter = 'all';
        $this->sortBy = 'newest';
        $this->resetPage();
    }

    public function viewBill($billId): void
    {
        $this->redirect(ViewPartnerBill::getUrl(['bill_id' => $billId]));
    }

    public function getStatusColor(string $status): string
    {
        return match ($status) {
            'pending' => 'warning',
            'confirmed' => 'success',
            'paid' => 'info',
            'cancelled' => 'danger',
            default => 'gray',
        };
    }

    public function getStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => __('partner/bill.status_pending'),
            'confirmed' => __('partner/bill.status_confirmed'),
            'in_job' => __('partner/bill.status_in_job'),
            'paid' => __('partner/bill.paid'),
            'cancelled' => __('partner/bill.status_cancelled'),
            default => $status,
        };
    }
}
