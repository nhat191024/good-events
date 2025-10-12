<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use Cmgmyr\Messenger\Models\Thread as BaseThread;
use Cmgmyr\Messenger\Models\Models;
use Cmgmyr\Messenger\Models\Message;

class Thread extends BaseThread
{
    /**
     * Returns threads that the user is associated with and sorts start from unread.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUserOrderByNotReadMessages(Builder $query, $userId)
    {
        $participantTable = Models::table('participants');
        $threadsTable = Models::table('threads');
        $tablePrefix = $this->getConnection()->getTablePrefix();

        $orderBy = 'IF(`' . $participantTable . '`.last_read IS NULL, 2, IF (`' . $threadsTable . '`.updated_at>`' . $tablePrefix . $participantTable . '`.last_read, 2, 0)) DESC';
        return $query->join($participantTable, $this->getQualifiedKeyName(), '=', $participantTable . '.thread_id')
            ->where($participantTable . '.user_id', $userId)
            ->whereNull($participantTable . '.deleted_at')
            ->orderByRaw($orderBy)
            ->select($threadsTable . '.*');
    }

    /**
     * Get the latest message associated with the thread.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
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
}
