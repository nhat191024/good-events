<?php

namespace App\Services;

use App\Models\User;
use App\Models\Partner;
use App\Models\Customer;
use App\Models\PartnerBill;

use App\Services\FCMService;

use App\Mail\PartnerBillReceived;
use App\Mail\PartnerBillConfirmed;
use App\Mail\PartnerBillReminder;
use App\Mail\PartnerBillExpired;

use Filament\Actions\Action;
use Filament\Notifications\Notification;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

class PartnerBillNotificationService
{
    private FCMService $fcmService;

    public function __construct()
    {
        $this->fcmService = app(FCMService::class);
    }

    /**
     * Determine locale for a user
     */
    private function getUserLocale($user): string
    {
        // For now, use app locale. In future, can check user preference
        return $user && property_exists($user, 'language') ? $user->language : config('app.locale', 'vi');
    }

    /**
     * Gửi mail thông báo đơn đã được nhận (khi đơn được tạo)
     */
    public function sendOrderReceivedNotification(PartnerBill $partnerBill): void
    {
        try {
            /** @var Customer|null $client */
            $client = Customer::find($partnerBill->client_id);


            if ($client && $client->email) {
                $clientLocale = $this->getUserLocale($client);
                Mail::to($client->email)
                    ->queue(new PartnerBillReceived($partnerBill, 'client', $clientLocale));
            }

            $eligiblePartners = User::whereHas('partnerServices', function ($query) use ($partnerBill) {
                $query->where('category_id', $partnerBill->category_id)
                    ->where('status', 'approved');
            })->whereNotNull('email')->get();

            /** @var User|null $partner */
            foreach ($eligiblePartners as $partner) {
                $partnerLocale = $this->getUserLocale($partner);
                Mail::to($partner->email)
                    ->queue(new PartnerBillReceived($partnerBill, 'partner', $partnerLocale));

                if ($partner->fcm_token) {
                    $title = __('notification.bill_received.title');
                    $body = __('notification.bill_received.subject', ['code' => $partnerBill->code]);

                    //TODO: add data payload with order details link
                    $this->fcmService->sendToUser($partner, $title, $body);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to send order received notification', [
                'partner_bill_id' => $partnerBill->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Gửi mail thông báo đơn đã được xác nhận
     */
    public function sendOrderConfirmedNotification(PartnerBill $partnerBill): void
    {
        try {
            /** @var Partner|null $partner */
            $partner = Partner::find($partnerBill->partner_id);
            $clientName = $partnerBill->client?->name ?? 'Khách hàng';

            if (!$partner) {
                Log::warning('Partner bill confirmed but partner record missing', [
                    'partner_bill_id' => $partnerBill->id,
                    'partner_id' => $partnerBill->partner_id,
                ]);

                return;
            }

            if ($partner) {
                $partnerLocale = $this->getUserLocale($partner);

                if ($partner->email) {
                    Mail::to($partner->email)
                        ->queue(new PartnerBillConfirmed($partnerBill, $partnerLocale));
                }

                if ($partner->fcm_token) {
                    $title = __('notification.bill_confirmed.title');
                    $body = __('notification.bill_confirmed.subject', ['code' => $partnerBill->code]);
                    $this->fcmService->sendToUser($partner, $title, $body);
                }

                Notification::make()
                    ->title(__('notification.client_accepted_title'))
                    ->body(__('notification.client_accepted_body', [
                        'code' => $partnerBill->code,
                        'client_name' => $clientName,
                    ]))
                    ->warning()
                    ->actions([
                        Action::make('open')
                            ->label('Mở chat')
                            ->url(route('filament.partner.pages.chat', ['chat' => $partnerBill->thread_id])),
                    ])
                    ->sendToDatabase($partner,  true);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send order confirmed notification', [
                'partner_bill_id' => $partnerBill->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Gửi mail thông báo sắp đến giờ sự kiện
     * TODO: có thể sẽ loại bỏ gửi mail cho event này
     */
    public function sendUpcomingEventReminder(PartnerBill $partnerBill): void
    {
        try {
            if ($partnerBill->isPaid()) {
                return;
            }

            /** @var Partner|null $partner */
            $partner = Partner::find($partnerBill->partner_id);
            /** @var Customer|null $client */
            $client = Customer::find($partnerBill->client_id);

            if ($client) {
                $clientLocale = $this->getUserLocale($client);

                if ($client->email) {
                    Mail::to($client->email)
                        ->send(new PartnerBillReminder($partnerBill, 'client', $clientLocale));
                }

                if ($client->fcm_token) {
                    $title = __('notification.bill_reminder.title');
                    $body = __('notification.bill_reminder.client_subject', ['code' => $partnerBill->code]);
                    $this->fcmService->sendToUser($client, $title, $body);
                }
            }

            // Notify for partner
            if ($partner) {
                $partnerLocale = $this->getUserLocale($partner);

                if ($partner->email) {
                    Mail::to($partner->email)
                        ->send(new PartnerBillReminder($partnerBill, 'partner', $partnerLocale));
                }

                if ($partner->fcm_token) {
                    $title = __('notification.bill_reminder.title');
                    $body = __('notification.bill_reminder.partner_subject', ['code' => $partnerBill->code]);
                    $this->fcmService->sendToUser($partner, $title, $body);
                }

                $eventDateTime = $partnerBill->date->copy()
                    ->setTimeFrom($partnerBill->start_time);

                if ($eventDateTime->isFuture() && $eventDateTime->diffInHours(now()) <= 2) {
                    Notification::make()
                        ->title(__('notification.partner_show_reminder_title', ['code' => $partnerBill->code]))
                        ->body(__('notification.partner_show_reminder_body', ['code' => $partnerBill->code, 'start_time' => $eventDateTime]))
                        ->warning()
                        ->actions([
                            Action::make('open')
                                ->label('Mở chat')
                                ->url(route('chat.index', ['chat' => $partnerBill->thread_id])),
                        ])
                        ->sendToDatabase($partner);
                }
            }

            Log::info('Upcoming event reminder sent successfully', [
                'partner_bill_id' => $partnerBill->id,
                'code' => $partnerBill->code,
                'event_date' => $partnerBill->date,
                'start_time' => $partnerBill->start_time
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send upcoming event reminder', [
                'partner_bill_id' => $partnerBill->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Gửi mail thông báo đơn hàng đã hết hạn (không có đối tác nhận)
     */
    public function sendOrderExpiredNotification(PartnerBill $partnerBill): void
    {
        try {
            // Chỉ gửi cho client vì đơn hết hạn do không có partner nhận
            if ($partnerBill->client && $partnerBill->client->email) {
                $clientLocale = $this->getUserLocale($partnerBill->client);
                Mail::to($partnerBill->client->email)
                    ->queue(new PartnerBillExpired($partnerBill, $clientLocale));

                /** @var Customer|null $client */
                $client = Customer::find($partnerBill->client_id);
                Notification::make()
                    ->title(__('notification.client_order_expired_title', ['code' => $partnerBill->code]))
                    ->body(__('notification.client_order_expired_body', ['code' => $partnerBill->code]))
                    ->danger()
                    ->actions([
                        Action::make('open')
                            ->label('Xem đơn')
                            ->url(route('client-orders.dashboard', ['order' => $partnerBill->id])),
                    ])
                    ->sendToDatabase($client);

                Log::info('Order expired notification sent successfully', [
                    'partner_bill_id' => $partnerBill->id,
                    'code' => $partnerBill->code,
                    'client_email' => $partnerBill->client->email
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send order expired notification', [
                'partner_bill_id' => $partnerBill->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function sendBillCompletedReminder(PartnerBill $partnerBill): void
    {
        try {
            /** @var Partner|null $partner */
            $partner = Partner::find($partnerBill->partner_id);
            /** @var Customer|null $client */
            $client = Customer::find($partnerBill->client_id);

            if ($partner) {
                Notification::make()
                    ->title(__('notification.order_completed_title'))
                    ->body(__('notification.partner_order_completed_body', ['code' => $partnerBill->code]))
                    ->success()
                    ->sendToDatabase($partner);
            }

            if ($client) {
                Notification::make()
                    ->title(__('notification.order_completed_title'))
                    ->body(__('notification.order_completed_body', ['code' => $partnerBill->code]))
                    ->success()
                    ->sendToDatabase($client);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send bill completed reminder', [
                'partner_bill_id' => $partnerBill->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
