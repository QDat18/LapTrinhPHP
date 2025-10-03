<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Volunteer Connect Platform')</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        
        :root {
            --primary-color: #3B82F6;
            --secondary-color: #10B981;
            --danger-color: #EF4444;
            --warning-color: #F59E0B;
            --dark-color: #1F2937;
            --light-bg: #F9FAFB;
        }
        
        body {
            background-color: var(--light-bg);
            min-height: 100vh;
        }
        
        .navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            padding: 1rem 0;
        }
        
        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
            font-size: 1.5rem;
        }
        
        .nav-link {
            color: var(--dark-color);
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.3s;
        }
        
        .nav-link:hover {
            color: var(--primary-color);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 0.6rem 1.5rem;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: #2563EB;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        
        .badge-notification {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger-color);
            color: white;
            border-radius: 10px;
            padding: 2px 6px;
            font-size: 0.7rem;
        }
        
        .main-content {
            padding: 2rem 0;
            min-height: calc(100vh - 200px);
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            transition: all 0.3s;
        }
        
        .card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        footer {
            background: white;
            padding: 2rem 0;
            margin-top: 3rem;
            border-top: 1px solid #E5E7EB;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="fas fa-hands-helping"></i> VolunteerConnect
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('opportunities.index') }}">
                            <i class="fas fa-search"></i> Tìm Cơ Hội
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('organizations.index') }}">
                            <i class="fas fa-building"></i> Tổ Chức
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('about') }}">
                            <i class="fas fa-info-circle"></i> Giới Thiệu
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Đăng Nhập</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary ms-2" href="{{ route('register') }}">Đăng Ký</a>
                        </li>
                    @else
                        <!-- Notifications -->
                        <li class="nav-item dropdown me-3">
                            <a class="nav-link position-relative" href="#" id="notificationDropdown" 
                               data-bs-toggle="dropdown">
                                <i class="fas fa-bell fa-lg"></i>
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                    <span class="badge-notification">
                                        {{ auth()->user()->unreadNotifications->count() }}
                                    </span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" style="width: 300px;">
                                <li class="dropdown-header">Thông Báo</li>
                                @forelse(auth()->user()->notifications()->limit(5)->get() as $notification)
                                    <li>
                                        <a class="dropdown-item small" href="{{ $notification->action_url ?? '#' }}">
                                            <strong>{{ $notification->title }}</strong><br>
                                            <small class="text-muted">{{ $notification->content }}</small>
                                        </a>
                                    </li>
                                @empty
                                    <li class="dropdown-item text-center text-muted">
                                        Không có thông báo mới
                                    </li>
                                @endforelse
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-center" href="{{ route('notifications.index') }}">
                                        Xem tất cả
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- Messages -->
                        <li class="nav-item me-3">
                            <a class="nav-link position-relative" href="{{ route('conversations.index') }}">
                                <i class="fas fa-comments fa-lg"></i>
                                @if(auth()->user()->unreadMessagesCount > 0)
                                    <span class="badge-notification">
                                        {{ auth()->user()->unreadMessagesCount }}
                                    </span>
                                @endif
                            </a>
                        </li>
                        
                        <!-- User Menu -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" 
                               data-bs-toggle="dropdown">
                                <img src="{{ auth()->user()->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->first_name) }}" 
                                     class="rounded-circle" width="32" height="32" alt="Avatar">
                                {{ auth()->user()->first_name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('dashboard') }}">
                                        <i class="fas fa-tachometer-alt"></i> Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile') }}">
                                        <i class="fas fa-user"></i> Hồ Sơ
                                    </a>
                                </li>
                                
                                @if(auth()->user()->user_type === 'Volunteer')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('applications.my') }}">
                                            <i class="fas fa-file-alt"></i> Đơn Ứng Tuyển
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('volunteer-activities.index') }}">
                                            <i class="fas fa-clock"></i> Giờ Tình Nguyện
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('favorites.index') }}">
                                            <i class="fas fa-heart"></i> Yêu Thích
                                        </a>
                                    </li>
                                @endif
                                
                                @if(auth()->user()->user_type === 'Organization')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('opportunities.my') }}">
                                            <i class="fas fa-briefcase"></i> Cơ Hội Của Tôi
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('applications.received') }}">
                                            <i class="fas fa-inbox"></i> Đơn Ứng Tuyển
                                        </a>
                                    </li>
                                @endif
                                
                                @if(auth()->user()->user_type === 'Admin')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                            <i class="fas fa-cog"></i> Admin Panel
                                        </a>
                                    </li>
                                @endif
                                
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('settings') }}">
                                        <i class="fas fa-cog"></i> Cài Đặt
                                    </a>
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-sign-out-alt"></i> Đăng Xuất
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if(session('info'))
        <div class="container mt-3">
            <div class="alert alert-info alert-dismissible fade show">
                <i class="fas fa-info-circle"></i> {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="fas fa-hands-helping"></i> VolunteerConnect</h5>
                    <p class="text-muted">Kết nối tình nguyện viên với các tổ chức phi lợi nhuận</p>
                </div>
                <div class="col-md-4">
                    <h6>Liên Kết</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('about') }}" class="text-muted">Giới Thiệu</a></li>
                        <li><a href="{{ route('contact') }}" class="text-muted">Liên Hệ</a></li>
                        <li><a href="{{ route('privacy') }}" class="text-muted">Chính Sách</a></li>
                        <li><a href="{{ route('terms') }}" class="text-muted">Điều Khoản</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6>Theo Dõi</h6>
                    <div class="social-links">
                        <a href="#" class="text-muted me-3"><i class="fab fa-facebook fa-2x"></i></a>
                        <a href="#" class="text-muted me-3"><i class="fab fa-instagram fa-2x"></i></a>
                        <a href="#" class="text-muted me-3"><i class="fab fa-twitter fa-2x"></i></a>
                    </div>
                </div>
            </div>
            <hr>
            <div class="text-center text-muted">
                <small>&copy; 2025 VolunteerConnect. All rights reserved.</small>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
        
        // CSRF token for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>