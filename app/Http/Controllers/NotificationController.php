<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Get the user's unread notifications
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnread()
    {
        $user = Auth::user();
        $notifications = $user->notifications()
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Process notifications to validate links
        $notifications->transform(function ($notification) {
            // If there's a link, verify the resource exists
            if ($notification->link) {
                // Extract the route and ID from link
                $url = parse_url($notification->link);
                if (isset($url['path'])) {
                    // Check if it's a course link
                    if (strpos($url['path'], '/candidate/courses/') === 0) {
                        $courseId = intval(substr($url['path'], strlen('/candidate/courses/')));
                        // Check if course exists
                        $courseExists = DB::table('courses')->where('id', $courseId)->exists();
                        if (!$courseExists) {
                            $notification->link = null;
                        }
                    }
                }
            }
            return $notification;
        });

        return response()->json([
            'notifications' => $notifications,
            'count' => $user->notifications()->whereNull('read_at')->count(),
        ]);
    }

    /**
     * Mark a notification as read
     *
     * @param  Notification  $notification
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function markAsRead(Notification $notification, Request $request)
    {
        // Security check - ensure the notification belongs to the current user
        if (Auth::id() !== $notification->user_id) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            return redirect()->route('notifications.index')
                ->with('error', 'You are not authorized to mark this notification as read');
        }

        $notification->update(['read_at' => now()]);

        // Check if the request wants JSON (AJAX request)
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'count' => Auth::user()->notifications()->whereNull('read_at')->count(),
            ]);
        }

        // For regular form submissions, redirect back to the notifications page
        return redirect()->route('notifications.index')
            ->with('success', 'Notification marked as read');
    }

    /**
     * Mark all notifications as read
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        $user->notifications()->whereNull('read_at')->update(['read_at' => now()]);

        // Check if the request wants JSON (AJAX request)
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'count' => 0,
            ]);
        }

        // For regular form submissions, redirect back to the notifications page
        return redirect()->route('notifications.index')
            ->with('success', 'All notifications marked as read');
    }

    /**
     * Get all user notifications for the notifications page
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        // Process notifications to validate links
        $notifications->getCollection()->transform(function ($notification) {
            // If there's a link, verify the resource exists
            if ($notification->link) {
                // Extract the route and ID from link
                $url = parse_url($notification->link);
                if (isset($url['path'])) {
                    // Check if it's a course link
                    if (strpos($url['path'], '/candidate/courses/') === 0) {
                        $courseId = intval(substr($url['path'], strlen('/candidate/courses/')));
                        // Check if course exists
                        $courseExists = DB::table('courses')->where('id', $courseId)->exists();
                        if (!$courseExists) {
                            $notification->link = null;
                        }
                    }
                }
            }
            return $notification;
        });

        return view('notifications.index', compact('notifications'));
    }
} 