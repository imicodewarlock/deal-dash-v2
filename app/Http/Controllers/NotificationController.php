<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends BaseController
{
    /**
     * Retrieve all notifications for the authenticated user
     *
     * Undocumented function long description
     *
     * @param Request $request Description
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNotifications(Request $request)
    {
        // Get the authenticated user
        $user = $request->user();

        $perPage = $request->get('per_page', 10);

        $notifications = $user->notifications()->orderBy('created_at', 'desc')->paginate($perPage);

        if ($notifications->isEmpty()) {
            return $this->sendError(__('notification.all_records_err'), Response::HTTP_OK);
        }

        return $this->sendSuccess(__('notification.all_records'), $notifications->items(), Response::HTTP_OK,
            [
                'current_page' => $notifications->currentPage(),
                'total_count' => $notifications->total(),
                'per_page' => $notifications->perPage(),
                'total_pages' => $notifications->lastPage(),
                'has_more_pages' => $notifications->hasMorePages(),
            ],
            [
                'next_page_url' => $notifications->nextPageUrl(),
                'prev_page_url' => $notifications->previousPageUrl(),
                'first_page_url' => $notifications->url(1),
                'last_page_url' => $notifications->url($notifications->lastPage()),
            ]
        );
    }

    /**
     * Retrieve only unread notifications for the authenticated user
     *
     * Undocumented function long description
     *
     * @param Request $request Description
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnreadNotifications(Request $request)
    {
        // Get the authenticated user
        $user = $request->user();

        $perPage = $request->get('per_page', 10);

        $unreadNotifications = $user->unreadNotifications()->orderBy('created_at', 'desc')->paginate($perPage);

        if ($unreadNotifications->isEmpty()) {
            return $this->sendError(__('notification.all_records_err'), Response::HTTP_OK);
        }

        return $this->sendSuccess(__('notification.all_records'), $unreadNotifications->items(), Response::HTTP_OK,
            [
                'current_page' => $unreadNotifications->currentPage(),
                'total_count' => $unreadNotifications->total(),
                'per_page' => $unreadNotifications->perPage(),
                'total_pages' => $unreadNotifications->lastPage(),
                'has_more_pages' => $unreadNotifications->hasMorePages(),
            ],
            [
                'next_page_url' => $unreadNotifications->nextPageUrl(),
                'prev_page_url' => $unreadNotifications->previousPageUrl(),
                'first_page_url' => $unreadNotifications->url(1),
                'last_page_url' => $unreadNotifications->url($unreadNotifications->lastPage()),
            ]
        );
    }

    public function markAsRead(Request $request, $notificationId)
    {
        $user = $request->user();
        $notification = $user->unreadNotifications()->find($notificationId);

        if ($notification) {
            $notification->markAsRead();
            return $this->sendSuccess(__('notification.read'));
        }

        return $this->sendError(__('notification.not_found'), Response::HTTP_OK);
    }

    public function markAsUnread(Request $request, $notificationId)
    {
        $user = $request->user();
        $notification = $user->notifications()->find($notificationId);

        if ($notification) {
            $notification->update(['read_at' => null]);
            return $this->sendSuccess(__('notification.unread'));
        }

        return $this->sendError(__('notification.not_found'), Response::HTTP_OK);
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        if ($user->notifications->isEmpty()) {
            return $this->sendError(__('notification.all_records_err'), Response::HTTP_OK);
        }
        $user->notifications->markAsRead();
        return $this->sendSuccess(__('notification.read_all'));
    }

    public function markAllAsUnread(Request $request)
    {
        $user = $request->user();
        if ($user->notifications->isEmpty()) {
            return $this->sendError(__('notification.all_records_err'), Response::HTTP_OK);
        }
        $user->notifications()->update(['read_at' => null]);
        return $this->sendSuccess(__('notification.unread_all'));
    }

    public function deleteSingle(Request $request, $notificationId)
    {
        $user = $request->user();
        $notification = $user->notifications()->find($notificationId);

        if ($notification) {
            $notification->delete();
            return $this->sendSuccess(__('notification.deleted'));
        }

        return $this->sendError(__('notification.not_found'), Response::HTTP_OK);
    }

    public function deleteAll(Request $request)
    {
        $user = $request->user();
        if ($user->notifications->isEmpty()) {
            return $this->sendError(__('notification.all_records_err'), Response::HTTP_OK);
        }
        $user->notifications()->delete();
        return $this->sendSuccess(__('notification.unread_all'));
    }
}
