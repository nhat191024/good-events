<?php

namespace App\Filament\Partner\Resources\PartnerBills\Pages;

use App\Models\PartnerBill;
use App\Enum\PartnerBillStatus;
use App\Filament\Partner\Resources\PartnerBills\PartnerBillResource;

use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

use Livewire\WithPagination;

class PartnerBillsListPage extends Page
{
    use WithPagination;

    protected static string $resource = PartnerBillResource::class;

    protected string $view = 'filament.partner.pages.partner-bills-list-page';

    protected string $paginationTheme = 'tailwind';

    public function getTitle(): string|Htmlable
    {
        return __('partner/bill.bill');
    }

    public $statusFilter = 'all';

    public $searchQuery = '';

    public $dateFilter = 'all';

    public $sortBy = 'date_asc';

    public $perPage = 6;

    public $arrivalPhoto;

    protected $listeners = [
        'refreshBills' => '$refresh',
    ];

    protected function rules()
    {
        return [
            'arrivalPhoto' => 'required|image|max:5120|mimes:jpeg,png,jpg,webp', // Max 5MB
        ];
    }

    protected function messages()
    {
        return [
            'arrivalPhoto.required' => __('partner/bill.arrival_photo_required'),
            'arrivalPhoto.image' => __('partner/bill.arrival_photo_must_be_image'),
            'arrivalPhoto.max' => __('partner/bill.arrival_photo_max_size'),
            'arrivalPhoto.mimes' => __('partner/bill.arrival_photo_invalid_format'),
        ];
    }

    public function getBillsProperty()
    {
        $query = PartnerBill::query()
            ->whereIn('status', [
                PartnerBillStatus::PENDING,
                PartnerBillStatus::CONFIRMED,
                PartnerBillStatus::IN_JOB,
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
            'date_asc' => $query->orderBy('date', 'asc')->orderBy('start_time', 'asc'),
            'date_desc' => $query->orderBy('date', 'desc')->orderBy('start_time', 'desc'),
            'newest' => $query->orderByDesc('updated_at'),
            default => $query->orderBy('date', 'asc'),
        };

        return $query->paginate($this->perPage);
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
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
        $this->statusFilter = 'all';
        $this->searchQuery = '';
        $this->dateFilter = 'all';
        $this->sortBy = 'newest';
        $this->resetPage();
    }

    public function viewBill($billId): void
    {
        $this->redirect(route('filament.partner.resources.partner-bills.view', ['record' => $billId]));
    }

    public function openMarkInJobModal($billId): void
    {
        $this->selectedBillId = $billId;
        $this->showMarkInJobModal = true;
    }

    public function openCompleteModal($billId): void
    {
        $this->selectedBillId = $billId;
        $this->showCompleteModal = true;
    }

    public function closeModals(): void
    {
        $this->showMarkInJobModal = false;
        $this->showCompleteModal = false;
        $this->selectedBillId = null;
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

    public function markAsInJob($billId): void
    {
        $this->validate();

        $bill = PartnerBill::findOrFail($billId);

        if (!$bill->details()->where('partner_id', auth()->id())->exists()) {
            Notification::make()
                ->title(__('partner/bill.unauthorized_action'))
                ->danger()
                ->send();
            return;
        }

        if ($bill->status !== PartnerBillStatus::CONFIRMED) {
            Notification::make()
                ->title(__('partner/bill.must_be_confirmed'))
                ->danger()
                ->send();
            return;
        }

        if ($this->arrivalPhoto) {
            $bill->addMedia($this->arrivalPhoto->getRealPath())
                ->usingName('Arrival Photo - ' . $bill->code)
                ->usingFileName($this->arrivalPhoto->getClientOriginalName())
                ->toMediaCollection('arrival_photo');
        }

        $bill->status = PartnerBillStatus::IN_JOB;
        $bill->save();

        $this->arrivalPhoto = null;

        Notification::make()
            ->title(__('partner/bill.marked_as_in_job'))
            ->success()
            ->send();
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

        // Check if status is IN_JOB
        if ($bill->status !== PartnerBillStatus::IN_JOB) {
            Notification::make()
                ->title(__('partner/bill.must_be_in_job'))
                ->danger()
                ->send();
            return;
        }

        $user = auth()->user();
        $balance = $user->balanceInt;
        $feePercentage = app(\App\Settings\PartnerSettings::class)->fee_percentage;
        $withdrawAmount = floor($bill->total * ($feePercentage / 100));

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
    }
}
