<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use App\Models\VolunteerOpportunity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Danh sách conversations
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Lấy conversations mà user tham gia
        $query = Conversation::whereHas('participants', function($q) use ($user) {
            $q->where('user_id', $user->user_id)
              ->where('is_active', true);
        })
        ->with(['participants.user', 'opportunity', 'creator', 'lastMessage']);
        
        // Filter theo type
        if ($request->filled('type')) {
            $query->where('conversation_type', $request->type);
        }
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhereHas('participants.user', function($subQ) use ($search) {
                      $subQ->where('first_name', 'like', "%$search%")
                           ->orWhere('last_name', 'like', "%$search%");
                  });
            });
        }
        
        // Only active conversations
        $query->where('is_active', true);
        
        $conversations = $query->orderBy('last_message_at', 'desc')
            ->paginate(20);
        
        // Count unread messages
        $unreadCount = ConversationParticipant::where('user_id', $user->user_id)
            ->where('is_active', true)
            ->sum('unread_count');
        
        return view('conversations.index', compact('conversations', 'unreadCount'));
    }

    // Hiển thị chi tiết conversation
    public function show($id)
    {
        $user = Auth::user();
        
        $conversation = Conversation::with([
            'participants.user.volunteerProfile',
            'participants.user.organization',
            'opportunity',
            'creator'
        ])->findOrFail($id);
        
        // Kiểm tra quyền xem
        $participant = $conversation->participants()
            ->where('user_id', $user->user_id)
            ->where('is_active', true)
            ->first();
            
        if (!$participant) {
            abort(403, 'Bạn không có quyền xem conversation này');
        }
        
        // Load messages
        $messages = Message::where('conversation_id', $id)
            ->where('is_deleted', false)
            ->with(['sender'])
            ->orderBy('sent_at', 'asc')
            ->paginate(50);
        
        // Mark messages as read
        $this->markAsRead($conversation, $user);
        
        return view('conversations.show', compact('conversation', 'messages', 'participant'));
    }

    // Tạo conversation mới
    public function create(Request $request)
    {
        $type = $request->get('type', 'direct');
        $opportunityId = $request->get('opportunity_id');
        $userId = $request->get('user_id');
        
        // Load data for form
        $opportunity = null;
        $recipient = null;
        
        if ($opportunityId) {
            $opportunity = VolunteerOpportunity::with('organization')->findOrFail($opportunityId);
        }
        
        if ($userId) {
            $recipient = User::findOrFail($userId);
        }
        
        return view('conversations.create', compact('type', 'opportunity', 'recipient'));
    }

    // Lưu conversation mới
    public function store(Request $request)
    {
        $validated = $request->validate([
            'conversation_type' => 'required|in:direct,group,opportunity_chat',
            'title' => 'nullable|string|max:100',
            'opportunity_id' => 'nullable|exists:volunteer_opportunities,opportunity_id',
            'participant_ids' => 'required|array|min:1',
            'participant_ids.*' => 'exists:users,user_id',
            'initial_message' => 'nullable|string|max:1000'
        ]);
        
        $user = Auth::user();
        
        // Validate participants
        if ($validated['conversation_type'] === 'direct' && count($validated['participant_ids']) !== 1) {
            return back()->with('error', 'Direct chat chỉ có 2 người!');
        }
        
        if ($validated['conversation_type'] === 'group' && count($validated['participant_ids']) > 20) {
            return back()->with('error', 'Group chat tối đa 20 người!');
        }
        
        // Check if direct conversation already exists
        if ($validated['conversation_type'] === 'direct') {
            $existingConversation = $this->findExistingDirectConversation(
                $user->user_id,
                $validated['participant_ids'][0]
            );
            
            if ($existingConversation) {
                return redirect()->route('conversations.show', $existingConversation->conversation_id)
                    ->with('info', 'Conversation đã tồn tại!');
            }
        }
        
        DB::beginTransaction();
        try {
            // Create conversation
            $conversation = Conversation::create([
                'conversation_type' => $validated['conversation_type'],
                'title' => $validated['title'] ?? $this->generateConversationTitle($validated, $user),
                'opportunity_id' => $validated['opportunity_id'] ?? null,
                'created_by' => $user->user_id,
                'last_message_at' => now(),
                'is_active' => true
            ]);
            
            // Add creator as participant
            ConversationParticipant::create([
                'conversation_id' => $conversation->conversation_id,
                'user_id' => $user->user_id,
                'joined_at' => now(),
                'is_active' => true
            ]);
            
            // Add other participants
            foreach ($validated['participant_ids'] as $participantId) {
                if ($participantId != $user->user_id) {
                    ConversationParticipant::create([
                        'conversation_id' => $conversation->conversation_id,
                        'user_id' => $participantId,
                        'joined_at' => now(),
                        'is_active' => true
                    ]);
                    
                    // Send notification
                    $this->sendConversationInviteNotification($conversation, $participantId);
                }
            }
            
            // Send initial message if provided
            if (!empty($validated['initial_message'])) {
                Message::create([
                    'conversation_id' => $conversation->conversation_id,
                    'sender_id' => $user->user_id,
                    'message_type' => 'text',
                    'content' => $validated['initial_message'],
                    'sent_at' => now()
                ]);
                
                // Update unread count for other participants
                ConversationParticipant::where('conversation_id', $conversation->conversation_id)
                    ->where('user_id', '!=', $user->user_id)
                    ->increment('unread_count');
            }
            
            DB::commit();
            
            return redirect()->route('conversations.show', $conversation->conversation_id)
                ->with('success', 'Đã tạo conversation thành công!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // Update conversation (title, settings)
    public function update(Request $request, $id)
    {
        $conversation = Conversation::findOrFail($id);
        $user = Auth::user();
        
        // Kiểm tra quyền (chỉ creator hoặc admin)
        if ($conversation->created_by != $user->user_id && $user->user_type !== 'Admin') {
            abort(403, 'Bạn không có quyền chỉnh sửa conversation này');
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:100'
        ]);
        
        $conversation->update($validated);
        
        return back()->with('success', 'Đã cập nhật conversation!');
    }

    // Add participants to conversation
    public function addParticipants(Request $request, $id)
    {
        $conversation = Conversation::findOrFail($id);
        $user = Auth::user();
        
        // Kiểm tra quyền
        $isParticipant = $conversation->participants()
            ->where('user_id', $user->user_id)
            ->where('is_active', true)
            ->exists();
            
        if (!$isParticipant) {
            abort(403, 'Bạn không có quyền thêm người vào conversation này');
        }
        
        if ($conversation->conversation_type === 'direct') {
            return back()->with('error', 'Không thể thêm người vào direct chat!');
        }
        
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,user_id'
        ]);
        
        // Check limit
        $currentCount = $conversation->participants()->where('is_active', true)->count();
        if ($currentCount + count($validated['user_ids']) > 20) {
            return back()->with('error', 'Group chat tối đa 20 người!');
        }
        
        DB::beginTransaction();
        try {
            foreach ($validated['user_ids'] as $userId) {
                // Check if already participant
                $existing = ConversationParticipant::where('conversation_id', $id)
                    ->where('user_id', $userId)
                    ->first();
                
                if ($existing) {
                    if (!$existing->is_active) {
                        $existing->update(['is_active' => true, 'joined_at' => now()]);
                    }
                } else {
                    ConversationParticipant::create([
                        'conversation_id' => $id,
                        'user_id' => $userId,
                        'joined_at' => now(),
                        'is_active' => true
                    ]);
                }
                
                // Send notification
                $this->sendConversationInviteNotification($conversation, $userId);
                
                // Send system message
                Message::create([
                    'conversation_id' => $id,
                    'sender_id' => $user->user_id,
                    'message_type' => 'text',
                    'content' => User::find($userId)->first_name . ' đã được thêm vào nhóm',
                    'sent_at' => now()
                ]);
            }
            
            DB::commit();
            
            return back()->with('success', 'Đã thêm thành viên vào conversation!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // Leave conversation
    public function leave($id)
    {
        $conversation = Conversation::findOrFail($id);
        $user = Auth::user();
        
        if ($conversation->conversation_type === 'direct') {
            return back()->with('error', 'Không thể rời khỏi direct chat! Hãy archive thay thế.');
        }
        
        $participant = ConversationParticipant::where('conversation_id', $id)
            ->where('user_id', $user->user_id)
            ->first();
            
        if (!$participant) {
            return back()->with('error', 'Bạn không phải thành viên của conversation này!');
        }
        
        DB::beginTransaction();
        try {
            // Mark as inactive
            $participant->update(['is_active' => false]);
            
            // Send system message
            Message::create([
                'conversation_id' => $id,
                'sender_id' => $user->user_id,
                'message_type' => 'text',
                'content' => $user->first_name . ' đã rời khỏi nhóm',
                'sent_at' => now()
            ]);
            
            // Check if no active participants left
            $activeCount = ConversationParticipant::where('conversation_id', $id)
                ->where('is_active', true)
                ->count();
                
            if ($activeCount === 0) {
                $conversation->update(['is_active' => false]);
            }
            
            DB::commit();
            
            return redirect()->route('conversations.index')
                ->with('success', 'Đã rời khỏi conversation!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // Archive conversation (soft delete for user)
    public function archive($id)
    {
        $user = Auth::user();
        
        $participant = ConversationParticipant::where('conversation_id', $id)
            ->where('user_id', $user->user_id)
            ->firstOrFail();
        
        $participant->update(['is_active' => false]);
        
        return back()->with('success', 'Đã archive conversation!');
    }

    // Unarchive conversation
    public function unarchive($id)
    {
        $user = Auth::user();
        
        $participant = ConversationParticipant::where('conversation_id', $id)
            ->where('user_id', $user->user_id)
            ->firstOrFail();
        
        $participant->update(['is_active' => true]);
        
        return back()->with('success', 'Đã khôi phục conversation!');
    }

    // Delete conversation (only creator or admin)
    public function destroy($id)
    {
        $conversation = Conversation::findOrFail($id);
        $user = Auth::user();
        
        if ($conversation->created_by != $user->user_id && $user->user_type !== 'Admin') {
            abort(403, 'Bạn không có quyền xóa conversation này');
        }
        
        DB::beginTransaction();
        try {
            // Soft delete messages
            Message::where('conversation_id', $id)->update(['is_deleted' => true]);
            
            // Deactivate conversation
            $conversation->update(['is_active' => false]);
            
            // Deactivate all participants
            ConversationParticipant::where('conversation_id', $id)
                ->update(['is_active' => false]);
            
            DB::commit();
            
            return redirect()->route('conversations.index')
                ->with('success', 'Đã xóa conversation!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // Helper: Mark messages as read
    private function markAsRead($conversation, $user)
    {
        $participant = ConversationParticipant::where('conversation_id', $conversation->conversation_id)
            ->where('user_id', $user->user_id)
            ->first();
            
        if ($participant && $participant->unread_count > 0) {
            $participant->update([
                'unread_count' => 0,
                'last_read_at' => now()
            ]);
        }
    }

    // Helper: Find existing direct conversation
    private function findExistingDirectConversation($userId1, $userId2)
    {
        return Conversation::where('conversation_type', 'direct')
            ->where('is_active', true)
            ->whereHas('participants', function($q) use ($userId1) {
                $q->where('user_id', $userId1)->where('is_active', true);
            })
            ->whereHas('participants', function($q) use ($userId2) {
                $q->where('user_id', $userId2)->where('is_active', true);
            })
            ->first();
    }

    // Helper: Generate conversation title
    private function generateConversationTitle($validated, $user)
    {
        if ($validated['conversation_type'] === 'direct') {
            $otherUser = User::find($validated['participant_ids'][0]);
            return 'Chat với ' . $otherUser->first_name . ' ' . $otherUser->last_name;
        }
        
        if ($validated['conversation_type'] === 'opportunity_chat' && isset($validated['opportunity_id'])) {
            $opportunity = VolunteerOpportunity::find($validated['opportunity_id']);
            return 'Chat: ' . $opportunity->title;
        }
        
        return 'Group Chat - ' . now()->format('d/m/Y');
    }

    // Helper: Send conversation invite notification
    private function sendConversationInviteNotification($conversation, $userId)
    {
        DB::table('notifications')->insert([
            'user_id' => $userId,
            'notification_type' => 'Message',
            'title' => 'Bạn được thêm vào conversation mới',
            'content' => $conversation->title,
            'related_id' => $conversation->conversation_id,
            'related_type' => 'conversation',
            'action_url' => route('conversations.show', $conversation->conversation_id),
            'created_at' => now()
        ]);
    }
}