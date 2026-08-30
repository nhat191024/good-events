<?php

use App\Enum\PartnerBillDetailStatus;
use App\Enum\PartnerBillStatus;
use App\Filament\Partner\Pages\PendingPartnerBill;
use App\Filament\Partner\Pages\RealtimePartnerBill;
use App\Models\PartnerBill;
use App\Models\PartnerBillDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createSelfOwnedPartnerBill(User $user): PartnerBill
{
    return PartnerBill::withoutEvents(fn (): PartnerBill => PartnerBill::query()->create([
        'code' => 'PB-SELF-ORDER',
        'address' => 'Test address',
        'phone' => '0900000000',
        'client_id' => $user->id,
        'status' => PartnerBillStatus::PENDING,
    ]));
}

it('prevents a partner from accepting their own order through the API', function (): void {
    $partner = User::factory()->create([
        'can_accept_shows' => true,
    ]);
    $bill = createSelfOwnedPartnerBill($partner);

    $this->actingAs($partner, 'sanctum')
        ->postJson("/api/partner/bills/{$bill->id}/accept", ['price' => 100000])
        ->assertForbidden()
        ->assertJson([
            'message' => __('partner/bill.self_order_not_allowed'),
            'code' => 'SELF_ORDER_NOT_ALLOWED',
        ]);

    expect(PartnerBillDetail::query()->whereBelongsTo($bill)->exists())->toBeFalse();
});

it('prevents a partner from accepting their own order through Livewire', function (): void {
    $partner = User::factory()->create([
        'can_accept_shows' => true,
    ]);
    $bill = createSelfOwnedPartnerBill($partner);

    Livewire::actingAs($partner)
        ->test(RealtimePartnerBill::class)
        ->set('selectedBillId', $bill->id)
        ->set('priceInput', 100000)
        ->call('acceptOrder')
        ->assertSessionHas('error', __('partner/bill.self_order_not_allowed'));

    expect(PartnerBillDetail::query()->whereBelongsTo($bill)->exists())->toBeFalse();
});

it('hides previously accepted self-owned orders from the pending partner page', function (): void {
    $partner = User::factory()->create();
    $bill = createSelfOwnedPartnerBill($partner);

    PartnerBillDetail::query()->create([
        'partner_bill_id' => $bill->id,
        'partner_id' => $partner->id,
        'total' => 100000,
        'status' => PartnerBillDetailStatus::NEW,
    ]);

    Livewire::actingAs($partner)
        ->test(PendingPartnerBill::class)
        ->assertDontSee($bill->code);
});
