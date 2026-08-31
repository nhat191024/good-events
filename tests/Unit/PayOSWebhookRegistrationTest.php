<?php

use App\Filament\Admin\Pages\PayOSWebhookManager;
use App\Settings\PayOSSettings;

it('registers PayOS settings in the system navigation group', function (): void {
    expect(PayOSSettings::group())->toBe('payos')
        ->and(PayOSWebhookManager::getNavigationGroup())->toBe('system');
});
