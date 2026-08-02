<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Cmgmyr\Messenger\Models\Models;
use Cmgmyr\Messenger\Models\Participant;
use Cmgmyr\Messenger\Models\Thread as BaseThread;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $subject
 * @property CarbonImmutable|null $deleted_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read PartnerBill|null $bill
 * @property-read mixed $latest_message
 * @property-read Message|null $latestMessage
 * @property-read Collection<int, Message> $messages
 * @property-read int|null $messages_count
 * @property-read Collection<int, Participant> $participants
 * @property-read int|null $participants_count
 * @property-read Collection<int, User> $users
 * @property-read int|null $users_count
 *
 * @method static Builder<static>|Thread between(array $participants)
 * @method static Builder<static>|Thread betweenOnly(array $participants)
 * @method static Builder<static>|Thread forUser($userId)
 * @method static Builder<static>|Thread forUserOrderByNotReadMessages($userId)
 * @method static Builder<static>|Thread forUserWithNewMessages($userId)
 * @method static Builder<static>|Thread newModelQuery()
 * @method static Builder<static>|Thread newQuery()
 * @method static Builder<static>|Thread onlyTrashed()
 * @method static Builder<static>|Thread query()
 * @method static Builder<static>|Thread whereCreatedAt($value)
 * @method static Builder<static>|Thread whereDeletedAt($value)
 * @method static Builder<static>|Thread whereId($value)
 * @method static Builder<static>|Thread whereSubject($value)
 * @method static Builder<static>|Thread whereUpdatedAt($value)
 * @method static Builder<static>|Thread withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Thread withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Thread extends BaseThread
{
    public function calls(): HasMany
    {
        return $this->hasMany(Call::class);
    }

    /**
     * Returns threads that the user is associated with and sorts start from unread.
     *
     * @param  int  $userId
     * @return Builder
     */
    public function scopeForUserOrderByNotReadMessages(Builder $query, $userId)
    {
        $participantTable = Models::table('participants');
        $threadsTable = Models::table('threads');
        $tablePrefix = $this->getConnection()->getTablePrefix();

        $orderBy = 'IF(`'.$participantTable.'`.last_read IS NULL, 2, IF (`'.$threadsTable.'`.updated_at>`'.$tablePrefix.$participantTable.'`.last_read, 2, 0)) DESC';

        return $query->join($participantTable, $this->getQualifiedKeyName(), '=', $participantTable.'.thread_id')
            ->where($participantTable.'.user_id', $userId)
            ->whereNull($participantTable.'.deleted_at')
            ->orderByRaw($orderBy)
            ->select($threadsTable.'.*');
    }

    /**
     * Get the latest message associated with the thread.
     *
     * @return HasOne
     */
    public function latestMessage()
    {
        return $this->hasOne(Message::class, 'thread_id')->latestOfMany();
    }

    public function getLatestMessageAttribute()
    {
        if ($this->relationLoaded('latestMessage')) {
            return $this->getRelation('latestMessage');
        }

        return $this->messages()->latest()->first();
    }

    public function bill()
    {
        return $this->hasOne(PartnerBill::class);
    }
}
