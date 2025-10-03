@extends('layouts.app')

@section('title', 'Dashboard - Tổ Chức')

@section('content')
<div class="container py-4">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-success text-white">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <img src="{{ auth()->user()->organization->logo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->organization->organization_name) }}" 
                                     class="rounded-circle bg-white p-2" width="80" height="80" alt="Logo">
                            </div>
                            <div>
                                <h2 class="mb-1">{{ auth()->user()->organization->organization_name }}</h2>
                                <p class="mb-0 opacity-75">
                                    <i class="fas fa-star"></i> {{ number_format(auth()->user()->organization->rating, 1) }} 
                                    <span class="mx-2">|</span>
                                    <i class="fas fa-users"></i> {{ auth()->user()->organization->volunteer_count }} Volunteers
                                </p>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('opportunities.create') }}" class="btn btn-light btn-lg">
                                <i class="fas fa-plus"></i> Tạo Cơ Hội Mới
                            </a>
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
                            <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-briefcase fa-2x text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ $stats['active_opportunities'] ?? 0 }}</h3>
                            <small class="text-muted">Cơ Hội Đang Hoạt Động</small>
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
                                <i class="fas fa-inbox fa-2x text-warning"></i>
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
                            <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-users fa-2x text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ $stats['total_volunteers'] ?? 0 }}</h3>
                            <small class="text-muted">Tổng Volunteers</small>
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
                                <i class="fas fa-clock fa-2x text-info"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ $stats['total_hours'] ?? 0 }}</h3>
                            <small class="text-muted">Tổng Giờ Đóng Góp</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Pending Applications -->
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">
                            <i class="fas fa-inbox text-warning"></i> Đơn Ứng Tuyển Chờ Duyệt
                        </h5>
                        <a href="{{ route('applications.received') }}" class="btn btn-sm btn-outline-success">
                            Xem Tất Cả
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @forelse($pendingApplications ?? [] as $app)
                        <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                            <div class="flex-shrink-0">
                                <img src="{{ $app->volunteer->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($app->volunteer->first_name) }}" 
                                     class="rounded-circle" width="50" height="50" alt="Avatar">
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">
                                    {{ $app->volunteer->first_name }} {{ $app->volunteer->last_name }}
                                </h6>
                                <small class="text-muted">
                                    Ứng tuyển: <strong>{{ Str::limit($app->opportunity->title, 40) }}</strong>
                                </small>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($app->applied_date)->diffForHumans() }}
                                </small>
                            </div>
                            <div>
                                <a href="{{ route('applications.show', $app->application_id) }}" 
                                   class="btn btn-sm btn-outline-primary me-1">
                                    <i class="fas fa-eye"></i> Xem
                                </a>
                                <form action="{{ route('applications.update-status', $app->application_id) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="Accepted">
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-check"></i> Duyệt
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>Không có đơn chờ duyệt</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Activities Need Verification -->
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">
                            <i class="fas fa-clock text-primary"></i> Giờ Tình Nguyện Cần Xác Nhận
                        </h5>
                        <a href="{{ route('volunteer-activities.index', ['status' => 'Pending']) }}" 
                           class="btn btn-sm btn-outline-success">
                            Xem Tất Cả
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Volunteer</th>
                                    <th>Cơ Hội</th>
                                    <th>Ngày</th>
                                    <th>Giờ</th>
                                    <th>Hành Động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingActivities ?? [] as $activity)
                                    <tr>
                                        <td>{{ $activity->volunteer->first_name }} {{ $activity->volunteer->last_name }}</td>
                                        <td>{{ Str::limit($activity->opportunity->title, 30) }}</td>
                                        <td>{{ \Carbon\Carbon::parse($activity->activity_date)->format('d/m/Y') }}</td>
                                        <td><strong>{{ $activity->hours_worked }}h</strong></td>
                                        <td>
                                            <form action="{{ route('volunteer-activities.verify', $activity->activity_id) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="action" value="verify">
                                                <button type="submit" class="btn btn-sm btn-success" 
                                                        onclick="return confirm('Xác nhận giờ tình nguyện này?')">
                                                    <i class="fas fa-check"></i> Xác Nhận
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            Không có giờ cần xác nhận
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Opportunities -->
            <div class="card">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">
                            <i class="fas fa-briefcase text-success"></i> Cơ Hội Của Bạn
                        </h5>
                        <a href="{{ route('opportunities.my') }}" class="btn btn-sm btn-outline-success">
                            Quản Lý
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tiêu Đề</th>
                                    <th>Trạng Thái</th>
                                    <th>Ứng Tuyển</th>
                                    <th>Lượt Xem</th>
                                    <th>Hành Động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOpportunities ?? [] as $opp)
                                    <tr>
                                        <td>
                                            <a href="{{ route('opportunities.show', $opp->opportunity_id) }}">
                                                {{ Str::limit($opp->title, 40) }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $opp->status == 'Active' ? 'success' : 'secondary' }}">
                                                {{ $opp->status }}
                                            </span>
                                        </td>
                                        <td>{{ $opp->application_count }}/{{ $opp->volunteers_needed }}</td>
                                        <td>{{ $opp->view_count }}</td>
                                        <td>
                                            <a href="{{ route('opportunities.edit', $opp->opportunity_id) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            Chưa có cơ hội nào
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
            <!-- Performance Chart -->
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line text-success"></i> Hiệu Suất
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" height="200"></canvas>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">
                        <i class="fas fa-bolt text-success"></i> Hành Động Nhanh
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('opportunities.create') }}" class="btn btn-outline-success">
                            <i class="fas fa-plus"></i> Tạo Cơ Hội Mới
                        </a>
                        <a href="{{ route('applications.received') }}" class="btn btn-outline-primary">
                            <i class="fas fa-inbox"></i> Xem Đơn Ứng Tuyển
                        </a>
                        <a href="{{ route('volunteer-activities.index') }}" class="btn btn-outline-warning">
                            <i class="fas fa-check-circle"></i> Xác Nhận Giờ
                        </a>
                        <a href="{{ route('analytics.organization') }}" class="btn btn-outline-info">
                            <i class="fas fa-chart-bar"></i> Xem Báo Cáo
                        </a>
                    </div>
                </div>
            </div>

            <!-- Top Volunteers -->
            <div class="card">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">
                        <i class="fas fa-medal text-warning"></i> Top Volunteers
                    </h6>
                </div>
                <div class="card-body">
                    @forelse($topVolunteers ?? [] as $volunteer)
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <img src="{{ $volunteer->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($volunteer->first_name) }}" 
                                     class="rounded-circle" width="40" height="40" alt="Avatar">
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">{{ $volunteer->first_name }} {{ $volunteer->last_name }}</h6>
                                <small class="text-muted">{{ $volunteer->total_hours }}h</small>
                            </div>
                            <div>
                                <span class="badge bg-warning">
                                    <i class="fas fa-star"></i> {{ number_format($volunteer->rating, 1) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-3 text-muted">
                            <i class="fas fa-users fa-2x mb-2 opacity-50"></i>
                            <p class="small">Chưa có dữ liệu</p>
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
    // Performance Chart
    const ctx = document.getElementById('performanceChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels ?? []) !!},
            datasets: [{
                label: 'Đơn Ứng Tuyển',
                data: {!! json_encode($applicationData ?? []) !!},
                backgroundColor: 'rgba(16, 185, 129, 0.8)',
            }, {
                label: 'Giờ Đóng Góp',
                data: {!! json_encode($hoursData ?? []) !!},
                backgroundColor: 'rgba(59, 130, 246, 0.8)',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
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