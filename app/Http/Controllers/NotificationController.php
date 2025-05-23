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
     * Get all user notifications for the notifications page with filtering and sorting
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = $user->notifications();
        
        // Apply filters
        if ($request->has('read_status')) {
            if ($request->read_status === 'read') {
                $query->read();
            } elseif ($request->read_status === 'unread') {
                $query->unread();
            }
        }
        
        if ($request->has('type') && $request->type) {
            $query->ofType($request->type);
        }
        
        if ($request->has('start_date') && $request->start_date) {
            $startDate = $request->start_date;
            $endDate = $request->has('end_date') && $request->end_date ? $request->end_date : null;
            $query->inDateRange($startDate, $endDate);
        }
        
        // Apply sorting
        $sortField = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        
        // Validate sort field to prevent SQL injection
        $allowedSortFields = ['created_at', 'read_at', 'type'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }
        
        $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        
        // Get paginated results
        $notifications = $query->paginate(15)->withQueryString();
        
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

        // Get notification types for the filter dropdown
        $notificationTypes = [
            Notification::TYPE_COURSE => 'Course',
            Notification::TYPE_EXAM => 'Exam',
            Notification::TYPE_REMINDER => 'Reminder',
            Notification::TYPE_SYSTEM => 'System',
        ];

        return view('notifications.index', compact('notifications', 'notificationTypes'));
    }
    
    /**
     * Delete a notification
     *
     * @param  Notification  $notification
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Notification $notification, Request $request)
    {
        // Security check - ensure the notification belongs to the current user
        if (Auth::id() !== $notification->user_id) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            return redirect()->route('notifications.index')
                ->with('error', 'You are not authorized to delete this notification');
        }

        $notification->delete();

        // Check if the request wants JSON (AJAX request)
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'count' => Auth::user()->notifications()->whereNull('read_at')->count(),
            ]);
        }

        // For regular form submissions, redirect back to the notifications page
        return redirect()->route('notifications.index')
            ->with('success', 'Notification deleted successfully');
    }
    
    /**
     * Delete all notifications
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroyAll(Request $request)
    {
        $user = Auth::user();
        
        // Apply filters if they exist in the request
        $query = $user->notifications();
        
        if ($request->has('read_status')) {
            if ($request->read_status === 'read') {
                $query->read();
            } elseif ($request->read_status === 'unread') {
                $query->unread();
            }
        }
        
        if ($request->has('type') && $request->type) {
            $query->ofType($request->type);
        }
        
        $query->delete();

        // Check if the request wants JSON (AJAX request)
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'count' => Auth::user()->notifications()->whereNull('read_at')->count(),
            ]);
        }

        // For regular form submissions, redirect back to the notifications page
        return redirect()->route('notifications.index')
            ->with('success', 'Notifications deleted successfully');
    }
} 