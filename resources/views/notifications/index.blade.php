@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')
<div class="space-y-6">
    
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Notifications</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Manage your notifications and alerts</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="markAllAsRead()" 
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                <i class="fas fa-check-double mr-2"></i>Mark All as Read
            </button>
            <button onclick="deleteAllRead()" 
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                <i class="fas fa-trash mr-2"></i>Clear Read
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $stats['total'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-bell text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Unread</p>
                    <p class="text-2xl font-bold text-orange-600 dark:text-orange-400 mt-1">{{ $stats['unread'] }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-envelope text-orange-600 dark:text-orange-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">High Priority</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $stats['high_priority'] }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-circle text-red-600 dark:text-red-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Today</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $stats['today'] }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar-day text-green-600 dark:text-green-400 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <form method="GET" action="{{ route('notifications.index') }}" class="flex flex-wrap gap-4">
            
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type</label>
                <select name="type" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Types</option>
                    <option value="Application" {{ request('type') == 'Application' ? 'selected' : '' }}>Application</option>
                    <option value="Message" {{ request('type') == 'Message' ? 'selected' : '' }}>Message</option>
                    <option value="Video Call" {{ request('type') == 'Video Call' ? 'selected' : '' }}>Video Call</option>
                    <option value="Review" {{ request('type') == 'Review' ? 'selected' : '' }}>Review</option>
                    <option value="System" {{ request('type') == 'System' ? 'selected' : '' }}>System</option>
                    <option value="Opportunity" {{ request('type') == 'Opportunity' ? 'selected' : '' }}>Opportunity</option>
                </select>
            </div>

            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">All</option>
                    <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>Unread</option>
                    <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Read</option>
                </select>
            </div>

            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Priority</label>
                <select name="priority" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Priorities</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
                <a href="{{ route('notifications.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                    <i class="fas fa-redo"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Notifications List -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($notifications as $notification)
            <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-750 transition {{ !$notification->is_read ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                <div class="flex items-start space-x-4">
                    
                    <!-- Icon -->
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center
                            {{ $notification->notification_type == 'Application' ? 'bg-blue-100 dark:bg-blue-900' :
                               ($notification->notification_type == 'Message' ? 'bg-green-100 dark:bg-green-900' :
                               ($notification->notification_type == 'Video Call' ? 'bg-purple-100 dark:bg-purple-900' :
                               ($notification->notification_type == 'Review' ? 'bg-yellow-100 dark:bg-yellow-900' :
                               ($notification->notification_type == 'System' ? 'bg-gray-100 dark:bg-gray-700' :
                               'bg-indigo-100 dark:bg-indigo-900')))) }}">
                            <i class="fas {{ 
                                $notification->notification_type == 'Application' ? 'fa-file-alt text-blue-600 dark:text-blue-400' :
                                ($notification->notification_type == 'Message' ? 'fa-envelope text-green-600 dark:text-green-400' :
                                ($notification->notification_type == 'Video Call' ? 'fa-video text-purple-600 dark:text-purple-400' :
                                ($notification->notification_type == 'Review' ? 'fa-star text-yellow-600 dark:text-yellow-400' :
                                ($notification->notification_type == 'System' ? 'fa-cog text-gray-600 dark:text-gray-400' :
                                'fa-clipboard-list text-indigo-600 dark:text-indigo-400'))))
                            }} text-xl"></i>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $notification->title }}</h3>
                                    @if(!$notification->is_read)
                                    <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $notification->content }}</p>
                                <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500 dark:text-gray-500">
                                    <span>
                                        <i class="fas fa-clock mr-1"></i>{{ $notification->created_at->diffForHumans() }}
                                    </span>
                                    <span class="px-2 py-1 rounded-full
                                        {{ $notification->priority == 'high' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' :
                                           ($notification->priority == 'medium' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' :
                                           'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400') }}">
                                        {{ ucfirst($notification->priority) }} Priority
                                    </span>
                                    <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400 rounded-full">
                                        {{ $notification->notification_type }}
                                    </span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center space-x-2 ml-4">
                                @if($notification->action_url)
                                <a href="{{ $notification->action_url }}" 
                                   class="p-2 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition"
                                   title="View Details">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                                @endif

                                @if(!$notification->is_read)
                                <button onclick="markAsRead({{ $notification->notification_id }})" 
                                        class="p-2 text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/30 rounded-lg transition"
                                        title="Mark as Read">
                                    <i class="fas fa-check"></i>
                                </button>
                                @endif

                                <button onclick="deleteNotification({{ $notification->notification_id }})" 
                                        class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition"
                                        title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            @empty
            <div class="p-12 text-center">
                <i class="fas fa-bell-slash text-gray-300 dark:text-gray-600 text-5xl mb-4"></i>
                <p class="text-gray-500 dark:text-gray-400 text-lg font-medium">No notifications found</p>
                <p class="text-gray-400 dark:text-gray-500 text-sm mt-2">You're all caught up!</p>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($notifications->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $notifications->links() }}
        </div>
        @endif

    </div>

</div>

@push('scripts')
<script>
async function markAsRead(notificationId) {
    try {
        const response = await fetch(`/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();
        
        if (data.success) {
            showToast('Notification marked as read', 'success');
            setTimeout(() => location.reload(), 500);
        }
    } catch (error) {
        showToast('Failed to mark as read', 'error');
    }
}

async function markAllAsRead() {
    if (!confirm('Mark all notifications as read?')) return;
    
    try {
        const response = await fetch('/notifications/read-all', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const data = await response.json();
        
        if (data.success) {
            showToast('All notifications marked as read', 'success');
            setTimeout(() => location.reload(), 500);
        }
    } catch (error) {
        showToast('Failed to mark all as read', 'error');
    }
}

async function deleteNotification(notificationId) {
    if (!confirm('Delete this notification?')) return;
    
    try {
        const response = await fetch(`/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const data = await response.json();
        
        if (data.success) {
            showToast('Notification deleted', 'success');
            setTimeout(() => location.reload(), 500);
        }
    } catch (error) {
        showToast('Failed to delete notification', 'error');
    }
}

async function deleteAllRead() {
    if (!confirm('Delete all read notifications? This action cannot be undone.')) return;
    
    try {
        const response = await fetch('/notifications/delete-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const data = await response.json();
        
        if (data.success) {
            showToast(`Deleted ${data.count} notifications`, 'success');
            setTimeout(() => location.reload(), 500);
        }
    } catch (error) {
        showToast('Failed to delete notifications', 'error');
    }
}
</script>
@endpush
@endsection