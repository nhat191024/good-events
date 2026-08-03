<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\NotificationResource;
use App\Models\Customer;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * GET /api/notifications
     *
     * Query: per_page, unread
     * Response: paginated NotificationResource with normalized action data and meta.unread_count
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $perPage = (int) min(50, max(5, (int) $request->integer('per_page', 10)));
        $onlyUnread = $request->boolean('unread');

        $query = $this->notificationQuery($user->id)
            ->when($onlyUnread, fn ($q) => $q->whereNull('read_at'))
            ->orderByDesc('created_at');

        $paginator = $query->paginate($perPage)->appends($request->query());

        return NotificationResource::collection($paginator)->additional([
            'meta' => [
                'unread_count' => $this->notificationQuery($user->id)
                    ->whereNull('read_at')
                    ->count(),
            ],
        ]);
    }

    /**
     * POST /api/notifications/{id}/read
     *
     * Response: { success: true, data: NotificationResource }
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function read(Request $request, string $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        /** @var DatabaseNotification $notification */
        $notification = $this->notificationQuery($user->id)->whereKey($id)->firstOrFail();
        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'data' => new NotificationResource($notification->refresh()),
        ]);
    }

    /**
     * POST /api/notifications/read-all
     *
     * Response: { success: true }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function readAll(Request $request)
    {
        $authUser = $request->user();
        if (!$authUser) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $this->notificationQuery($authUser->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * POST /api/notifications/{id}/delete
     *
     * Response: { success: true }
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $notification = $this->notificationQuery($user->id)->whereKey($id)->firstOrFail();
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
