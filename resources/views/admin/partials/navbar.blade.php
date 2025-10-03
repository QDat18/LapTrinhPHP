<nav class="bg-indigo-600 text-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center py-4">
            <div class="flex items-center space-x-4">
                <i class="fas fa-shield-alt text-2xl"></i>
                <h1 class="text-xl font-bold">Admin Panel</h1>
            </div>
            <div class="flex items-center space-x-6">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-indigo-200 transition">
                    <i class="fas fa-home mr-2"></i>Dashboard
                </a>
                <a href="{{ route('admin.users.index') }}" class="hover:text-indigo-200 transition">
                    <i class="fas fa-users mr-2"></i>Users
                </a>
                <a href="{{ route('admin.organizations.verification') }}" class="hover:text-indigo-200 transition relative">
                    <i class="fas fa-building mr-2"></i>Organizations
                    @if(isset($pendingVerifications) && $pendingVerifications > 0)
                    <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                        {{ $pendingVerifications }}
                    </span>
                    @endif
                </a>
                <a href="{{ route('admin.activities.pending') }}" class="hover:text-indigo-200 transition">
                    <i class="fas fa-clock mr-2"></i>Activities
                </a>
                <a href="{{ route('admin.reviews.moderate') }}" class="hover:text-indigo-200 transition">
                    <i class="fas fa-star mr-2"></i>Reviews
                </a>
                <a href="{{ route('admin.reports.index') }}" class="hover:text-indigo-200 transition">
                    <i class="fas fa-chart-bar mr-2"></i>Reports
                </a>
                <a href="{{ route('home') }}" class="hover:text-indigo-200 transition">
                    <i class="fas fa-external-link-alt mr-2"></i>View Site
                </a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="hover:text-indigo-200 transition">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>