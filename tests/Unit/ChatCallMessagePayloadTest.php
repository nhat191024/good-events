<?php

use App\Models\Call;
use App\Models\Message;
use App\Support\ChatMessagePayload;
use Tests\TestCase;

uses(TestCase::class);

it('includes the call summary used by chat history views', function () {
    $call = new Call([
        'uuid' => '01K1ABCDEF1234567890ABCDEF',
        'started_at' => '2026-08-30 09:00:00',
        'ended_at' => '2026-08-30 10:02:03',
    ]);

    $message = new Message([
        'thread_id' => 10,
        'user_id' => 20,
        'type' => Message::TYPE_CALL,
        'call_id' => 30,
        'call_duration_seconds' => 3723,
    ]);
    $message->id = 40;
    $message->setRelation('call', $call);

    expect(ChatMessagePayload::message($message)['call'])->toMatchArray([
        'id' => '01K1ABCDEF1234567890ABCDEF',
        'duration_seconds' => 3723,
    ]);
});

it('includes the sender avatar in the websocket payload', function () {
    $payload = ChatMessagePayload::broadcast([
        'id' => 40,
        'thread_id' => 10,
        'user_id' => 20,
        'type' => Message::TYPE_TEXT,
        'body' => 'Hello',
        'user' => [
            'name' => 'Sender',
            'avatar' => 'https://example.com/avatar.webp',
        ],
    ]);

    expect($payload['user'])->toMatchArray([
        'id' => 20,
        'name' => 'Sender',
        'avatar' => 'https://example.com/avatar.webp',
    ]);
});
