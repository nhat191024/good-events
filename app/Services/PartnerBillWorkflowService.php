<?php

namespace App\Services;

use App\Enum\PartnerBillStatus;
use App\Models\PartnerBill;

class PartnerBillWorkflowService
{
    public function isActionLocked(int $partnerId, PartnerBill $targetBill): bool
    {
        if ($targetBill->completion_reminder_started_at) {
            return false;
        }

        return PartnerBill::query()
            ->where('partner_id', $partnerId)
            ->whereIn('status', [PartnerBillStatus::CONFIRMED, PartnerBillStatus::IN_JOB])
            ->whereNotNull('completion_reminder_started_at')
            ->whereKeyNot($targetBill->getKey())
            ->exists();
    }
}
