@extends('layouts.app')

@section('title', 'Tìm Cơ Hội Tình Nguyện')

@section('content')
<div class="container py-4">
    <!-- Hero Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-gradient p-5 rounded-3 text-white" style="background: linear-gradient(135deg, #3B82F6 0%, #10B981 100%);">
                <h1 class="display-4 fw-bold mb-3">Tìm Cơ Hội Tình Nguyện</h1>
                <p class="lead">Hàng trăm cơ hội đang chờ bạn khám phá</p>
                
                <!-- Search Bar -->
                <form action="{{ route('search') }}" method="GET" class="mt-4">
                    <div class="input-group input-group-lg">
                        <input type="text" class="form-control" name="q" 
                               placeholder="Tìm kiếm theo tiêu đề, địa điểm, kỹ năng..."
                               value="{{ request('q') }}">
                        <button class="btn btn-light px-4" type="submit">
                            <i class="fas fa-search"></i> Tìm Kiếm
                        </button>
                        <a href="{{ route('search.advanced') }}" class="btn btn-outline-light">
                            <i class="fas fa-sliders-h"></i> Nâng Cao
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('opportunities.index') }}" method="GET">
                        <div class="row g-3">
                            <!-- Category Filter -->
                            <div class="col-md-3">
                                <label class="form-label small">Danh Mục</label>
                                <select class="form-select" name="category">
                                    <option value="">Tất cả</option>
                                    @foreach($categories ?? [] as $category)
                                        <option value="{{ $category->category_id }}" 
                                                {{ request('category') == $category->category_id ? 'selected' : '' }}>
                                            {{ $category->category_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Location Filter -->
                            <div class="col-md-3">
                                <label class="form-label small">Địa Điểm</label>
                                <select class="form-select" name="location">
                                    <option value="">Tất cả</option>
                                    <option value="Hà Nội" {{ request('location') == 'Hà Nội' ? 'selected' : '' }}>Hà Nội</option>
                                    <option value="Hồ Chí Minh" {{ request('location') == 'Hồ Chí Minh' ? 'selected' : '' }}>Hồ Chí Minh</option>
                                    <option value="Đà Nẵng" {{ request('location') == 'Đà Nẵng' ? 'selected' : '' }}>Đà Nẵng</option>
                                </select>
                            </div>

                            <!-- Time Commitment -->
                            <div class="col-md-3">
                                <label class="form-label small">Thời Gian</label>
                                <select class="form-select" name="time_commitment">
                                    <option value="">Tất cả</option>
                                    <option value="1-2 hours" {{ request('time_commitment') == '1-2 hours' ? 'selected' : '' }}>1-2 giờ</option>
                                    <option value="3-5 hours" {{ request('time_commitment') == '3-5 hours' ? 'selected' : '' }}>3-5 giờ</option>
                                    <option value="Full day" {{ request('time_commitment') == 'Full day' ? 'selected' : '' }}>Cả ngày</option>
                                </select>
                            </div>

                            <!-- Submit -->
                            <div class="col-md-3">
                                <label class="form-label small">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter"></i> Lọc
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Results -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h5>
                    Tìm thấy <strong>{{ $opportunities->total() }}</strong> cơ hội
                </h5>
                <div>
                    <select class="form-select form-select-sm" onchange="window.location.href=this.value">
                        <option>Sắp xếp</option>
                        <option value="?sort=latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Mới nhất</option>
                        <option value="?sort=popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Phổ biến</option>
                        <option value="?sort=deadline" {{ request('sort') == 'deadline' ? 'selected' : '' }}>Hạn nộp đơn</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Opportunities Grid -->
    <div class="row g-4">
        @forelse($opportunities as $opportunity)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <div class="card-body">
                        <!-- Category Badge -->
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge rounded-pill" style="background-color: {{ $opportunity->category->color ?? '#3B82F6' }}">
                                <i class="{{ $opportunity->category->icon ?? 'fas fa-heart' }}"></i>
                                {{ $opportunity->category->category_name ?? 'General' }}
                            </span>
                            @auth
                                @if(auth()->user()->user_type === 'Volunteer')
                                    <button class="btn btn-sm btn-link favorite-btn" 
                                            data-opportunity-id="{{ $opportunity->opportunity_id }}">
                                        <i class="far fa-heart text-danger"></i>
                                    </button>
                                @endif
                            @endauth
                        </div>

                        <!-- Title -->
                        <h5 class="card-title mb-2">
                            <a href="{{ route('opportunities.show', $opportunity->opportunity_id) }}" 
                               class="text-dark text-decoration-none">
                                {{ Str::limit($opportunity->title, 60) }}
                            </a>
                        </h5>

                        <!-- Organization -->
                        <p class="text-muted small mb-3">
                            <i class="fas fa-building"></i>
                            <a href="{{ route('organizations.show', $opportunity->org_id) }}" 
                               class="text-muted text-decoration-none">
                                {{ $opportunity->organization->organization_name }}
                            </a>
                        </p>

                        <!-- Description -->
                        <p class="card-text small text-muted">
                            {{ Str::limit($opportunity->description, 100) }}
                        </p>

                        <!-- Meta Info -->
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge bg-light text-dark">
                                <i class="fas fa-map-marker-alt text-primary"></i>
                                {{ Str::limit($opportunity->location, 20) }}
                            </span>
                            <span class="badge bg-light text-dark">
                                <i class="fas fa-calendar text-success"></i>
                                {{ \Carbon\Carbon::parse($opportunity->start_date)->format('d/m/Y') }}
                            </span>
                            <span class="badge bg-light text-dark">
                                <i class="fas fa-clock text-warning"></i>
                                {{ $opportunity->time_commitment }}
                            </span>
                        </div>

                        <!-- Stats -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <small class="text-muted">
                                <i class="fas fa-users"></i>
                                {{ $opportunity->volunteers_registered }}/{{ $opportunity->volunteers_needed }} volunteers
                            </small>
                            <small class="text-muted">
                                <i class="fas fa-eye"></i> {{ $opportunity->view_count }} lượt xem
                            </small>
                        </div>

                        <!-- Action Button -->
                        <div class="d-grid">
                            <a href="{{ route('opportunities.show', $opportunity->opportunity_id) }}" 
                               class="btn btn-primary">
                                <i class="fas fa-arrow-right"></i> Xem Chi Tiết
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-search fa-4x text-muted mb-3"></i>
                    <h4>Không tìm thấy cơ hội phù hợp</h4>
                    <p class="text-muted">Hãy thử thay đổi bộ lọc hoặc tìm kiếm với từ khóa khác</p>
                    <a href="{{ route('opportunities.index') }}" class="btn btn-primary">
                        Xem Tất Cả Cơ Hội
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($opportunities->hasPages())
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex justify-content-center">
                    {{ $opportunities->links() }}
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
    .hover-card {
        transition: all 0.3s ease;
    }
    
    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15) !important;
    }
    
    .favorite-btn {
        padding: 0.25rem;
        line-height: 1;
    }
    
    .favorite-btn.active i {
        color: #EF4444 !important;
    }
    
    .favorite-btn.active i::before {
        content: "\f004";
        font-weight: 900;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Favorite functionality
    $('.favorite-btn').click(function(e) {
        e.preventDefault();
        const btn = $(this);
        const opportunityId = btn.data('opportunity-id');
        
        $.ajax({
            url: '{{ route("api.favorites.toggle") }}',
            method: 'POST',
            data: {
                opportunity_id: opportunityId
            },
            success: function(response) {
                if (response.success) {
                    btn.toggleClass('active');
                    
                    // Show toast notification
                    const message = response.action === 'added' 
                        ? 'Đã thêm vào yêu thích' 
                        : 'Đã xóa khỏi yêu thích';
                    
                    showToast(message, 'success');
                }
            },
            error: function(xhr) {
                showToast('Có lỗi xảy ra', 'error');
            }
        });
    });
    
    function showToast(message, type) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const toast = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('body').append(toast);
        
        setTimeout(function() {
            toast.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }
});
</script>
@endpush
@endsection