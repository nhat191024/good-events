<?php

namespace App\Filament\Partner\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Actions\Action;

use App\Settings\PartnerSettings;

class Dashboard extends BaseDashboard
{
    public $defaultAction = null;

    public int $balance = 0;
    public int $minBalance = 0;
    public int $amount = 0;

    public function mount(): void
    {
        $this->balance = auth()->user()->balanceInt ?? 0;
        $this->minBalance = app(PartnerSettings::class)->minimum_balance;
        $this->amount = $this->minBalance - $this->balance > 0 ? $this->minBalance - $this->balance : 0;

        if (! $this->shouldShowCallToAction()) {
            $this->defaultAction = null;
        } else {
            $this->defaultAction = 'callToAction';
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
        return Action::make('callToAction')
            ->modalHeading(__('notification.balance_low_title'))
            ->modalDescription(__('notification.balance_low_body', ['balance' => number_format($this->balance), 'amount' => number_format($this->amount)]))
            ->action(function (array $data) {
                return redirect()->route('filament.partner.resources.wallets.index');
            })
            ->modalSubmitActionLabel(__('notification.open_wallet'))
            ->modalCancelAction(false)
            ->closeModalByClickingAway(false)
            ->modalCloseButton(false);
    }
}
