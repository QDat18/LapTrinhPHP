@extends('layouts.app')

@section('title', 'Dashboard - Tình Nguyện Viên')

@section('content')
<div class="container py-4">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <img src="{{ auth()->user()->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->first_name) }}" 
                                 class="rounded-circle" width="80" height="80" alt="Avatar">
                        </div>
                        <div class="flex-grow-1">
                            <h2 class="mb-1">Xin chào, {{ auth()->user()->first_name }}! 👋</h2>
                            <p class="mb-0 opacity-75">Chào mừng trở lại với VolunteerConnect</p>
                        </div>
                        <div class="text-end">
                            <div class="h3 mb-0">{{ $stats['total_hours'] ?? 0 }}</div>
                            <small>Giờ Tình Nguyện</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-file-alt fa-2x text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ $stats['pending_applications'] ?? 0 }}</h3>
                            <small class="text-muted">Đơn Chờ Duyệt</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ $stats['accepted_applications'] ?? 0 }}</h3>
                            <small class="text-muted">Đơn Được Chấp Nhận</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-clock fa-2x text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ $stats['total_hours'] ?? 0 }}</h3>
                            <small class="text-muted">Tổng Giờ</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-star fa-2x text-info"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ number_format($stats['rating'] ?? 0, 1) }}</h3>
                            <small class="text-muted">Đánh Giá</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Recommended Opportunities -->
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">
                            <i class="fas fa-magic text-primary"></i> Gợi Ý Cho Bạn
                        </h5>
                        <a href="{{ route('opportunities.index') }}" class="btn btn-sm btn-outline-primary">
                            Xem Tất Cả
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @forelse($recommendedOpportunities as $opportunity)
                        <div class="d-flex mb-3 pb-3 border-bottom">
                            <div class="flex-shrink-0">
                                <span class="badge bg-{{ $opportunity->category->color ?? 'primary' }} rounded-circle p-3">
                                    <i class="{{ $opportunity->category->icon ?? 'fas fa-heart' }} fa-lg"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">
                                    <a href="{{ route('opportunities.show', $opportunity->opportunity_id) }}" 
                                       class="text-decoration-none text-dark">
                                        {{ $opportunity->title }}
                                    </a>
                                </h6>
                                <small class="text-muted">
                                    <i class="fas fa-building"></i> {{ $opportunity->organization->organization_name }}
                                </small>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt"></i> {{ $opportunity->location }}
                                    <span class="mx-2">|</span>
                                    <i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($opportunity->start_date)->format('d/m/Y') }}
                                </small>
                                <div class="mt-2">
                                    <span class="badge bg-primary-subtle text-primary">
                                        Match: {{ $opportunity->match_score ?? 85 }}%
                                    </span>
                                </div>
                            </div>
                            <div class="text-end">
                                <a href="{{ route('applications.create', ['opportunity_id' => $opportunity->opportunity_id]) }}" 
                                   class="btn btn-sm btn-primary">
                                    Ứng Tuyển
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-search fa-3x mb-3"></i>
                            <p>Chưa có cơ hội phù hợp</p>
                            <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary">
                                Cập Nhật Profile
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Applications -->
            <div class="card">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt text-primary"></i> Đơn Ứng Tuyển Gần Đây
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Cơ Hội</th>
                                    <th>Tổ Chức</th>
                                    <th>Ngày Nộp</th>
                                    <th>Trạng Thái</th>
                                    <th>Hành Động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentApplications as $app)
                                    <tr>
                                        <td>
                                            <a href="{{ route('opportunities.show', $app->opportunity->opportunity_id) }}">
                                                {{ Str::limit($app->opportunity->title, 40) }}
                                            </a>
                                        </td>
                                        <td>{{ $app->opportunity->organization->organization_name }}</td>
                                        <td>{{ \Carbon\Carbon::parse($app->applied_date)->format('d/m/Y') }}</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'Pending' => 'warning',
                                                    'Accepted' => 'success',
                                                    'Rejected' => 'danger',
                                                    'Under Review' => 'info'
                                                ];
                                                $color = $statusColors[$app->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $color }}">{{ $app->status }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('applications.show', $app->application_id) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            Chưa có đơn ứng tuyển nào
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Activity Chart -->
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line text-primary"></i> Hoạt Động Gần Đây
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="activityChart" height="200"></canvas>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">
                        <i class="fas fa-bolt text-primary"></i> Hành Động Nhanh
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('opportunities.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-search"></i> Tìm Cơ Hội
                        </a>
                        <a href="{{ route('volunteer-activities.create') }}" class="btn btn-outline-success">
                            <i class="fas fa-plus"></i> Log Giờ Tình Nguyện
                        </a>
                        <a href="{{ route('profile.edit') }}" class="btn btn-outline-info">
                            <i class="fas fa-user-edit"></i> Cập Nhật Profile
                        </a>
                        <a href="{{ route('analytics.volunteer') }}" class="btn btn-outline-warning">
                            <i class="fas fa-chart-bar"></i> Xem Thống Kê
                        </a>
                    </div>
                </div>
            </div>

            <!-- Achievements -->
            <div class="card">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">
                        <i class="fas fa-trophy text-warning"></i> Thành Tích
                    </h6>
                </div>
                <div class="card-body">
                    @forelse($achievements ?? [] as $achievement)
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <span class="fs-2">{{ $achievement['icon'] }}</span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <strong>{{ $achievement['title'] }}</strong>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-medal fa-3x mb-3 opacity-50"></i>
                            <p class="small">Hoàn thành hoạt động để nhận thành tích!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Activity Chart
    const ctx = document.getElementById('activityChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartLabels ?? []) !!},
            datasets: [{
                label: 'Giờ Tình Nguyện',
                data: {!! json_encode($chartData ?? []) !!},
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endpush
@endsection