<?php

namespace App\Filament\Partner\Pages;

use App\Models\PartnerBill;
use App\Models\Customer;

use Filament\Pages\Page;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ViewPartnerBill extends Page
{
    protected string $view = 'filament.partner.pages.view-partner-bill';

    public ?string $bill_id = null;
    public ?PartnerBill $bill = null;
    public ?Customer $customer = null;

    protected $queryString = [
        'bill_id' => ['except' => ''],
    ];

    public function mount()
    {
        $this->bill_id = request()->query('bill_id');

        if (!$this->bill_id) {
            $this->redirect(PendingPartnerBill::getUrl());
            return;
        }

        try {
            $this->bill = PartnerBill::with(['category', 'event', 'details' => function ($q) {
                $q->where('partner_id', auth()->id());
            }])->findOrFail($this->bill_id);

            $this->customer = Customer::select('id', 'name')->find($this->bill->client_id);

            // Check authorization
            if (!$this->bill->details()->where('partner_id', auth()->id())->exists()) {
                abort(403);
            }
        } catch (ModelNotFoundException $e) {
            $this->redirect(PendingPartnerBill::getUrl());
        }
    }

    public function getTitle(): string
    {
        return $this->bill ? "Chi tiết đơn hàng #{$this->bill->code}" : 'Chi tiết đơn hàng';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
