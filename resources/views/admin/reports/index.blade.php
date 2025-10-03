<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Generation - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">

    @include('admin.partials.navbar')

    <div class="max-w-7xl mx-auto px-4 py-8">
        
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-chart-bar mr-3 text-indigo-600"></i>
                Report Generation
            </h1>
            <p class="text-gray-600 mt-2">Generate comprehensive platform reports</p>
        </div>

        <!-- Report Generation Form -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            
            <!-- Report Configuration -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6">
                    <i class="fas fa-cog mr-2 text-gray-600"></i>
                    Configure Report
                </h2>

                <form method="GET" action="{{ route('admin.reports.generate') }}" class="space-y-6">
                    
                    <!-- Report Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Report Type</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($reportTypes as $key => $name)
                            <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition
                                {{ request('report_type') == $key ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200' }}">
                                <input type="radio" name="report_type" value="{{ $key }}" 
                                       {{ request('report_type', 'platform_overview') == $key ? 'checked' : '' }}
                                       class="text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-3 text-sm font-medium text-gray-800">{{ $name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Date Range -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Date Range</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">From Date</label>
                                <input type="date" name="date_from" 
                                       value="{{ request('date_from', now()->subMonth()->toDateString()) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">To Date</label>
                                <input type="date" name="date_to" 
                                       value="{{ request('date_to', now()->toDateString()) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>

                    <!-- Quick Date Ranges -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Quick Ranges</label>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" onclick="setDateRange(7)" 
                                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm transition">
                                Last 7 Days
                            </button>
                            <button type="button" onclick="setDateRange(30)" 
                                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm transition">
                                Last 30 Days
                            </button>
                            <button type="button" onclick="setDateRange(90)" 
                                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm transition">
                                Last 90 Days
                            </button>
                            <button type="button" onclick="setDateRange(365)" 
                                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm transition">
                                Last Year
                            </button>
                        </div>
                    </div>

                    <!-- Generate Button -->
                    <div class="flex space-x-3">
                        <button type="submit" 
                                class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-lg transition">
                            <i class="fas fa-chart-line mr-2"></i>
                            Generate Report
                        </button>
                    </div>
                </form>
            </div>

            <!-- Quick Actions -->
            <div class="space-y-4">
                
                <!-- Export Options -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">
                        <i class="fas fa-download mr-2 text-green-600"></i>
                        Export Options
                    </h3>
                    <div class="space-y-3">
                        <button onclick="exportReport('pdf')" 
                                class="w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg transition text-left">
                            <i class="fas fa-file-pdf mr-2"></i>
                            Export as PDF
                        </button>
                        <button onclick="exportReport('csv')" 
                                class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition text-left">
                            <i class="fas fa-file-csv mr-2"></i>
                            Export as CSV
                        </button>
                        <button onclick="exportReport('excel')" 
                                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2 px-4 rounded-lg transition text-left">
                            <i class="fas fa-file-excel mr-2"></i>
                            Export as Excel
                        </button>
                    </div>
                </div>

                <!-- Scheduled Reports -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">
                        <i class="fas fa-clock mr-2 text-blue-600"></i>
                        Scheduled Reports
                    </h3>
                    <p class="text-sm text-gray-600 mb-3">Automate report generation and delivery</p>
                    <button class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition">
                        <i class="fas fa-plus mr-2"></i>
                        Schedule Report
                    </button>
                </div>

                <!-- Recent Reports -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">
                        <i class="fas fa-history mr-2 text-purple-600"></i>
                        Recent Reports
                    </h3>
                    <div class="space-y-2 text-sm">
                        <a href="#" class="block p-2 hover:bg-gray-50 rounded transition">
                            <div class="font-medium text-gray-800">Platform Overview</div>
                            <div class="text-xs text-gray-500">Generated: 2 hours ago</div>
                        </a>
                        <a href="#" class="block p-2 hover:bg-gray-50 rounded transition">
                            <div class="font-medium text-gray-800">User Summary</div>
                            <div class="text-xs text-gray-500">Generated: Yesterday</div>
                        </a>
                        <a href="#" class="block p-2 hover:bg-gray-50 rounded transition">
                            <div class="font-medium text-gray-800">Volunteer Activity</div>
                            <div class="text-xs text-gray-500">Generated: 3 days ago</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Templates Info -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-info-circle mr-2 text-indigo-600"></i>
                Available Report Types
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                
                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-800 mb-2">Platform Overview</h4>
                    <p class="text-sm text-gray-600">Comprehensive summary of all platform metrics and activities</p>
                </div>

                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-800 mb-2">User Summary</h4>
                    <p class="text-sm text-gray-600">Detailed breakdown of user registrations, demographics, and engagement</p>
                </div>

                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-800 mb-2">Opportunity Summary</h4>
                    <p class="text-sm text-gray-600">Analysis of posted opportunities, applications, and completion rates</p>
                </div>

                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-800 mb-2">Volunteer Activity</h4>
                    <p class="text-sm text-gray-600">Track volunteer hours, participation rates, and impact metrics</p>
                </div>

                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-800 mb-2">Organization Performance</h4>
                    <p class="text-sm text-gray-600">Evaluate organization engagement, volunteer attraction, and ratings</p>
                </div>

                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-800 mb-2">Financial Summary</h4>
                    <p class="text-sm text-gray-600">Economic value of volunteer contributions and platform transactions</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function setDateRange(days) {
            const today = new Date();
            const pastDate = new Date(today.getTime() - (days * 24 * 60 * 60 * 1000));
            
            document.querySelector('input[name="date_from"]').value = pastDate.toISOString().split('T')[0];
            document.querySelector('input[name="date_to"]').value = today.toISOString().split('T')[0];
        }

        function exportReport(format) {
            const reportType = document.querySelector('input[name="report_type"]:checked').value;
            const dateFrom = document.querySelector('input[name="date_from"]').value;
            const dateTo = document.querySelector('input[name="date_to"]').value;
            
            window.location.href = `/admin/reports/download/${format}?report_type=${reportType}&date_from=${dateFrom}&date_to=${dateTo}`;
        }
    </script>

</body>
</html>