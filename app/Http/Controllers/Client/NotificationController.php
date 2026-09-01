<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\Notification\NotificationResource;
use App\Models\Customer;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $authUser = $request->user();
        abort_if(! $authUser, 401, 'unauthenticated');

        $perPage = (int) min(50, max(5, (int) $request->integer('per_page', 10)));
        $onlyUnread = $request->boolean('unread');

        $query = $this->notificationQuery($authUser->id)
            ->when($onlyUnread, fn ($q) => $q->whereNull('read_at'))
            ->orderByDesc('created_at');

        $paginator = $query->paginate($perPage)->appends($request->query());

        return NotificationResource::collection($paginator)->additional([
            'meta' => [
                'unread_count' => $this->notificationQuery($authUser->id)
                    ->whereNull('read_at')
                    ->count(),
            ],
        ]);
    }

    public function read(Request $request, string $id)
    {
        $authUser = $request->user();
        abort_if(! $authUser, 401, 'unauthenticated');

        /** @var DatabaseNotification $notification */
        $notification = $this->notificationQuery($authUser->id)->whereKey($id)->firstOrFail();
        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'data' => new NotificationResource($notification->refresh()),
        ]);
    }

    public function readAll(Request $request)
    {
        $authUser = $request->user();
        abort_if(! $authUser, 401, 'unauthenticated');

        $this->notificationQuery($authUser->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, string $id)
    {
        $authUser = $request->user();
        abort_if(! $authUser, 401, 'unauthenticated');

        $notification = $this->notificationQuery($authUser->id)->whereKey($id)->firstOrFail();
        $notification->delete();

        return response()->json(['success' => true]);
    }

    /** @return Builder<DatabaseNotification> */
    private function notificationQuery(int $userId): Builder
    {
        return DatabaseNotification::query()
            ->where('notifiable_id', $userId)
            ->whereIn('notifiable_type', [
                User::class,
                Partner::class,
                Customer::class,
            ]);
    }
}
