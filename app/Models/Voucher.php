<?php

namespace App\Models;

use Illuminate\Support\Carbon;

use BeyondCode\Vouchers\Models\Voucher as BaseVoucher;

/**
 * @property int $id
 * @property string $code
 * @property string $model_type
 * @property int $model_id
 * @property \Illuminate\Support\Collection<array-key, mixed>|null $data
 * @property Carbon|null $expires_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $model
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voucher newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voucher newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voucher query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voucher whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voucher whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voucher whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voucher whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voucher whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voucher whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voucher whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voucher whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Voucher extends BaseVoucher
{
    /**
     * Get the discount percentage from the voucher data
     *
     * @return int|null
     */
    public function discountPercentage(): ?int
    {
        return $this->data['discount_percent'] ?? null;
    }

    /**
     * Get the discount amount from the voucher data
     *
     * @return int|null
     */
    public function maxDiscountAmount(): ?int
    {
        return $this->data['max_discount_amount'] ?? null;
    }

    /**
     * Get the minimum order amount from the voucher data
     *
     * @return int|null
     */
    public function minOrderAmount(): ?int
    {
        return $this->data['min_order_amount'] ?? null;
    }

    /**
     * Get the usage limit from the voucher data
     *
     * @return int|null
     */
    public function usageLimit(): ?int
    {
        return $this->data['usage_limit'] ?? null;
    }

    /**
     * Get the times used from the voucher data
     *
     * @return int
     */
    public function timesUsed(): int
    {
        return $this->data['times_used'] ?? 0;
    }

    /**
     * Check if the voucher is unlimited from the voucher data
     *
     * @return bool
     */
    public function isUnlimited(): bool
    {
        return $this->data['is_unlimited'] ?? false;
    }

    /**
     * Get the start at date from the voucher data
     *
     * @return Carbon|null
     */
    public function startAt(): ?Carbon
    {
        return isset($this->data['starts_at']) ? Carbon::parse($this->data['starts_at']) : null;
    }

    /**
     * Validate the voucher against the order total and other conditions
     *
     * @param int $orderTotal
     *
     * @return object { status: bool, message: string }
     */
    public function validate($orderTotal): object
    {
        $now = Carbon::now();

        if ($this->users()->wherePivot('user_id', $this->id)->exists()) {
            return (object) [
                'status' => false,
                'message' => __('voucher.already_redeemed')
            ];
        }

        if ($this->expires_at && $now->greaterThan($this->expires_at)) {
            return (object) [
                'status' => false,
                'message' => __('voucher.expired')
            ];
        }

        if ($this->startAt() && $now->lessThan($this->startAt())) {
            return (object) [
                'status' => false,
                'message' => __('voucher.not_yet_valid')
            ];
        }

        if (!$this->isUnlimited()) {
            if ($this->usageLimit() !== null && $this->timesUsed() >= $this->usageLimit()) {
                return (object) [
                    'status' => false,
                    'message' => __('voucher.usage_limit_reached')
                ];
            }
        }

        if ($this->minOrderAmount() !== null && $orderTotal < $this->minOrderAmount()) {
            return (object) [
                'status' => false,
                'message' => __('voucher.min_order_not_met')
            ];
        }

        return (object) [
            'status' => true,
            'message' => __('voucher.valid')
        ];
    }

    /**
     * Calculate the discount amount based on the order total and voucher settings
     *
     * @param int $orderTotal
     * @return int
     */
    public function getDiscountAmount($orderTotal): int
    {
        $discount = 0;

        if ($this->discountPercentage() !== null) {
            $discount = $orderTotal * ($this->discountPercentage() / 100);

            if ($this->maxDiscountAmount() !== null) {
                $discount = min($discount, $this->maxDiscountAmount());
            }
        }

        return (int) round($discount);
    }
}
