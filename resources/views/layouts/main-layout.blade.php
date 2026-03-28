<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>@yield('title', 'Ứng dụng Điểm danh Sinh viên')</title>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1a73e8;
            --secondary-color: #f1f3f4;
            --text-color: #202124;
            --border-color: #dadce0;
            --hover-color: #e8f0fe;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', Arial, sans-serif;
        }
        
        body {
            display: flex;
            height: 100vh;
            background-color: #fff;
            color: var(--text-color);
            overflow: hidden;
        }
        
        /* ==================== */
        /* SIDEBAR - DESKTOP */
        /* ==================== */
        .sidebar {
            width: 240px;
            background-color: var(--secondary-color);
            padding: 16px 0;
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
            z-index: 1000;
            position: relative;
        }
        
        .logo {
            padding: 0 24px 16px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 16px;
        }
        
        .logo h1 {
            font-size: 22px;
            color: var(--primary-color);
        }
        
        .menu-item {
            padding: 12px 24px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: background-color 0.2s;
            text-decoration: none;
            color: inherit;
            border-left: 3px solid transparent;
        }
        
        .menu-item:hover {
            background-color: var(--hover-color);
        }
        
        .menu-item.active {
            background-color: var(--hover-color);
            border-left: 3px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        .menu-item i {
            margin-right: 16px;
            width: 20px;
            text-align: center;
        }
        
        /* ==================== */
        /* MAIN CONTENT */
        /* ==================== */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-width: 0; /* Fix flexbox overflow issue */
        }
        
        /* ==================== */
        /* HEADER */
        /* ==================== */
        .header {
            padding: 12px 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: white;
            height: 64px;
            min-height: 64px;
            flex-shrink: 0;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .menu-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.2s;
            display: none; /* Ẩn trên desktop, hiện trên mobile */
        }
        
        .menu-icon:hover {
            background-color: var(--hover-color);
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .logo-icon {
            width: 32px;
            height: 32px;
            background-color: var(--primary-color);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
        }
        
        .logo-text {
            font-size: 20px;
            font-weight: 500;
            color: var(--text-color);
        }
        
        .header-center {
            flex: 1;
            max-width: 600px;
            margin: 0 40px;
        }
        
        .search-bar {
            display: flex;
            align-items: center;
            background-color: var(--secondary-color);
            border-radius: 8px;
            padding: 8px 16px;
            border: 1px solid transparent;
            transition: border-color 0.2s;
            width: 100%;
        }
        
        .search-bar:focus-within {
            border-color: var(--primary-color);
            background-color: white;
        }
        
        .search-icon {
            color: #5f6368;
            margin-right: 12px;
            font-size: 18px;
            flex-shrink: 0;
        }
        
        .search-input {
            border: none;
            background: transparent;
            outline: none;
            width: 100%;
            font-size: 16px;
            color: var(--text-color);
            min-width: 0; /* Fix flexbox overflow */
        }
        
        .search-input::placeholder {
            color: #5f6368;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-shrink: 0;
        }
        
        .login-button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 20px;
            padding: 8px 20px;
            cursor: pointer;
            font-weight: 500;
            font-size: 14px;
            transition: background-color 0.2s;
            text-decoration: none;
            white-space: nowrap;
        }
        
        .login-button:hover {
            background-color: #0d62c9;
        }
        
        /* ==================== */
        /* CONTENT AREA */
        /* ==================== */
        .content {
            padding: 24px;
            overflow-y: auto;
            flex: 1;
            background-color: #f8f9fa;
        }
        
        /* ==================== */
        /* OVERLAY FOR MOBILE */
        /* ==================== */
        #sidebarOverlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            display: none;
        }
        
        #sidebarOverlay.active {
            display: block;
        }
        
        /* ==================== */
        /* RESPONSIVE - MOBILE */
        /* ==================== */
        @media (max-width: 768px) {
            body {
                overflow-x: hidden;
            }
            
            /* Sidebar mobile */
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100%;
                width: 280px;
                background-color: var(--secondary-color);
                z-index: 1000;
                transform: translateX(-100%);
                box-shadow: 2px 0 8px rgba(0,0,0,0.2);
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
                transition: transform 0.3s ease;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .sidebar .logo {
                padding: 16px 20px;
                text-align: center;
            }
            
            .sidebar .logo h1 {
                font-size: 18px;
                margin-top: 10px;
            }
            
            .sidebar .logo img {
                max-width: 120px;
                height: auto;
            }
            
            .sidebar .menu-item {
                padding: 12px 20px;
                font-size: 16px;
                border-bottom: 1px solid var(--border-color);
                border-left: none;
            }
            
            .sidebar .menu-item.active {
                border-left: none;
                background-color: var(--hover-color);
            }
            
            /* Menu icon hiển thị trên mobile */
            .menu-icon {
                display: flex;
            }
            
            /* Header mobile */
            .header {
                padding: 8px 16px;
                height: 60px;
                min-height: 60px;
            }
            
            .logo-section .logo-text {
                font-size: 16px;
            }
            
            .header-center {
                margin: 0 15px;
                max-width: none;
            }
            
            .search-bar {
                padding: 6px 12px;
            }
            
            .search-input {
                font-size: 14px;
            }
            
            .header-right {
                gap: 8px;
            }
            
            .login-button {
                padding: 6px 12px;
                font-size: 12px;
            }
            
            /* Welcome text mobile */
            .header-right span {
                font-size: 12px;
                display: none;
            }
            
            /* Logo section ẩn trên mobile */
            .logo-section {
                display: none;
            }
            
            /* Content mobile */
            .content {
                padding: 16px;
            }
        }
        
        /* Mobile nhỏ hơn */
        @media (max-width: 480px) {
            .header {
                padding: 6px 12px;
            }
            
            .header-center {
                margin: 0 10px;
            }
            
            .search-bar {
                padding: 4px 10px;
            }
            
            .content {
                padding: 12px 8px;
            }
            
            /* Ẩn hoàn toàn logo-text trên mobile nhỏ */
            .logo-text {
                display: none;
            }
        }
        
        /* ==================== */
        /* TABLET (768px - 1024px) */
        /* ==================== */
        @media (min-width: 769px) and (max-width: 1024px) {
            .sidebar {
                width: 200px;
            }
            
            .logo h1 {
                font-size: 18px;
            }
            
            .menu-item {
                padding: 10px 16px;
                font-size: 14px;
            }
            
            .menu-item i {
                margin-right: 12px;
                font-size: 14px;
            }
            
            .header-center {
                margin: 0 20px;
            }
            
            .logo-text {
                font-size: 16px;
            }
        }
        
        /* ==================== */
        /* PAGINATION STYLES */
        /* ==================== */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            margin-bottom: 0;
            flex-wrap: wrap;
        }
        
        .pagination .page-item {
            margin: 2px;
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        .pagination .page-link {
            color: var(--primary-color);
            border: 1px solid var(--border-color);
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            transition: all 0.2s;
            font-size: 14px;
        }
        
        .pagination .page-link:hover {
            background-color: var(--hover-color);
            color: var(--primary-color);
        }
        
        .pagination .disabled .page-link {
            color: #6c757d;
            pointer-events: none;
            background-color: #fff;
            border-color: #dee2e6;
        }
        
        /* Responsive pagination */
        @media (max-width: 768px) {
            .pagination .page-link {
                padding: 4px 8px;
                font-size: 12px;
            }
        }
        
    </style>
    
    <!-- Additional CSS từ các views con -->
    @stack('styles')
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <img src="{{ asset('images/logo1.png') }}" alt="Logo" style="width:100%; height:auto;">
            <h1>Hệ thống điểm danh</h1>
        </div>
      
        <a href="{{ route('home.index') }}" class="menu-item {{ Request::route()->getName() == 'home.index' ? 'active' : '' }}" data-page="home">
            <i>🏠</i> Màn hình chính
        </a>
        <a href="{{ route('lichthi.index') }}" class="menu-item {{ Request::route()->getName() == 'lichthi.index' ? 'active' : '' }}">
            <i>📅</i> Lịch thi toàn bộ
        </a>
        <a href="{{ route('diemdanh.index') }}" class="menu-item {{ Request::route()->getName() == 'diemdanh.index' ? 'active' : '' }}">
            <i>📝</i> DS phòng thi cá nhân
        </a>
        @if(Auth::guard('giangvien')->check() && Auth::guard('giangvien')->user()->vai_tro === 'admin')
        <a href="{{ route('sinhvien.index') }}" class="menu-item {{ Request::route()->getName() == 'sinhvien.index' ? 'active' : '' }}">
            <i>📋</i> DS sinh viên
        </a>
        <a href="{{ route('giangvien.index') }}" class="menu-item {{ Request::route()->getName() == 'giangvien.index' ? 'active' : '' }}">
            <i class="fa-solid fa-file" style="color: #74C0FC;"></i> DS giảng viên
        </a>
        <a href="{{ route('monhoc.index') }}" class="menu-item {{ Request::route()->getName() == 'monhoc.index' ? 'active' : '' }}">
            <i>📚</i> Môn học
        </a>
        <a href="{{ route('rekognition.train') }}" class="menu-item {{ Request::route()->getName() == 'rekognition.train' ? 'active' : '' }}">
            <i>⚙️</i> Training ảnh sinh viên
        </a>
        @endif
        <a href="{{ route('auth.profile') }}" class="menu-item {{ Request::route()->getName() == 'auth.profile' ? 'active' : '' }}">
            <i>👤</i> Thông tin tài khoản
        </a>
    </div>
    
    <!-- Main Content Area -->
    <div class="main-content">
        <!-- Header với Icon Add và Login -->
        <div class="header">
            <!-- Left Section -->
            <div class="header-left">
                <div class="menu-icon" id="hamburger" onclick="event.preventDefault(); event.stopPropagation();">
                    <i class="fas fa-bars"></i>
                </div>
                <div class="logo-section">
                    <div class="logo-icon">LH</div>
                    <div class="logo-text">Lớp học</div>
                </div>
            </div>
            
            <!-- Center Section - Search Bar -->
            <div class="header-center">
                @unless(isset($hideSearch) && $hideSearch === true)
                    @hasSection('search')
                        @yield('search')
                    @else
                        @php
                            $currentRoute = Route::currentRouteName();
                            
                            // Chỉ xử lý 2 route này
                            if ($currentRoute === 'sinhvien.index') {
                                $action = route('sinhvien.index');
                                $placeholder = 'Tìm kiếm MSSV, tên sinh viên, lớp...';
                            } else {
                                // Mặc định là home.index (bao gồm các trang khác)
                                $action = route('home.index');
                                $placeholder = 'Tìm kiếm môn thi, phòng thi...';
                            }
                        @endphp
                        
                        <form action="{{ $action }}" method="GET" class="w-100">
                            <div class="search-bar">
                                <div class="search-icon">🔍</div>
                                <input type="text" name="search" class="search-input" 
                                    placeholder="{{ $placeholder }}"
                                    value="{{ request('search') }}">
                                <button type="submit" style="display:none;"></button>
                            </div>
                        </form>
                    @endif
                @endunless
            </div>

            <!-- Right Section -->
            <div class="header-right">
                <div class="d-flex align-items-center gap-3">
                    @if(Auth::guard('giangvien')->check())
                        <span class="d-none d-md-inline">Xin chào, <strong>{{ Auth::guard('giangvien')->user()->ho_ten }}</strong></span>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="login-button">
                                <i class="fas fa-sign-out-alt d-md-none"></i>
                                <span class="d-none d-md-inline">Đăng xuất</span>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="login-button">
                            <i class="fas fa-sign-in-alt d-md-none"></i>
                            <span class="d-none d-md-inline">Đăng nhập</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Khu vực hiển thị nội dung chính -->
        <div class="content">
            @yield('content')
        </div>
    </div>
    
    <div id="sidebarOverlay"></div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const hamburger = document.getElementById('hamburger');
            const overlay = document.getElementById('sidebarOverlay');
            const menuItems = document.querySelectorAll('.menu-item');

            // Mở sidebar
            hamburger.addEventListener('click', (e) => {
                e.stopPropagation();
                sidebar.classList.add('active');
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            });

            // Đóng sidebar
            function closeSidebar() {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }

            overlay.addEventListener('click', closeSidebar);

            // Đóng sidebar khi click ra ngoài (trên mobile)
            document.addEventListener('click', (e) => {
                if (window.innerWidth <= 768 && !sidebar.contains(e.target) && !hamburger.contains(e.target)) {
                    closeSidebar();
                }
            });

            // Đóng sidebar khi click menu item (trên mobile)
            menuItems.forEach(item => {
                item.addEventListener('click', () => {
                    if (window.innerWidth <= 768) {
                        closeSidebar();
                    }
                });
            });

            // Đóng sidebar khi resize về desktop
            window.addEventListener('resize', () => {
                if (window.innerWidth > 768) {
                    closeSidebar();
                }
            });

            // Đóng sidebar khi nhấn ESC
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    closeSidebar();
                }
            });
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Tìm kiếm...",
                allowClear: true,
                width: '100%'
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <!-- Additional scripts từ các views con -->
    @stack('scripts')
</body>
</html>