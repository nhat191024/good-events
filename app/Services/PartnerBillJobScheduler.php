<?php

namespace App\Services;

use App\Jobs\PartnerBillFirstJob;
use App\Jobs\PartnerBillSecondJob;
use App\Jobs\PartnerBillThirdJob;
use App\Models\PartnerBill;
use Illuminate\Support\Carbon;

class PartnerBillJobScheduler
{
    public function scheduleFirstCheck(PartnerBill $partnerBill): void
    {
        $reminderTime = $this->upcomingReminderAt($partnerBill);

        if (! $reminderTime) {
            return;
        }

        if ($reminderTime->isFuture()) {
            PartnerBillFirstJob::dispatch($partnerBill)->delay($reminderTime);

            return;
        }

        PartnerBillFirstJob::dispatch($partnerBill);
    }

    public function scheduleExpirationCheck(PartnerBill $partnerBill): void
    {
        $expirationTime = $this->expirationCheckAt($partnerBill);

        if (! $expirationTime) {
            return;
        }

        if ($expirationTime->isFuture()) {
            PartnerBillSecondJob::dispatch($partnerBill)->delay($expirationTime);

            return;
        }

        PartnerBillSecondJob::dispatch($partnerBill);
    }

    public function scheduleCompletionReminder(PartnerBill $partnerBill): void
    {
        $completionReminderTime = $this->completionReminderAt($partnerBill);

        if (! $completionReminderTime) {
            return;
        }

        if ($completionReminderTime->isFuture()) {
            PartnerBillThirdJob::dispatch($partnerBill)->delay($completionReminderTime);

            return;
        }

        PartnerBillThirdJob::dispatch($partnerBill);
    }

    public function eventStartsAt(PartnerBill $partnerBill): ?Carbon
    {
        if (! $partnerBill->date || ! $partnerBill->start_time) {
            return null;
        }

        return $partnerBill->date->copy()->setTimeFrom($partnerBill->start_time);
    }

    public function eventEndsAt(PartnerBill $partnerBill): ?Carbon
    {
        $eventStartsAt = $this->eventStartsAt($partnerBill);

        if (! $eventStartsAt) {
            return null;
        }

        if (! $partnerBill->end_time) {
            return $eventStartsAt->copy();
        }

        $eventEndsAt = $partnerBill->date->copy()->setTimeFrom($partnerBill->end_time);

        if ($eventEndsAt->lessThanOrEqualTo($eventStartsAt)) {
            $eventEndsAt->addDay();
        }

        return $eventEndsAt;
    }

    public function upcomingReminderAt(PartnerBill $partnerBill): ?Carbon
    {
        return $this->eventStartsAt($partnerBill)?->copy()->subHours(2);
    }

    public function expirationCheckAt(PartnerBill $partnerBill): ?Carbon
    {
        return $partnerBill->created_at?->copy()->addHours(48);
    }

    public function completionReminderAt(PartnerBill $partnerBill): ?Carbon
    {
        return $this->eventEndsAt($partnerBill)?->copy()->addMinutes(20);
    }

    public function shouldWaitForUpcomingReminder(PartnerBill $partnerBill): bool
    {
        $reminderTime = $this->upcomingReminderAt($partnerBill);

        return $reminderTime && $reminderTime->isFuture();
    }

    public function shouldWaitForCompletionReminder(PartnerBill $partnerBill): bool
    {
        $completionReminderTime = $this->completionReminderAt($partnerBill);

        return $completionReminderTime && $completionReminderTime->isFuture();
    }

    public function shouldSendUpcomingReminder(PartnerBill $partnerBill): bool
    {
        $eventStartsAt = $this->eventStartsAt($partnerBill);
        $reminderTime = $this->upcomingReminderAt($partnerBill);

        return $eventStartsAt
            && $reminderTime
            && now()->greaterThanOrEqualTo($reminderTime)
            && now()->lessThan($eventStartsAt);
    }
}
