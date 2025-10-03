<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <div class="text-sm font-medium text-gray-600 mb-1">Total Users</div>
            <div class="text-3xl font-bold text-gray-800">{{ number_format($data['users']['total_users']) }}</div>
            <div class="text-sm text-gray-500 mt-2">
                {{ number_format($data['users']['volunteers']) }} Volunteers, 
                {{ number_format($data['users']['organizations']) }} Organizations
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <div class="text-sm font-medium text-gray-600 mb-1">Active Opportunities</div>
            <div class="text-3xl font-bold text-gray-800">{{ number_format($data['opportunities']['active_opportunities']) }}</div>
            <div class="text-sm text-gray-500 mt-2">
                {{ number_format($data['opportunities']['total_opportunities']) }} Total Posted
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
            <div class="text-sm font-medium text-gray-600 mb-1">Volunteer Hours</div>
            <div class="text-3xl font-bold text-gray-800">{{ number_format($data['activities']['total_hours']) }}</div>
            <div class="text-sm text-gray-500 mt-2">
                {{ number_format($data['activities']['active_volunteers']) }} Active Volunteers
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-orange-500">
            <div class="text-sm font-medium text-gray-600 mb-1">Applications</div>
            <div class="text-3xl font-bold text-gray-800">{{ number_format($data['applications']['total']) }}</div>
            <div class="text-sm text-gray-500 mt-2">
                {{ number_format($data['applications']['accepted']) }} Accepted
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- User Growth -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">User Registrations</h3>
            <canvas id="userGrowthChart" height="250"></canvas>
        </div>

        <!-- Applications by Status -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Application Status</h3>
            <canvas id="applicationStatusChart" height="250"></canvas>
        </div>
    </div>

    <!-- Top Performers -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Top Volunteers -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Top Volunteers by Hours</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2 text-sm font-semibold text-gray-600">#</th>
                            <th class="text-left py-2 text-sm font-semibold text-gray-600">Name</th>
                            <th class="text-right py-2 text-sm font-semibold text-gray-600">Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['activities']['top_volunteers']->take(10) as $index => $volunteer)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 text-gray-700">{{ $index + 1 }}</td>
                            <td class="py-2 text-gray-700">{{ $volunteer->first_name }} {{ $volunteer->last_name }}</td>
                            <td class="py-2 text-right font-semibold text-gray-800">{{ number_format($volunteer->total_hours, 1) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Hours by Category -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Hours by Category</h3>
            <canvas id="categoryHoursChart" height="250"></canvas>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Summary Statistics</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <h4 class="font-semibold text-gray-700 mb-3">User Metrics</h4>
                <ul class="space-y-2 text-sm">
                    <li class="flex justify-between">
                        <span class="text-gray-600">Total Registrations:</span>
                        <span class="font-semibold">{{ number_format($data['users']['total_users']) }}</span>
                    </li>
                    <li class="flex justify-between">
                        <span class="text-gray-600">Verified Users:</span>
                        <span class="font-semibold">{{ number_format($data['users']['verified_users']) }}</span>
                    </li>
                    <li class="flex justify-between">
                        <span class="text-gray-600">Active Users:</span>
                        <span class="font-semibold">{{ number_format($data['users']['active_users']) }}</span>
                    </li>
                </ul>
            </div>

            <div>
                <h4 class="font-semibold text-gray-700 mb-3">Opportunity Metrics</h4>
                <ul class="space-y-2 text-sm">
                    <li class="flex justify-between">
                        <span class="text-gray-600">Total Posted:</span>
                        <span class="font-semibold">{{ number_format($data['opportunities']['total_opportunities']) }}</span>
                    </li>
                    <li class="flex justify-between">
                        <span class="text-gray-600">Active:</span>
                        <span class="font-semibold">{{ number_format($data['opportunities']['active_opportunities']) }}</span>
                    </li>
                    <li class="flex justify-between">
                        <span class="text-gray-600">Completed:</span>
                        <span class="font-semibold">{{ number_format($data['opportunities']['completed_opportunities']) }}</span>
                    </li>
                </ul>
            </div>

            <div>
                <h4 class="font-semibold text-gray-700 mb-3">Engagement Metrics</h4>
                <ul class="space-y-2 text-sm">
                    <li class="flex justify-between">
                        <span class="text-gray-600">Total Hours:</span>
                        <span class="font-semibold">{{ number_format($data['activities']['total_hours']) }}</span>
                    </li>
                    <li class="flex justify-between">
                        <span class="text-gray-600">Active Volunteers:</span>
                        <span class="font-semibold">{{ number_format($data['activities']['active_volunteers']) }}</span>
                    </li>
                    <li class="flex justify-between">
                        <span class="text-gray-600">Avg Rating:</span>
                        <span class="font-semibold">{{ number_format($data['reviews']['average_rating'], 2) }} ⭐</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    // User Growth Chart
    const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
    new Chart(userGrowthCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($data['users']['registrations_by_day']->pluck('date')) !!},
            datasets: [{
                label: 'New Users',
                data: {!! json_encode($data['users']['registrations_by_day']->pluck('count')) !!},
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            }
        }
    });

    // Application Status Chart
    const appStatusCtx = document.getElementById('applicationStatusChart').getContext('2d');
    new Chart(appStatusCtx, {
        type: 'pie',
        data: {
            labels: ['Pending', 'Accepted', 'Rejected'],
            datasets: [{
                data: [
                    {{ $data['applications']['pending'] }},
                    {{ $data['applications']['accepted'] }},
                    {{ $data['applications']['total'] - $data['applications']['pending'] - $data['applications']['accepted'] }}
                ],
                backgroundColor: [
                    'rgba(251, 191, 36, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(239, 68, 68, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Category Hours Chart
    const categoryHoursCtx = document.getElementById('categoryHoursChart').getContext('2d');
    new Chart(categoryHoursCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($data['activities']['hours_by_category']->pluck('category_name')) !!},
            datasets: [{
                label: 'Hours',
                data: {!! json_encode($data['activities']['hours_by_category']->pluck('total_hours')) !!},
                backgroundColor: 'rgba(139, 92, 246, 0.8)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: { display: false }
            }
        }
    });
</script>
