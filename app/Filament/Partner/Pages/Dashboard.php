<?php

namespace App\Filament\Partner\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Actions\Action;

use App\Settings\PartnerSettings;

class Dashboard extends BaseDashboard
{
    public $defaultAction = 'callToAction';

    protected int $balance = 0;
    protected int $minBalance = 0;

    public function mount(): void
    {
        $this->balance = auth()->user()->balanceInt ?? 0;
        $this->minBalance = app(PartnerSettings::class)->minimum_balance;

        if (! $this->shouldShowCallToAction()) {
            $this->defaultAction = null;
        }
    }

    protected function shouldShowCallToAction(): bool
    {
        if (session()->has('partner_cta_shown')) {
            return false;
        }

        if ($this->balance >= $this->minBalance) {
            return false;
        }

        session()->put('partner_cta_shown', true);
        return true;
    }

    public function callToAction(): Action
    {
        $amount = $this->minBalance - $this->balance;

        return Action::make('callToAction')
            ->modalHeading(__('notification.balance_low_title'))
            ->modalDescription(__('notification.balance_low_body', ['balance' => number_format($this->balance), 'amount' => number_format($amount)]))
            ->action(function (array $data) {
                return redirect()->route('filament.partner.resources.wallets.index');
            })
            ->modalSubmitActionLabel(__('notification.open_wallet'))
            ->modalCancelAction(false)
            ->closeModalByClickingAway(false)
            ->modalCloseButton(false);
    }
}
