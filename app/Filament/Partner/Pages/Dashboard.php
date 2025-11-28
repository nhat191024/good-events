<?php

namespace App\Filament\Partner\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Actions\Action;

use App\Settings\PartnerSettings;

class Dashboard extends BaseDashboard
{
    public $defaultAction = 'callToAction';

    public function mount(): void
    {
        if (! $this->shouldShowCallToAction()) {
            $this->defaultAction = null;
        }
    }

    protected function shouldShowCallToAction(): bool
    {
        if (session()->has('partner_cta_shown')) {
            return false;
        }

        session()->put('partner_cta_shown', true);
        return true;
    }

    public function callToAction(): Action
    {
        $user = auth()->user();
        $balance = $user->balanceInt ?? 0;

        $amount = app(PartnerSettings::class)->minimum_balance - $balance;

        return Action::make('callToAction')
            ->modalHeading(__('notification.balance_low_title'))
            ->modalDescription(__('notification.balance_low_body', ['balance' => number_format($balance), 'amount' => number_format($amount)]))
            ->action(function (array $data) {
                return redirect()->route('filament.partner.resources.wallets.index');
            })
            ->modalSubmitActionLabel(__('notification.open_wallet'))
            ->modalCancelAction(false)
            ->closeModalByClickingAway(false)
            ->modalCloseButton(false);
    }
}
