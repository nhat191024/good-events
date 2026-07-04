<?php

namespace App\Events;

use App\Models\PartnerBill;
use App\Services\PartnerBillRecipientResolver;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewPartnerBillCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public PartnerBill $bill;

    /**
     * Create a new event instance.
     */
    public function __construct(PartnerBill $bill)
    {
        $this->bill = $bill;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'NewPartnerBillCreated';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return collect(app(PartnerBillRecipientResolver::class)->eligiblePartnerIds($this->bill))
            ->map(fn (int $partnerId): PrivateChannel => new PrivateChannel("partner-orders.{$partnerId}"))
            ->all();
    }

    /**
     * Get the data to be broadcast with the event.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->bill->id,
            'code' => $this->bill->code,
            'final_total' => $this->bill->final_total,
            'created_at' => optional($this->bill->created_at)->diffForHumans(),
            'client_name' => $this->bill->client->name,
            'category_name' => $this->bill->category->name,
            'category_image' => $this->bill->category->getFirstMediaUrl('images', 'thumb'),
            'event_name' => $this->bill->event->name ?? $this->bill->custom_event,
            'date' => optional($this->bill->date)->toDateString(),
            'start_time' => optional($this->bill->start_time)->format('H:i'),
            'end_time' => optional($this->bill->end_time)->format('H:i'),
            'address' => $this->bill->address,
            'location_id' => $this->bill->location_id,
            'note' => $this->bill->note,
        ];
    }
}
