<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Lấy messages cho conversation (for AJAX/API)
    public function index(Request $request, $conversationId)
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
        
        $page = $request->get('page', 1);
        $perPage = 50;
        
        $messages = Message::where('conversation_id', $conversationId)
            ->where('is_deleted', false)
            ->with(['sender'])
            ->orderBy('sent_at', 'desc')
            ->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'messages' => $messages->items(),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total()
            ]
        ]);
    }

    // Gửi message
    public function send(Request $request, $conversationId)
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
        
        $validated = $request->validate([
            'message_type' => 'required|in:text,image,file,video,opportunity_share',
            'content' => 'required_if:message_type,text|nullable|string|max:5000',
            'attachment' => 'required_if:message_type,image,file,video|nullable|file|max:10240', // 10MB
            'opportunity_id' => 'required_if:message_type,opportunity_share|nullable|exists:volunteer_opportunities,opportunity_id'
        ]);
        
        DB::beginTransaction();
        try {
            $attachmentUrl = null;
            $attachmentName = null;
            
            // Handle file upload
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                
                // Validate file type based on message_type
                $this->validateFileType($file, $validated['message_type']);
                
                // Store file
                $path = $file->store('messages/' . $conversationId, 'public');
                $attachmentUrl = Storage::url($path);
                $attachmentName = $file->getClientOriginalName();
            }
            
            // Create message
            $message = Message::create([
                'conversation_id' => $conversationId,
                'sender_id' => $user->user_id,
                'message_type' => $validated['message_type'],
                'content' => $validated['content'] ?? null,
                'attachment_url' => $attachmentUrl,
                'attachment_name' => $attachmentName,
                'sent_at' => now()
            ]);
            
            // Update conversation last_message_at
            Conversation::where('conversation_id', $conversationId)
                ->update(['last_message_at' => now()]);
            
            // Update unread count for other participants
            ConversationParticipant::where('conversation_id', $conversationId)
                ->where('user_id', '!=', $user->user_id)
                ->where('is_active', true)
                ->increment('unread_count');
            
            // Send notifications to other participants
            $this->sendMessageNotifications($conversationId, $message, $user);
            
            DB::commit();
            
            // Return message with sender info
            $message->load('sender');
            
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Mark messages as read
    public function markRead(Request $request, $conversationId)
    {
        $user = Auth::user();
        
        $participant = ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', $user->user_id)
            ->firstOrFail();
        
        $participant->update([
            'unread_count' => 0,
            'last_read_at' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Marked as read'
        ]);
    }

    // Upload attachment
    public function uploadAttachment(Request $request, $conversationId)
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
        
        $validated = $request->validate([
            'file' => 'required|file|max:10240', // 10MB
            'type' => 'required|in:image,file,video'
        ]);
        
        try {
            $file = $request->file('file');
            
            // Validate file type
            $this->validateFileType($file, $validated['type']);
            
            // Store file
            $path = $file->store('messages/' . $conversationId, 'public');
            $url = Storage::url($path);
            
            return response()->json([
                'success' => true,
                'url' => $url,
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Delete message
    public function destroy($conversationId, $messageId)
    {
        $user = Auth::user();
        
        $message = Message::where('conversation_id', $conversationId)
            ->where('message_id', $messageId)
            ->firstOrFail();
        
        // Chỉ sender hoặc admin mới xóa được
        if ($message->sender_id != $user->user_id && $user->user_type !== 'Admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Soft delete
        $message->update(['is_deleted' => true]);
        
        return response()->json([
            'success' => true,
            'message' => 'Message deleted'
        ]);
    }

    // Edit message
    public function update(Request $request, $conversationId, $messageId)
    {
        $user = Auth::user();
        
        $message = Message::where('conversation_id', $conversationId)
            ->where('message_id', $messageId)
            ->where('message_type', 'text')
            ->firstOrFail();
        
        // Chỉ sender mới edit được
        if ($message->sender_id != $user->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Chỉ edit được trong 15 phút
        if ($message->sent_at->diffInMinutes(now()) > 15) {
            return response()->json(['error' => 'Cannot edit message after 15 minutes'], 400);
        }
        
        $validated = $request->validate([
            'content' => 'required|string|max:5000'
        ]);
        
        $message->update([
            'content' => $validated['content'] . ' (đã chỉnh sửa)'
        ]);
        
        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    // Get latest messages (for real-time updates)
    public function getLatest(Request $request, $conversationId)
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
        
        $afterId = $request->get('after_id', 0);
        
        $messages = Message::where('conversation_id', $conversationId)
            ->where('message_id', '>', $afterId)
            ->where('is_deleted', false)
            ->with(['sender'])
            ->orderBy('sent_at', 'asc')
            ->get();
        
        return response()->json([
            'success' => true,
            'messages' => $messages,
            'count' => $messages->count()
        ]);
    }

    // Search messages in conversation
    public function search(Request $request, $conversationId)
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
        
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([
                'success' => false,
                'error' => 'Query must be at least 2 characters'
            ], 400);
        }
        
        $messages = Message::where('conversation_id', $conversationId)
            ->where('is_deleted', false)
            ->where('message_type', 'text')
            ->where('content', 'like', "%$query%")
            ->with(['sender'])
            ->orderBy('sent_at', 'desc')
            ->limit(50)
            ->get();
        
        return response()->json([
            'success' => true,
            'messages' => $messages,
            'count' => $messages->count()
        ]);
    }

    // Get unread count
    public function getUnreadCount()
    {
        $user = Auth::user();
        
        $unreadCount = ConversationParticipant::where('user_id', $user->user_id)
            ->where('is_active', true)
            ->sum('unread_count');
        
        $conversations = ConversationParticipant::where('user_id', $user->user_id)
            ->where('is_active', true)
            ->where('unread_count', '>', 0)
            ->pluck('unread_count', 'conversation_id');
        
        return response()->json([
            'success' => true,
            'total_unread' => $unreadCount,
            'conversations' => $conversations
        ]);
    }

    // Typing indicator (for real-time)
    public function typing(Request $request, $conversationId)
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
        
        $validated = $request->validate([
            'is_typing' => 'required|boolean'
        ]);
        
        // Broadcast typing event (would use WebSockets in production)
        // For now, just return success
        return response()->json([
            'success' => true,
            'user_id' => $user->user_id,
            'is_typing' => $validated['is_typing']
        ]);
    }

    // Helper: Validate file type
    private function validateFileType($file, $messageType)
    {
        $mimeType = $file->getMimeType();
        
        switch ($messageType) {
            case 'image':
                if (!str_starts_with($mimeType, 'image/')) {
                    throw new \Exception('File phải là hình ảnh');
                }
                break;
                
            case 'video':
                if (!str_starts_with($mimeType, 'video/')) {
                    throw new \Exception('File phải là video');
                }
                break;
                
            case 'file':
                // Allow most file types, but block executables
                $blockedExtensions = ['exe', 'bat', 'cmd', 'sh', 'php', 'js'];
                $extension = $file->getClientOriginalExtension();
                
                if (in_array(strtolower($extension), $blockedExtensions)) {
                    throw new \Exception('Loại file này không được phép upload');
                }
                break;
        }
    }

    // Helper: Send message notifications
    private function sendMessageNotifications($conversationId, $message, $sender)
    {
        $conversation = Conversation::find($conversationId);
        
        $participants = ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', '!=', $sender->user_id)
            ->where('is_active', true)
            ->get();
        
        foreach ($participants as $participant) {
            DB::table('notifications')->insert([
                'user_id' => $participant->user_id,
                'notification_type' => 'Message',
                'title' => 'Tin nhắn mới từ ' . $sender->first_name,
                'content' => $this->getMessagePreview($message),
                'related_id' => $conversationId,
                'related_type' => 'message',
                'action_url' => route('conversations.show', $conversationId),
                'created_at' => now()
            ]);
        }
    }

    // Helper: Get message preview for notification
    private function getMessagePreview($message)
    {
        switch ($message->message_type) {
            case 'text':
                return substr($message->content, 0, 100);
            case 'image':
                return '📷 Đã gửi hình ảnh';
            case 'video':
                return '🎥 Đã gửi video';
            case 'file':
                return '📎 Đã gửi file: ' . $message->attachment_name;
            case 'opportunity_share':
                return '🎯 Đã chia sẻ một cơ hội tình nguyện';
            default:
                return 'Tin nhắn mới';
        }
    }
}