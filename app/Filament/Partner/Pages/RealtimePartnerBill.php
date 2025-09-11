<?php

namespace App\Filament\Partner\Pages;

use Filament\Pages\Page;
use App\Models\PartnerBill;
use App\Models\User;

class RealtimePartnerBill extends Page
{
    protected string $view = 'filament.partner.pages.realtime-partner-bill';

    protected static ?string $title = 'Auto-refresh Partner Bills';

    public $partnerBills = [];
    public $partnerId = null;
    public $categoryIds = [];
    public $lastUpdated;

    public function mount(): void
    {
        $this->partnerId = auth()->id();
        $this->loadPartnerBills();
        $this->lastUpdated = now()->format('H:i:s');
    }

    public function loadPartnerBills(): void
    {
        $user = User::find($this->partnerId);
        if (!$user || !$user->partnerServices()->exists()) {
            $this->partnerBills = [];
            $this->categoryIds = [];
            return;
        }

        $this->categoryIds = $user->partnerServices()->pluck('id')->toArray();

        $this->partnerBills = PartnerBill::whereIn('category_id', $this->categoryIds)
            ->with(['client', 'event', 'category'])
            ->latest()
            ->limit(10)
            ->get()
            ->toArray();

        $this->lastUpdated = now()->format('H:i:s');
    }

    public function refreshBills(): void
    {
        $this->loadPartnerBills();
    }

    // Auto refresh method - called every 30 seconds
    public function autoRefresh(): void
    {
        $this->loadPartnerBills();
    }
}
