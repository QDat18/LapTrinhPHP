<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report View - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50">

    @include('admin.partials.navbar')

    <div class="max-w-7xl mx-auto px-4 py-8">
        
        <!-- Report Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">
                        {{ ucwords(str_replace('_', ' ', $reportType)) }} Report
                    </h1>
                    <div class="flex items-center space-x-4 text-sm text-gray-600">
                        <div>
                            <i class="fas fa-calendar mr-2"></i>
                            <strong>Period:</strong> {{ \Carbon\Carbon::parse($data['date_from'])->format('M d, Y') }} 
                            - {{ \Carbon\Carbon::parse($data['date_to'])->format('M d, Y') }}
                        </div>
                        <div>
                            <i class="fas fa-clock mr-2"></i>
                            <strong>Generated:</strong> {{ $data['generated_at']->format('M d, Y H:i') }}
                        </div>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button onclick="window.print()" 
                            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition">
                        <i class="fas fa-print mr-2"></i>Print
                    </button>
                    <a href="{{ route('admin.reports.download', ['type' => 'pdf']) }}?report_type={{ $reportType }}&date_from={{ $data['date_from'] }}&date_to={{ $data['date_to'] }}" 
                       class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition">
                        <i class="fas fa-file-pdf mr-2"></i>Export PDF
                    </a>
                    <a href="{{ route('admin.reports.download', ['type' => 'csv']) }}?report_type={{ $reportType }}&date_from={{ $data['date_from'] }}&date_to={{ $data['date_to'] }}" 
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition">
                        <i class="fas fa-file-csv mr-2"></i>Export CSV
                    </a>
                </div>
            </div>
        </div>

        <!-- Report Content Based on Type -->
        @if($reportType == 'platform_overview')
            @include('admin.reports.partials.platform_overview', ['data' => $data])
        @elseif($reportType == 'user_summary')
            @include('admin.reports.partials.user_summary', ['data' => $data])
        @elseif($reportType == 'volunteer_activity')
            @include('admin.reports.partials.volunteer_activity', ['data' => $data])
        @elseif($reportType == 'organization_performance')
            @include('admin.reports.partials.organization_performance', ['data' => $data])
        @endif

    </div>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white;
            }
        }
    </style>

</body>
</html>