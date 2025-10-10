<?php

namespace App\Filament\Partner\Resources\PartnerBills\Pages;

use App\Models\PartnerBill;
use App\Enum\PartnerBillStatus;
use App\Filament\Partner\Resources\PartnerBills\PartnerBillResource;

use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

class PartnerBillsListPage extends Page
{
    protected static string $resource = PartnerBillResource::class;

    protected string $view = 'filament.partner.pages.partner-bills-list-page';

    public function getTitle(): string|Htmlable
    {
        return __('partner/bill.bill');
    }

    public $bills = [];

    public $statusFilter = 'all';

    public $searchQuery = '';

    public $dateFilter = 'today';

    public $sortBy = 'newest';

    protected $listeners = [
        'refreshBills' => 'loadBills',
    ];

    public function mount(): void
    {
        $this->loadBills();
    }

    public function loadBills(): void
    {
        $query = PartnerBill::query()
            ->whereIn('status', [
                PartnerBillStatus::PENDING,
                PartnerBillStatus::CONFIRMED,
            ])
            ->whereHas('details', function (Builder $query) {
                $query->where('partner_id', auth()->id());
            })
            ->with(['client', 'category', 'event', 'details' => function ($query) {
                $query->where('partner_id', auth()->id());
            }]);

        // Apply status filter
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

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
            'date_asc' => $query->orderBy('date', 'asc'),
            'date_desc' => $query->orderBy('date', 'desc'),
            default => $query->orderByDesc('updated_at'),
        };

        $this->bills = $query->get();
    }

    public function updatedStatusFilter(): void
    {
        $this->loadBills();
    }

    public function updatedSearchQuery(): void
    {
        $this->loadBills();
    }

    public function updatedDateFilter(): void
    {
        $this->loadBills();
    }

    public function updatedSortBy(): void
    {
        $this->loadBills();
    }

    public function clearFilters(): void
    {
        $this->statusFilter = 'all';
        $this->searchQuery = '';
        $this->dateFilter = 'all';
        $this->sortBy = 'newest';
        $this->loadBills();
    }

    public function viewBill($billId): void
    {
        $this->redirect(route('filament.partner.resources.partner-bills.view', ['record' => $billId]));
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
            'paid' => __('partner/bill.paid'),
            'cancelled' => __('partner/bill.status_cancelled'),
            default => $status,
        };
    }

    public function completeBill($billId): void
    {
        $bill = PartnerBill::findOrFail($billId);

        // Verify the bill belongs to this partner
        if (!$bill->details()->where('partner_id', auth()->id())->exists()) {
            Notification::make()
                ->title(__('partner/bill.unauthorized_action'))
                ->danger()
                ->send();
            return;
        }

        // Check if status is confirmed
        if ($bill->status !== PartnerBillStatus::CONFIRMED) {
            Notification::make()
                ->title(__('partner/bill.cannot_complete_order'))
                ->danger()
                ->send();
            return;
        }

        $user = auth()->user();
        $balance = $user->balanceInt;
        $feePercentage = app(\App\Settings\PartnerSettings::class)->fee_percentage;
        $withdrawAmount = floor($bill->final_total * ($feePercentage / 100));

        if ($balance < $withdrawAmount) {
            $formatWithdrawAmount = number_format($withdrawAmount) . ' VND';
            $formatBalance = number_format($balance) . ' VND';
            Notification::make()
                ->title(__('partner/bill.insufficient_balance', ['amount' => $formatWithdrawAmount, 'balance' => $formatBalance]))
                ->danger()
                ->send();
            return;
        }

        // Withdraw money
        $old_balance = $user->balanceInt;
        $transaction = $user->withdraw($withdrawAmount, ['reason' => 'Thu phí nền tảng show mã: ' . $bill->code, 'old_balance' => $old_balance]);
        $new_balance = $user->balanceInt;
        $transactionId = $transaction->id;
        $transaction->meta = array_merge($transaction->meta ?? [], [
            'new_balance' => $new_balance,
        ]);
        $transaction->save();

        // Update status
        $bill->status = PartnerBillStatus::COMPLETED;
        $bill->save();

        Notification::make()
            ->title(__('partner/bill.order_completed_success'))
            ->success()
            ->send();

        // Reload bills
        $this->loadBills();
    }
}
