<?php

namespace App\Http\Controllers;

use App\Models\VideoCall;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VideoCallController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Danh sách video calls
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Lấy calls từ conversations mà user tham gia
        $query = VideoCall::with(['conversation.participants', 'initiator'])
            ->whereHas('conversation.participants', function($q) use ($user) {
                $q->where('user_id', $user->user_id)
                  ->where('is_active', true);
            });
        
        // Filter theo status
        if ($request->filled('status')) {
            $query->where('call_status', $request->status);
        }
        
        // Filter theo type
        if ($request->filled('type')) {
            $query->where('call_type', $request->type);
        }
        
        // Filter theo date range
        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }
        
        $calls = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Tính stats
        $stats = [
            'total_calls' => VideoCall::whereHas('conversation.participants', function($q) use ($user) {
                $q->where('user_id', $user->user_id);
            })->count(),
            
            'total_duration' => VideoCall::whereHas('conversation.participants', function($q) use ($user) {
                $q->where('user_id', $user->user_id);
            })->where('call_status', 'ended')->sum('duration'),
            
            'missed_calls' => VideoCall::whereHas('conversation.participants', function($q) use ($user) {
                $q->where('user_id', $user->user_id);
            })->where('call_status', 'missed')
            ->where('initiated_by', '!=', $user->user_id)
            ->count()
        ];
        
        return view('video-calls.index', compact('calls', 'stats'));
    }

    // Initiate video call
    public function initiate(Request $request)
    {
        $validated = $request->validate([
            'conversation_id' => 'required|exists:conversations,conversation_id',
            'call_type' => 'required|in:audio,video'
        ]);
        
        $user = Auth::user();
        
        // Kiểm tra quyền
        $participant = ConversationParticipant::where('conversation_id', $validated['conversation_id'])
            ->where('user_id', $user->user_id)
            ->where('is_active', true)
            ->first();
            
        if (!$participant) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Kiểm tra xem có call đang active không
        $activeCall = VideoCall::where('conversation_id', $validated['conversation_id'])
            ->whereIn('call_status', ['initiated', 'ringing', 'active'])
            ->first();
            
        if ($activeCall) {
            return response()->json([
                'success' => false,
                'error' => 'Đã có cuộc gọi đang diễn ra'
            ], 400);
        }
        
        DB::beginTransaction();
        try {
            // Generate unique room ID
            $roomId = Str::uuid()->toString();
            
            // Create video call
            $call = VideoCall::create([
                'conversation_id' => $validated['conversation_id'],
                'initiated_by' => $user->user_id,
                'call_type' => $validated['call_type'],
                'call_status' => 'ringing',
                'room_id' => $roomId,
                'created_at' => now()
            ]);
            
            // Send notifications to other participants
            $this->sendCallNotifications($call, 'initiated');
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'call' => $call,
                'room_id' => $roomId,
                'join_url' => route('video-calls.room', $call->call_id)
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Join video call
    public function join(Request $request, $callId)
    {
        $user = Auth::user();
        
        $call = VideoCall::with('conversation')->findOrFail($callId);
        
        // Kiểm tra quyền
        $participant = ConversationParticipant::where('conversation_id', $call->conversation_id)
            ->where('user_id', $user->user_id)
            ->where('is_active', true)
            ->first();
            
        if (!$participant) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Kiểm tra call status
        if (!in_array($call->call_status, ['ringing', 'active'])) {
            return response()->json([
                'success' => false,
                'error' => 'Cuộc gọi đã kết thúc hoặc không khả dụng'
            ], 400);
        }
        
        DB::beginTransaction();
        try {
            // Update call status to active if ringing
            if ($call->call_status === 'ringing') {
                $call->update([
                    'call_status' => 'active',
                    'started_at' => now()
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'call' => $call,
                'room_id' => $call->room_id,
                'join_url' => route('video-calls.room', $call->call_id)
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Video call room (view)
    public function room($callId)
    {
        $user = Auth::user();
        
        $call = VideoCall::with(['conversation.participants.user', 'initiator'])
            ->findOrFail($callId);
        
        // Kiểm tra quyền
        $participant = ConversationParticipant::where('conversation_id', $call->conversation_id)
            ->where('user_id', $user->user_id)
            ->where('is_active', true)
            ->first();
            
        if (!$participant) {
            abort(403, 'Bạn không có quyền tham gia cuộc gọi này');
        }
        
        // Kiểm tra call status
        if (!in_array($call->call_status, ['ringing', 'active'])) {
            return view('video-calls.ended', compact('call'));
        }
        
        // Update call status if first time joining
        if ($call->call_status === 'ringing' && $user->user_id !== $call->initiated_by) {
            $call->update([
                'call_status' => 'active',
                'started_at' => now()
            ]);
        }
        
        $participants = $call->conversation->participants()
            ->where('is_active', true)
            ->with('user')
            ->get();
        
        return view('video-calls.room', compact('call', 'participants'));
    }

    // End video call
    public function end(Request $request, $callId)
    {
        $user = Auth::user();
        
        $call = VideoCall::findOrFail($callId);
        
        // Kiểm tra quyền (chỉ initiator hoặc participant)
        $participant = ConversationParticipant::where('conversation_id', $call->conversation_id)
            ->where('user_id', $user->user_id)
            ->where('is_active', true)
            ->first();
            
        if (!$participant) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        if (!in_array($call->call_status, ['ringing', 'active'])) {
            return response()->json([
                'success' => false,
                'error' => 'Call đã kết thúc'
            ], 400);
        }
        
        DB::beginTransaction();
        try {
            $endedAt = now();
            $duration = 0;
            
            if ($call->started_at) {
                $duration = $endedAt->diffInSeconds($call->started_at);
            }
            
            $call->update([
                'call_status' => 'ended',
                'ended_at' => $endedAt,
                'duration' => $duration
            ]);
            
            // Send notifications
            $this->sendCallNotifications($call, 'ended');
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'duration' => $duration,
                'message' => 'Cuộc gọi đã kết thúc'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Decline video call
    public function decline($callId)
    {
        $user = Auth::user();
        
        $call = VideoCall::findOrFail($callId);
        
        // Chỉ decline được khi đang ringing
        if ($call->call_status !== 'ringing') {
            return response()->json([
                'success' => false,
                'error' => 'Không thể decline call này'
            ], 400);
        }
        
        // Không phải initiator mới decline được
        if ($call->initiated_by === $user->user_id) {
            return response()->json([
                'success' => false,
                'error' => 'Người gọi không thể decline'
            ], 400);
        }
        
        DB::beginTransaction();
        try {
            $call->update([
                'call_status' => 'declined',
                'ended_at' => now()
            ]);
            
            // Notify initiator
            DB::table('notifications')->insert([
                'user_id' => $call->initiated_by,
                'notification_type' => 'Video Call',
                'title' => 'Cuộc gọi bị từ chối',
                'content' => $user->first_name . ' đã từ chối cuộc gọi',
                'related_id' => $call->call_id,
                'related_type' => 'call',
                'created_at' => now()
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Đã từ chối cuộc gọi'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Mark call as missed
    public function markMissed($callId)
    {
        $call = VideoCall::findOrFail($callId);
        
        // Auto-mark as missed after 60 seconds of ringing
        if ($call->call_status === 'ringing') {
            $timeSinceCreated = now()->diffInSeconds($call->created_at);
            
            if ($timeSinceCreated >= 60) {
                $call->update([
                    'call_status' => 'missed',
                    'ended_at' => now()
                ]);
                
                // Send notifications
                $this->sendCallNotifications($call, 'missed');
            }
        }
        
        return response()->json([
            'success' => true,
            'call_status' => $call->call_status
        ]);
    }

    // Get active call for conversation
    public function getActiveCall($conversationId)
    {
        $user = Auth::user();
        
        // Kiểm tra quyền
        $participant = ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', $user->user_id)
            ->where('is_active', true)
            ->first();
            
        if (!$participant) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $activeCall = VideoCall::where('conversation_id', $conversationId)
            ->whereIn('call_status', ['initiated', 'ringing', 'active'])
            ->with('initiator')
            ->first();
        
        return response()->json([
            'success' => true,
            'has_active_call' => $activeCall !== null,
            'call' => $activeCall
        ]);
    }

    // Call history for conversation
    public function conversationHistory($conversationId)
    {
        $user = Auth::user();
        
        // Kiểm tra quyền
        $participant = ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', $user->user_id)
            ->where('is_active', true)
            ->first();
            
        if (!$participant) {
            abort(403, 'Unauthorized');
        }
        
        $calls = VideoCall::where('conversation_id', $conversationId)
            ->with('initiator')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('video-calls.history', compact('calls', 'conversationId'));
    }

    // Stats for user
    public function stats()
    {
        $user = Auth::user();
        
        $stats = [
            'total_calls' => VideoCall::whereHas('conversation.participants', function($q) use ($user) {
                $q->where('user_id', $user->user_id);
            })->count(),
            
            'video_calls' => VideoCall::whereHas('conversation.participants', function($q) use ($user) {
                $q->where('user_id', $user->user_id);
            })->where('call_type', 'video')->count(),
            
            'audio_calls' => VideoCall::whereHas('conversation.participants', function($q) use ($user) {
                $q->where('user_id', $user->user_id);
            })->where('call_type', 'audio')->count(),
            
            'completed_calls' => VideoCall::whereHas('conversation.participants', function($q) use ($user) {
                $q->where('user_id', $user->user_id);
            })->where('call_status', 'ended')->count(),
            
            'missed_calls' => VideoCall::whereHas('conversation.participants', function($q) use ($user) {
                $q->where('user_id', $user->user_id);
            })->where('call_status', 'missed')
            ->where('initiated_by', '!=', $user->user_id)
            ->count(),
            
            'total_duration' => VideoCall::whereHas('conversation.participants', function($q) use ($user) {
                $q->where('user_id', $user->user_id);
            })->where('call_status', 'ended')->sum('duration'),
            
            'avg_duration' => VideoCall::whereHas('conversation.participants', function($q) use ($user) {
                $q->where('user_id', $user->user_id);
            })->where('call_status', 'ended')
            ->where('duration', '>', 0)
            ->avg('duration')
        ];
        
        // Format durations
        $stats['total_duration_formatted'] = $this->formatDuration($stats['total_duration']);
        $stats['avg_duration_formatted'] = $this->formatDuration($stats['avg_duration'] ?? 0);
        
        return view('video-calls.stats', compact('stats'));
    }

    // Helper: Send call notifications
    private function sendCallNotifications($call, $status)
    {
        $conversation = Conversation::find($call->conversation_id);
        $initiator = $call->initiator;
        
        $participants = ConversationParticipant::where('conversation_id', $call->conversation_id)
            ->where('user_id', '!=', $call->initiated_by)
            ->where('is_active', true)
            ->get();
        
        $title = '';
        $content = '';
        $priority = 'medium';
        
        switch ($status) {
            case 'initiated':
                $title = 'Cuộc gọi ' . ($call->call_type === 'video' ? 'video' : 'thoại') . ' đến';
                $content = $initiator->first_name . ' đang gọi cho bạn';
                $priority = 'high';
                break;
                
            case 'ended':
                $title = 'Cuộc gọi đã kết thúc';
                $content = 'Thời lượng: ' . $this->formatDuration($call->duration);
                break;
                
            case 'missed':
                $title = 'Cuộc gọi nhỡ';
                $content = 'Bạn đã bỏ lỡ cuộc gọi từ ' . $initiator->first_name;
                break;
        }
        
        foreach ($participants as $participant) {
            DB::table('notifications')->insert([
                'user_id' => $participant->user_id,
                'notification_type' => 'Video Call',
                'title' => $title,
                'content' => $content,
                'related_id' => $call->call_id,
                'related_type' => 'call',
                'action_url' => $status === 'initiated' ? route('video-calls.room', $call->call_id) : null,
                'priority' => $priority,
                'created_at' => now()
            ]);
        }
    }

    // Helper: Format duration
    private function formatDuration($seconds)
    {
        if ($seconds < 60) {
            return round($seconds) . ' giây';
        } elseif ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            $remainingSeconds = $seconds % 60;
            return $minutes . ' phút ' . round($remainingSeconds) . ' giây';
        } else {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            return $hours . ' giờ ' . $minutes . ' phút';
        }
    }
}