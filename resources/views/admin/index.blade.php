<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="{{ asset('asset/css/banDienThoaiGlobal.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/banDienThoai_Header.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/cssMobile.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/reponsive-grid.css') }}"/>    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Admin</title>
    <style>
        body { font-family: Arial; margin: 0; background: #f2f2f2;}
        .sidebar { width: 250px; background: #333; height: 100vh; color: white;}
        .sidebar h3 { text-align: left; border-bottom: 1px solid #555; }
        .sidebar a { display: block; color: #fff;width: 100%; padding: 15px 20px; text-decoration: none; border-bottom: 1px solid #444; }
        .sidebar a:hover { background: #444; }
        .content { padding: 20px; }
        .card { background: white; padding: 20px; margin-bottom: 20px; border-radius: 6px; }
    </style>
</head>
<body>
    <div class="d-flex">
        @if(session('admin_id'))
            <div class="sidebar">
                <h2>ADMIN</h2>
                <h3>{{session('admin_name')}}</h3>
                <a href="{{ route('admin.product') }}">Quản lý sản phẩm</a>
                <a href="{{ route('admin.order') }}">Quản lý đơn hàng</a>
            </div>

            <div class="content flex-1">
                <div class="d-flex justify-between align-center">
                    <h1>Trang chính</h1>
                    <a href="{{ route('admin.logout') }}">
                        <i class="fa-solid fa-right-from-bracket"></i> Logout
                    </a>
                </div>

                <div class="card">
                    <h2>Thống kê nhanh</h2>
                    <p>Tổng sản phẩm: {{$data["tongSP"]}}</p>
                    <p>Đơn hàng chờ xác nhận: {{$data["choDuyet"]}}</p>
                </div>

                <div class="card">
                    <h2>Chức năng</h2>
                    <ul>
                        <li><a href="{{ route('admin.product') }}">Quản lý sản phẩm</a></li>
                        <li><a href="{{ route('admin.order') }}">Quản lý đơn hàng</a></li>
                    </ul>
                </div>
            </div>
        @else
            <h1>Đã có lỗi xảy ra, vui lòng thử lại sau !</h1>
        @endif
    </div>
</body>
</html>
