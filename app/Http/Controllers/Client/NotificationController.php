<?php

namespace App\Http\Controllers\Client;

use App\Http\Resources\Notification\NotificationResource;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_if(!$user, 401, 'unauthenticated');

        $perPage    = (int) min(50, max(5, (int) $request->integer('per_page', 10)));
        $onlyUnread = $request->boolean('unread');

        $query = $user->notifications()
            ->when($onlyUnread, fn ($q) => $q->whereNull('read_at'))
            ->orderByDesc('created_at');

        $paginator = $query->paginate($perPage)->appends($request->query());

        return NotificationResource::collection($paginator)->additional([
            'meta' => [
                'unread_count' => $user->unreadNotifications()->count(),
            ],
        ]);
    }

    public function read(Request $request, string $id)
    {
        $user = $request->user();
        abort_if(!$user, 401, 'unauthenticated');

        /** @var DatabaseNotification $notification */
        $notification = $user->notifications()->where('id', $id)->firstOrFail();
        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'data'    => new NotificationResource($notification->refresh()),
        ]);
    }

    public function readAll(Request $request)
    {
        $user = $request->user();
        abort_if(!$user, 401, 'unauthenticated');

        $user->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        abort_if(!$user, 401, 'unauthenticated');

        $notification = $user->notifications()->where('id', $id)->firstOrFail();
        $notification->delete();

        return response()->json(['success' => true]);
    }
}
