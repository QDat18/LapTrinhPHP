<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display all notifications with filters
     */
    public function index(Request $request)
    {
        $query = Auth::user()->notifications();

        // Filter by type
        if ($request->filled('type')) {
            $query->where('notification_type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_read', $request->status === 'read');
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $notifications = $query->latest()->paginate(20);

        // Stats
        $stats = [
            'total' => Auth::user()->notifications()->count(),
            'unread' => Auth::user()->notifications()->where('is_read', false)->count(),
            'high_priority' => Auth::user()->notifications()
                ->where('priority', 'high')
                ->where('is_read', false)
                ->count(),
            'today' => Auth::user()->notifications()
                ->whereDate('created_at', today())
                ->count(),
        ];

        return view('notifications.index', compact('notifications', 'stats'));
    }
    
    /**
     * Get unread notifications count (for API)
     */
    public function getUnreadCount()
    {
        $count = Auth::user()->notifications()
            ->where('is_read', false)
            ->count();
        
        return response()->json(['count' => $count]);
    }
    
    /**
     * Get unread count (legacy method)
     */
    public function unreadCount()
    {
        return $this->getUnreadCount();
    }
    
    /**
     * Get recent notifications (for dropdown)
     */
    public function recent()
    {
        $notifications = Auth::user()->notifications()
            ->latest()
            ->take(10)
            ->get();
        
        return response()->json([
            'success' => true,
            'notifications' => $notifications
        ]);
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        
        $notification->update(['is_read' => true]);
        
        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $count = Auth::user()->notifications()
            ->where('is_read', false)
            ->update(['is_read' => true]);
        
        return response()->json([
            'success' => true,
            'count' => $count,
            'message' => 'All notifications marked as read'
        ]);
    }
    
    /**
     * Delete notification
     */
    public function destroy($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        
        $notification->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification deleted'
        ]);
    }
    
    /**
     * Delete all read notifications
     */
    public function deleteRead()
    {
        $count = Auth::user()->notifications()
            ->where('is_read', true)
            ->delete();
        
        return response()->json([
            'success' => true,
            'count' => $count,
            'message' => 'All read notifications deleted'
        ]);
    }
    
    /**
     * Delete all read notifications (legacy)
     */
    public function deleteAllRead()
    {
        return $this->deleteRead();
    }
    
    /**
     * Get notifications by type
     */
    public function getByType($type)
    {
        $validTypes = ['Application', 'Message', 'Video Call', 'Review', 'System', 'Opportunity'];
        
        if (!in_array($type, $validTypes)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid notification type'
            ], 400);
        }
        
        $notifications = Auth::user()->notifications()
            ->where('notification_type', $type)
            ->latest()
            ->paginate(20);
        
        return response()->json($notifications);
    }
    
    /**
     * Get notification statistics
     */
    public function statistics()
    {
        $user = Auth::user();
        
        $stats = [
            'total' => $user->notifications()->count(),
            'unread' => $user->notifications()->where('is_read', false)->count(),
            'by_type' => [
                'application' => $user->notifications()->where('notification_type', 'Application')->count(),
                'message' => $user->notifications()->where('notification_type', 'Message')->count(),
                'system' => $user->notifications()->where('notification_type', 'System')->count(),
                'opportunity' => $user->notifications()->where('notification_type', 'Opportunity')->count(),
            ],
            'by_priority' => [
                'high' => $user->notifications()->where('priority', 'high')->where('is_read', false)->count(),
                'medium' => $user->notifications()->where('priority', 'medium')->where('is_read', false)->count(),
                'low' => $user->notifications()->where('priority', 'low')->where('is_read', false)->count(),
            ]
        ];
        
        return response()->json($stats);
    }
}