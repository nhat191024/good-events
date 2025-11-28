<?php

namespace App\Services;

use App\Models\PartnerBill;
use App\Models\User;
use App\Mail\PartnerBillReceived;
use App\Mail\PartnerBillConfirmed;
use App\Mail\PartnerBillReminder;
use App\Mail\PartnerBillExpired;
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

    /**
     * Lấy danh sách đơn hàng đã hết hạn (PENDING và đã quá thời gian chờ)
     *
     * @param int $hoursThreshold Số giờ để xác định đơn hết hạn (mặc định 48 giờ)
     */
    public function getExpiredPartnerBills(int $hoursThreshold = 48): \Illuminate\Database\Eloquent\Collection
    {
        $expiredTime = Carbon::now()->subHours($hoursThreshold);

        return PartnerBill::where('status', \App\Enum\PartnerBillStatus::PENDING)
            ->whereNull('partner_id')
            ->where('created_at', '<=', $expiredTime)
            ->with(['client', 'event', 'category'])
            ->get();
    }

    /**
     * Gửi thông báo hết hạn cho tất cả đơn hàng đã hết hạn
     *
     * @param int $hoursThreshold Số giờ để xác định đơn hết hạn
     */
    public function sendAllExpiredNotifications(int $hoursThreshold = 48): int
    {
        $expiredBills = $this->getExpiredPartnerBills($hoursThreshold);
        $sentCount = 0;

        foreach ($expiredBills as $bill) {
            $this->sendOrderExpiredNotification($bill);
            $sentCount++;
        }

        Log::info('Batch expired notification sending completed', [
            'total_notifications_sent' => $sentCount,
            'hours_threshold' => $hoursThreshold
        ]);

        return $sentCount;
    }
}
