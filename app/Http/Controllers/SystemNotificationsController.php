<?php

namespace App\Http\Controllers;

use App\Traits\HttpResponses;
use App\Models\SystemNotifications;
use App\Http\Resources\SystemNotificationsResource;
use App\Http\Requests\SystemNotificationsRequest;
use Illuminate\Support\Facades\Auth;

class SystemNotificationsController extends Controller
{
    use HttpResponses;


    public function unreadNotificationsAdmin(SystemNotificationsRequest $request)
    {
        // Authenticate user first
        $user = Auth::user();

        if (!$user) {
            return $this->error(null, 'Unauthenticated.', 401);
        }

        // Only admin users are allowed
        if ($user->user_type !== 'admin') {
            return $this->error(null, 'Unauthorized to view notifications.', 403);
        }

        // Fetch notifications where receiver_role = 'admin'
        // This includes role-based notifications (broadcast) and individual ones
        $notifications = SystemNotifications::with(['sender', 'barangay'])
            ->where('receiver_role', 'admin')
            ->where(function ($query) use ($user) {
                // Include notifications specifically assigned to this admin (receiver_id)
                $query->where('receiver_id', $user->id)
                    ->orWhereNull('receiver_id'); // role-based broadcast
            })
            ->where('status', 'unread')
            ->latest()
            ->get();

        // Base query for unread admin notifications
        $query = SystemNotifications::with(['sender', 'barangay'])
            ->where('receiver_role', 'admin')
            ->where(function ($query) use ($user) {
                $query->where('receiver_id', $user->id)
                    ->orWhereNull('receiver_id'); // role-based broadcast
            })
            ->where('status', 'unread');

        // Get unread count
        $unreadCount = (clone $query)->count();

        return $this->success(
            [
                'notifications' => SystemNotificationsResource::collection($notifications),
                'unread_notifications' => $unreadCount,
            ],
            'Unread admin notifications retrieved successfully.'
        );
    }

    public function markAsReadAdmin(SystemNotificationsRequest $request, $id)
    {
        // Authenticate user first
        $user = Auth::user();

        if (!$user) {
            return $this->error(null, 'Unauthenticated.', 401);
        }

        // Only admin users can access this
        if ($user->user_type !== 'admin') {
            return $this->error(null, 'Unauthorized.', 403);
        }

        // Find admin notification (broadcast or personal)
        $notification = SystemNotifications::where('id', $id)
            ->where('receiver_role', 'admin')
            ->whereNull('receiver_id') // admin broadcast
            ->first();

        if (!$notification) {
            return $this->error(null, 'Notification not found.', 404);
        }

        if ($notification->status === 'read') {
            return $this->success(
                new SystemNotificationsResource($notification),
                'Notification already marked as read.'
            );
        }

        $notification->update([
            'status'  => 'read',
            'read_at' => now(),
        ]);

        return $this->success(
            new SystemNotificationsResource($notification),
            'Admin notification marked as read successfully.'
        );
    }

    public function markAllAsReadAdmin(SystemNotificationsRequest $request)
    {
        // Authenticate user first
        $user = Auth::user();

        if (!$user) {
            return $this->error(null, 'Unauthenticated.', 401);
        }

        // Only admin users are allowed
        if ($user->user_type !== 'admin') {
            return $this->error(null, 'Unauthorized.', 403);
        }

        // Mark all unread admin broadcast notifications as read
        $updatedCount = SystemNotifications::where('receiver_role', 'admin')
            ->whereNull('receiver_id') // admin broadcast only
            ->where('status', 'unread')
            ->update([
                'status'  => 'read',
                'read_at' => now(),
            ]);

        return $this->success(
            ['updated_count' => $updatedCount],
            'All admin notifications marked as read successfully.'
        );
    }



    public function unreadByRole(SystemNotificationsRequest $request)
    {
        // Authenticate user first
        $user = Auth::user();

        if (!$user) {
            return $this->error(null, 'Unauthenticated.', 401);
        }

        // Base query for unread notifications by role
        $query = SystemNotifications::with(['sender', 'barangay'])
            ->where('receiver_id', $user->id)
            ->where('receiver_role', $user->user_type) // admin, barangay, user
            ->where('status', 'unread');

        // Count unread notifications
        $unreadCount = (clone $query)->count();

        // Fetch notifications
        $notifications = $query->latest()->get();

        return $this->success(
            [
                'notifications' => SystemNotificationsResource::collection($notifications),
                'unread_notifications' => $unreadCount,
            ],
            'Unread notifications retrieved successfully.'
        );
    }



    public function markAsRead(SystemNotificationsRequest $request, $id)
    {
        // Authenticate user first
        $user = Auth::user();

        if (!$user) {
            return $this->error(null, 'Unauthenticated.', 401);
        }

        $notification = SystemNotifications::where('id', $id)
            ->where('receiver_id', $user->id)
            ->where('receiver_role', $user->user_type)
            ->first();

        if (!$notification) {
            return $this->error(null, 'Notification not found.', 404);
        }

        if ($notification->status === 'read') {
            return $this->success(
                new SystemNotificationsResource($notification),
                'Notification already marked as read.'
            );
        }

        $notification->update([
            'status'  => 'read',
            'read_at' => now(),
        ]);

        return $this->success(
            new SystemNotificationsResource($notification),
            'Notification marked as read successfully.'
        );
    }


    public function markAllAsRead(SystemNotificationsRequest $request)
    {
        // Authenticate user first
        $user = Auth::user();

        if (!$user) {
            return $this->error(null, 'Unauthenticated.', 401);
        }

        $updatedCount = SystemNotifications::where('receiver_id', $user->id)
            ->where('receiver_role', $user->user_type)
            ->where('status', 'unread')
            ->update([
                'status'  => 'read',
                'read_at' => now(),
            ]);

        return $this->success(
            ['updated_count' => $updatedCount],
            'All notifications marked as read successfully.'
        );
    }
}
