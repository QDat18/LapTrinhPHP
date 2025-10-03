<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display all notifications
     */
    public function index()
    {
        $notifications = Auth::user()->notifications()
            ->latest()
            ->paginate(20);
        
        return view('notifications.index', compact('notifications'));
    }
    
    /**
     * Get unread notifications count
     */
    public function unreadCount()
    {
        $count = Auth::user()->notifications()
            ->where('is_read', false)
            ->count();
        
        return response()->json(['count' => $count]);
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
        
        return response()->json($notifications);
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
        Auth::user()->notifications()
            ->where('is_read', false)
            ->update(['is_read' => true]);
        
        return response()->json([
            'success' => true,
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
    public function deleteAllRead()
    {
        Auth::user()->notifications()
            ->where('is_read', true)
            ->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'All read notifications deleted'
        ]);
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
     * Update notification preferences
     */
    public function updatePreferences(Request $request)
    {
        $preferences = $request->validate([
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'application_updates' => 'boolean',
            'message_alerts' => 'boolean',
            'opportunity_recommendations' => 'boolean',
        ]);
        
        // Store preferences in user settings (you might want to create a user_settings table)
        Auth::user()->update([
            'notification_preferences' => json_encode($preferences)
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated'
        ]);
    }
    
    /**
     * Send test notification
     */
    public function sendTest()
    {
        Notification::create([
            'user_id' => Auth::id(),
            'notification_type' => 'System',
            'title' => 'Test Notification',
            'content' => 'This is a test notification to verify your notification settings are working correctly.',
            'priority' => 'low',
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Test notification sent'
        ]);
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