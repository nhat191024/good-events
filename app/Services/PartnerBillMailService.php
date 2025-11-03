<?php

namespace App\Services;

use App\Models\PartnerBill;
use App\Models\User;
use App\Mail\PartnerBillReceived;
use App\Mail\PartnerBillConfirmed;
use App\Mail\PartnerBillReminder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PartnerBillMailService
{
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
            if ($partnerBill->client && $partnerBill->client->email) {
                $clientLocale = $this->getUserLocale($partnerBill->client);
                Mail::to($partnerBill->client->email)
                    ->queue(new PartnerBillReceived($partnerBill, 'client', $clientLocale));
            }

            $eligiblePartners = User::whereHas('partnerServices', function ($query) use ($partnerBill) {
                $query->where('category_id', $partnerBill->category_id)
                    ->where('status', 'approved');
            })->whereNotNull('email')->get();

            foreach ($eligiblePartners as $partner) {
                $partnerLocale = $this->getUserLocale($partner);
                Mail::to($partner->email)
                    ->queue(new PartnerBillReceived($partnerBill, 'partner', $partnerLocale));
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
            if ($partnerBill->partner && $partnerBill->partner->email) {
                $partnerLocale = $this->getUserLocale($partnerBill->partner);
                Mail::to($partnerBill->partner->email)
                    ->queue(new PartnerBillConfirmed($partnerBill, $partnerLocale));
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
     */
    public function sendUpcomingEventReminder(PartnerBill $partnerBill): void
    {
        try {
            // Chỉ gửi reminder cho đơn đã được thanh toán
            if (!$partnerBill->isPaid()) {
                return;
            }

            // Gửi mail cho client
            if ($partnerBill->client && $partnerBill->client->email) {
                $clientLocale = $this->getUserLocale($partnerBill->client);
                Mail::to($partnerBill->client->email)
                    ->send(new PartnerBillReminder($partnerBill, 'client', $clientLocale));
            }

            // Gửi mail cho partner
            if ($partnerBill->partner && $partnerBill->partner->email) {
                $partnerLocale = $this->getUserLocale($partnerBill->partner);
                Mail::to($partnerBill->partner->email)
                    ->send(new PartnerBillReminder($partnerBill, 'partner', $partnerLocale));
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
     * Lấy danh sách đơn hàng cần gửi reminder (sự kiện sẽ diễn ra trong 24 giờ tới)
     */
    public function getUpcomingPartnerBills(): \Illuminate\Database\Eloquent\Collection
    {
        $tomorrow = Carbon::now()->addDay();
        $dayAfterTomorrow = Carbon::now()->addDays(2);

        return PartnerBill::where('status', \App\Enum\PartnerBillStatus::COMPLETED)
            ->whereBetween('date', [$tomorrow->startOfDay(), $dayAfterTomorrow->startOfDay()])
            ->with(['client', 'partner', 'event', 'category'])
            ->get();
    }

    /**
     * Gửi reminder cho tất cả đơn hàng sắp đến giờ
     */
    public function sendAllUpcomingReminders(): int
    {
        $upcomingBills = $this->getUpcomingPartnerBills();
        $sentCount = 0;

        foreach ($upcomingBills as $bill) {
            $this->sendUpcomingEventReminder($bill);
            $sentCount++;
        }

        Log::info('Batch reminder sending completed', [
            'total_reminders_sent' => $sentCount
        ]);

        return $sentCount;
    }
}
