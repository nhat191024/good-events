<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\NotificationResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * GET /api/notifications
     *
     * Query: per_page, unread
     * Response: paginated NotificationResource with meta.unread_count
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $authUser = $request->user();
        if (!$authUser) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user = User::find($authUser->id);

        $perPage = (int) min(50, max(5, (int) $request->integer('per_page', 10)));
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
        $authUser = $request->user();
        if (!$authUser) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user = User::find($authUser->id);

        /** @var DatabaseNotification $notification */
        $notification = $user->notifications()->where('id', $id)->firstOrFail();
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

        $user = User::find($authUser->id);
        $user->unreadNotifications->markAsRead();

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
        $authUser = $request->user();
        if (!$authUser) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user = User::find($authUser->id);

        $notification = $user->notifications()->where('id', $id)->firstOrFail();
        $notification->delete();

        return response()->json(['success' => true]);
    }
}
