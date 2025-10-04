<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" 
      :class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - Volunteer Connect</title>

    <!-- Tailwind CSS with Dark Mode -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        
        .sidebar-scrollbar::-webkit-scrollbar { width: 6px; }
        .sidebar-scrollbar::-webkit-scrollbar-track { background: #1e293b; }
        .sidebar-scrollbar::-webkit-scrollbar-thumb { background: #475569; border-radius: 3px; }
        .sidebar-scrollbar::-webkit-scrollbar-thumb:hover { background: #64748b; }
        
        /* Dark mode transitions */
        * { transition: background-color 0.3s ease, color 0.3s ease; }
    </style>

    @stack('styles')
</head>

<body class="bg-gray-50 dark:bg-gray-900" x-data="{ sidebarOpen: true, mobileMenuOpen: false }">

    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-slate-800 to-slate-900 dark:from-slate-950 dark:to-black text-white transform transition-transform duration-300 ease-in-out lg:translate-x-0"
        :class="{ '-translate-x-full': !mobileMenuOpen, 'translate-x-0': mobileMenuOpen }"
        x-show="sidebarOpen || mobileMenuOpen" @click.away="mobileMenuOpen = false">

        <!-- Logo -->
        <div class="flex items-center justify-between h-16 px-6 border-b border-slate-700 dark:border-slate-800">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-hands-helping text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold">VolunteerConnect</h1>
                    <p class="text-xs text-slate-400">Admin Panel</p>
                </div>
            </div>
            <button @click="mobileMenuOpen = false" class="lg:hidden text-slate-400 hover:text-white">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto sidebar-scrollbar" style="max-height: calc(100vh - 64px);">

            <!-- Dashboard -->
            <a href="{{ route('admin.dashboard') }}"
                class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                <i class="fas fa-tachometer-alt w-5"></i>
                <span>Dashboard</span>
            </a>

            <!-- User Management -->
            <div x-data="{ open: {{ request()->routeIs('admin.users.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-lg transition text-slate-300 hover:bg-slate-700 hover:text-white">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-users w-5"></i>
                        <span>Users</span>
                    </div>
                    <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': open }"></i>
                </button>
                <div x-show="open" x-cloak class="ml-8 mt-2 space-y-1">
                    <a href="{{ route('admin.users.index') }}"
                        class="block px-4 py-2 rounded text-sm {{ request()->routeIs('admin.users.index') ? 'text-indigo-400' : 'text-slate-400 hover:text-white' }}">
                        All Users
                    </a>
                </div>
            </div>

            <!-- Organizations -->
            <a href="{{ route('admin.organizations.index') }}"
                class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('admin.organizations.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                <i class="fas fa-building w-5"></i>
                <span>Organizations</span>
            </a>

            <!-- Opportunities -->
            <a href="{{ route('admin.opportunities.index') }}"
                class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('admin.opportunities.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                <i class="fas fa-clipboard-list w-5"></i>
                <span>Opportunities</span>
            </a>

            <!-- Applications -->
            <a href="{{ route('admin.applications.index') }}"
                class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('admin.applications.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                <i class="fas fa-file-alt w-5"></i>
                <span>Applications</span>
            </a>

            <!-- Categories -->
            <a href="{{ route('admin.categories.index') }}"
                class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('admin.categories.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                <i class="fas fa-tags w-5"></i>
                <span>Categories</span>
            </a>

            <!-- Activities -->
            <a href="{{ route('admin.activities.index') }}"
                class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('admin.activities.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                <i class="fas fa-calendar-check w-5"></i>
                <span>Activities</span>
            </a>

            <!-- Reviews -->
            <a href="{{ route('admin.reviews.index') }}"
                class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('admin.reviews.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                <i class="fas fa-star w-5"></i>
                <span>Reviews</span>
            </a>

            <!-- Divider -->
            <div class="border-t border-slate-700 my-4"></div>

            <!-- Analytics -->
            <a href="{{ route('admin.analytics.index') }}"
                class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('admin.analytics.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                <i class="fas fa-chart-line w-5"></i>
                <span>Analytics</span>
            </a>

            <!-- Reports -->
            <a href="{{ route('admin.analytics.reports') }}"
                class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('admin.analytics.reports') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                <i class="fas fa-file-download w-5"></i>
                <span>Reports</span>
            </a>

            <!-- Settings -->
            <a href="{{ route('admin.settings.index') }}"
                class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('admin.settings.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                <i class="fas fa-cog w-5"></i>
                <span>Settings</span>
            </a>

        </nav>
    </div>

    <!-- Main Content -->
    <div class="transition-all duration-300" :class="{ 'lg:ml-64': sidebarOpen, 'lg:ml-0': !sidebarOpen }">

        <!-- Top Navigation -->
        <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-40">
            <div class="flex items-center justify-between h-16 px-6">

                <!-- Left side -->
                <div class="flex items-center space-x-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="hidden lg:block text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                        <i class="fas fa-bars text-xl"></i>
                    </button>

                    <!-- Breadcrumb -->
                    <nav class="hidden md:flex items-center space-x-2 text-sm">
                        <a href="{{ route('admin.dashboard') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                            <i class="fas fa-home"></i>
                        </a>
                        <i class="fas fa-chevron-right text-gray-400 dark:text-gray-600 text-xs"></i>
                        <span class="text-gray-700 dark:text-gray-300 font-medium">@yield('breadcrumb', 'Dashboard')</span>
                    </nav>
                </div>

                <!-- Right side -->
                <div class="flex items-center space-x-4">

                    <!-- Dark Mode Toggle -->
                    <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)"
                            class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                        <i class="fas" :class="darkMode ? 'fa-sun' : 'fa-moon'" class="text-xl"></i>
                    </button>

                    <!-- Notifications -->
                    <div x-data="{ 
                        open: false, 
                        notifications: [],
                        unreadCount: 0,
                        async loadNotifications() {
                            try {
                                const response = await fetch('/api/notifications/unread-count');
                                const data = await response.json();
                                this.unreadCount = data.count;
                                
                                const notifResponse = await fetch('/notifications');
                                const notifData = await notifResponse.json();
                                this.notifications = notifData.notifications || [];
                            } catch (error) {
                                console.error('Error loading notifications:', error);
                            }
                        }
                    }" 
                    x-init="loadNotifications(); setInterval(() => loadNotifications(), 30000)" 
                    class="relative">
                        <button @click="open = !open"
                            class="relative p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                            <i class="fas fa-bell text-xl"></i>
                            <span x-show="unreadCount > 0" 
                                  x-text="unreadCount" 
                                  class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center"></span>
                        </button>

                        <div x-show="open" @click.away="open = false" x-cloak
                            class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-2">
                            <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                                <h3 class="font-semibold text-gray-800 dark:text-gray-200">Notifications</h3>
                                <button @click="markAllAsRead()" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                                    Mark all read
                                </button>
                            </div>
                            <div class="max-h-96 overflow-y-auto">
                                <template x-for="notif in notifications" :key="notif.id">
                                    <a :href="notif.action_url || '#'" 
                                       class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700"
                                       :class="{ 'bg-blue-50 dark:bg-blue-900/20': !notif.is_read }">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 rounded-full flex items-center justify-center"
                                                     :class="{
                                                        'bg-blue-100 dark:bg-blue-900': notif.notification_type === 'Application',
                                                        'bg-green-100 dark:bg-green-900': notif.notification_type === 'System',
                                                        'bg-yellow-100 dark:bg-yellow-900': notif.notification_type === 'Message',
                                                        'bg-purple-100 dark:bg-purple-900': notif.notification_type === 'Review'
                                                     }">
                                                    <i class="fas" 
                                                       :class="{
                                                          'fa-file-alt text-blue-600': notif.notification_type === 'Application',
                                                          'fa-bell text-green-600': notif.notification_type === 'System',
                                                          'fa-envelope text-yellow-600': notif.notification_type === 'Message',
                                                          'fa-star text-purple-600': notif.notification_type === 'Review'
                                                       }"></i>
                                                </div>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200" x-text="notif.title"></p>
                                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1" x-text="notif.content"></p>
                                                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1" x-text="notif.created_at"></p>
                                            </div>
                                        </div>
                                    </a>
                                </template>
                                
                                <div x-show="notifications.length === 0" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-bell-slash text-3xl mb-2"></i>
                                    <p class="text-sm">No notifications</p>
                                </div>
                            </div>
                            <div class="px-4 py-2 border-t border-gray-200 dark:border-gray-700">
                                <a href="{{ route('notifications.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                                    View all notifications
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- User Menu -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                            class="flex items-center space-x-3 p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                            <img src="https://ui-avatars.com/api/?name={{ auth()->user()->first_name }}+{{ auth()->user()->last_name }}&background=6366f1&color=fff"
                                alt="Avatar" class="w-8 h-8 rounded-full">
                            <div class="hidden md:block text-left">
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Administrator</p>
                            </div>
                            <i class="fas fa-chevron-down text-gray-500 dark:text-gray-400 text-xs"></i>
                        </button>

                        <div x-show="open" @click.away="open = false" x-cloak
                            class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-2">
                            <a href="{{ route('profile') }}"
                                class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <i class="fas fa-user mr-2"></i> Profile
                            </a>
                            <a href="{{ route('user.change-password') }}"
                                class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <i class="fas fa-lock mr-2"></i> Change Password
                            </a>
                            <div class="border-t border-gray-200 dark:border-gray-700 my-2"></div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="p-6">
            @yield('content')
        </main>

    </div>

    <!-- Toast Notifications -->
    <div id="toast-container" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

    <script>
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `px-6 py-4 rounded-lg shadow-lg text-white transform transition-all duration-300 ${
                type === 'success' ? 'bg-green-500' :
                type === 'error' ? 'bg-red-500' :
                type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'
            }`;
            toast.innerHTML = `
                <div class="flex items-center space-x-3">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} text-xl"></i>
                    <span>${message}</span>
                </div>
            `;

            document.getElementById('toast-container').appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        async function markAllAsRead() {
            try {
                await fetch('/notifications/read-all', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
                window.location.reload();
            } catch (error) {
                console.error('Error marking notifications as read:', error);
            }
        }

        @if(session('success'))
            showToast("{{ session('success') }}", 'success');
        @endif
        @if(session('error'))
            showToast("{{ session('error') }}", 'error');
        @endif
        @if(session('warning'))
            showToast("{{ session('warning') }}", 'warning');
        @endif
    </script>

    @stack('scripts')
</body>
</html>