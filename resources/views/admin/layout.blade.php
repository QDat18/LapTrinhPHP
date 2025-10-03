<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - Volunteer Connect</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
        
        .sidebar-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar-scrollbar::-webkit-scrollbar-track {
            background: #1e293b;
        }
        
        .sidebar-scrollbar::-webkit-scrollbar-thumb {
            background: #475569;
            border-radius: 3px;
        }
        
        .sidebar-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: true, mobileMenuOpen: false }">
    
    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-slate-800 to-slate-900 text-white transform transition-transform duration-300 ease-in-out lg:translate-x-0"
         :class="{ '-translate-x-full': !mobileMenuOpen, 'translate-x-0': mobileMenuOpen }"
         x-show="sidebarOpen || mobileMenuOpen"
         @click.away="mobileMenuOpen = false">
        
        <!-- Logo -->
        <div class="flex items-center justify-between h-16 px-6 border-b border-slate-700">
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
                    <a href="{{ route('admin.users.index', ['type' => 'volunteer']) }}" 
                       class="block px-4 py-2 rounded text-sm text-slate-400 hover:text-white">
                        Volunteers
                    </a>
                    <a href="{{ route('admin.users.index', ['type' => 'organization']) }}" 
                       class="block px-4 py-2 rounded text-sm text-slate-400 hover:text-white">
                        Organizations
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
            <a href="{{ route('admin.analytics') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('admin.analytics') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                <i class="fas fa-chart-line w-5"></i>
                <span>Analytics</span>
            </a>
            
            <!-- Reports -->
            <a href="{{ route('admin.reports.index') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('admin.reports.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                <i class="fas fa-file-download w-5"></i>
                <span>Reports</span>
            </a>
            
            <!-- Settings -->
            <a href="{{ route('admin.settings') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('admin.settings') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                <i class="fas fa-cog w-5"></i>
                <span>Settings</span>
            </a>
            
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="transition-all duration-300" :class="{ 'lg:ml-64': sidebarOpen, 'lg:ml-0': !sidebarOpen }">
        
        <!-- Top Navigation -->
        <header class="bg-white border-b border-gray-200 sticky top-0 z-40">
            <div class="flex items-center justify-between h-16 px-6">
                
                <!-- Left side -->
                <div class="flex items-center space-x-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="hidden lg:block text-gray-500 hover:text-gray-700">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden text-gray-500 hover:text-gray-700">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    
                    <!-- Breadcrumb -->
                    <nav class="hidden md:flex items-center space-x-2 text-sm">
                        <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-home"></i>
                        </a>
                        <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                        <span class="text-gray-700 font-medium">@yield('breadcrumb', 'Dashboard')</span>
                    </nav>
                </div>
                
                <!-- Right side -->
                <div class="flex items-center space-x-4">
                    
                    <!-- Notifications -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="relative p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg">
                            <i class="fas fa-bell text-xl"></i>
                            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" x-cloak
                             class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 py-2">
                            <div class="px-4 py-2 border-b border-gray-200">
                                <h3 class="font-semibold text-gray-800">Notifications</h3>
                            </div>
                            <div class="max-h-96 overflow-y-auto">
                                <a href="#" class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-100">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user text-blue-600"></i>
                                            </div>
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <p class="text-sm text-gray-800">New user registered</p>
                                            <p class="text-xs text-gray-500 mt-1">5 minutes ago</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="px-4 py-2 border-t border-gray-200">
                                <a href="#" class="text-sm text-indigo-600 hover:text-indigo-700">View all notifications</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- User Menu -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center space-x-3 p-2 hover:bg-gray-100 rounded-lg">
                            <img src="https://ui-avatars.com/api/?name={{ auth()->user()->first_name }}+{{ auth()->user()->last_name }}&background=6366f1&color=fff" 
                                 alt="Avatar" class="w-8 h-8 rounded-full">
                            <div class="hidden md:block text-left">
                                <p class="text-sm font-medium text-gray-800">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                                <p class="text-xs text-gray-500">Administrator</p>
                            </div>
                            <i class="fas fa-chevron-down text-gray-500 text-xs"></i>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" x-cloak
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2">
                            <a href="{{ route('user.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i> Profile
                            </a>
                            <a href="{{ route('user.change-password') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-lock mr-2"></i> Change Password
                            </a>
                            <div class="border-t border-gray-200 my-2"></div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
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
        // Toast notification function
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
        
        // Show Laravel flash messages
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