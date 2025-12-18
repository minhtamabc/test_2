<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('asset/css/banDienThoaiGlobal.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/banDienThoai_Header.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/cssMobile.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/reponsive-grid.css') }}"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Home Page</title>
</head>
<body>
    <!-- header sẽ cố định với tablet và pc còn phone sẽ thành thanh menu -->
    <header>
        <div class="wrap-menu-header">
            <nav class="tim-kiem-dang-nhap-gio-hang d-flex">
                <span class="logo"><a href="{{route('home')}}">TechSTU</a></span>
                <div class="dang-nhap-gio-hang d-flex">
                    <div class="tim-kiem flex-1">
                        <!-- <input type="text" placeholder="... tim kiem">
                        <button>
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button> -->
                    </div>
                    <!-- Giỏ hàng - Kiểm tra đăng nhập -->
                    @if(session('user_id') && $data["donHang"] != '0')
                        <a href="{{ route('cart.index') }}" class="gio-hang d-flex align-center">
                            <i class="fa-solid fa-cart-shopping" style="color:green;"></i>
                            <span class="bg-green" style="background-color:green;color:white;" id="gio-hang">{{ $data["donHang"] }}</span>
                        </a>
                    @else
                        <a href="{{ route('cart.index') }}" class="gio-hang d-flex align-center">
                            <i class="fa-solid fa-cart-shopping"></i>
                            <span class="" id="gio-hang">0</span>
                        </a>
                    @endif
                    
                    <!-- Đăng nhập/Đăng xuất -->
                    @if(session('user_id'))
                        <div class="dang-nhap d-flex align-center" style="gap: 10px;">
                            <a href=" {{ route('order.index') }} ">
                                <i class="fa-solid fa-circle-user"></i>
                            </a>
                            <span>{{ session('user_name') }}</span>
                            <a href="{{ route('logout') }}" style="margin-left: 10px; color: #dc3545;">Đăng xuất</a>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="dang-nhap d-flex align-center">
                            <i class="fa-solid fa-circle-user"></i>
                            <span>Log in</span>
                        </a>
                    @endif
                </div>
            </nav>
        </div>
        <nav class="danh-sach-loai-san-pham">
            <ul class="d-flex align-center list-menu">
                @if(isset($data["branch"]))
                    <li><a href="">SHOP ALL</a></li>
                    @foreach($data["branch"] as $ten_hang)
                        <li><a href="">{{$ten_hang->ten_hang}}</a></li>
                    @endforeach
                @endif
            </ul>
        </nav>

        <!-- menu cua dien thoai -->
        <nav class="menu-cua-dien-thoai">
            <div class="header-dien-thoai">
                <span class="logo">TechSTU</span>
            
                <div class="menu-mobile">
                    <div class="menu-mobile-tim-kiem">
                        <div class="tim-kiem">
                            <button>
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </button>
                        </div>
                        <div class="modal-mobile"></div>
                    </div>
                    <div class="menu-mobile-gio-hang">
                        <a href="" class="gio-hang display-center">
                            <i class="fa-solid fa-cart-shopping"></i>
                            <span class="" id="gio-hang">0</span>
                        </a>
                    </div>
                    <div class="menu-mobile-button">
                        <div class="menu-mobile-1"></div>
                        <div class="menu-mobile-1"></div>
                        <div class="menu-mobile-1"></div>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    
    
    <main>
        <section class="gioi-thieu">
            <div class="wrap-chi-tiet-gioi-thieu">
                <img src="https://static.wixstatic.com/media/c22c23_e140bfa8cd6f4cb2ac5ee6e204f64073~mv2.jpg" alt="" class="img">
                <div class="gioi-thieu-text">
                    <h2>Ngày siêu sale</h2>
                    <h1>Giảm đến 30%</h1>
                    <p>Hãy lựa chọn hãng điện thoại yêu thích</p>
                </div>
            </div>
            <div class="wrap-chi-tiet-gioi-thieu">
                <img src="https://static.wixstatic.com/media/c837a6_d84a631864a442a496670bc2d787c6a0~mv2.jpg" alt="" class="img">
                <div class="gioi-thieu-text">
                    <h2>Duy nhất hôm nay</h2>
                    <h1>Nghe ở bất cứ đâu</h1>
                    <p>Top Headphone nổi tiếng</p>
                </div>
            </div>
        </section>

        <section class="best-seller">
            <h2 class="title">Best sellers</h2>

            <div class="wrap-list-san-pham d-flex align-center">
                <div class="btn-pre" id="btn-next">&#10094;</div>

                <div class="list-san-pham d-flex flex-no-wrap">

                @if(isset($data["bestSeller"]))
                    @foreach($data["bestSeller"] as $sp)
                        <div class="san-pham">
                            <span class="logo-sale">
                                BEST SELER
                            </span>
                            <div class="wrap-img-san-pham">
                                <a href="{{ route('product.detail',$sp->id_chi_tiet_thiet_bi) }}">
                                    <img src="{{asset('asset/images/'.$sp->src_anh)}}" alt="sanpham" class="" width="100%">
                                </a>
                            </div>
                            <div class="wrap-thong-tin-san-pham">
                                <p class="ten-san-pham">{{$sp->ten}}</p>
                                <div class="display-center justify-space-between">
                                    <p class="gia">
                                    <span class=""> <strong>{{number_format($sp->gia_ban*1000,0,',','.')}}₫</strong></span>
                                        <!-- <span class="gia-sale">$70.00</span> -->
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
                </div>
               
                <div class="btn-next" id="btn-pre">&#10095;</div>
            </div>
        </section>
        <section>
            <div class="danh-sach-san-pham">
                <div style="padding:20px 40px ;">
                    <h1 style="margin: 40px 0;font-size: 3rem;">Danh mục sản phẩm</h1>
                </div>
                <div class="d-flex align-center">
                    <h3 style="margin:20px 2rem 20px 40px;">Sản phẩm: {{count($data["products"])}}</h3>
                    <form action="" method="post">
                        <select name="" style="padding: .5rem; border-radius: .5rem;font-size: 1rem;">
                            <option value="dienthoai">Điện thoại</option>
                            <option value="tainghe">Tai nghe</option>
                            <option value="sacduphong">Sạc dự phòng</option>
                        </select>
                    </form>
                </div>
                
                <div class="list-san-pham-thuong d-flex">

                    @if(isset($data["products"]))
                        @foreach($data["products"] as $product)
                            <div class="san-pham-thuong border-gray">
                                <div class="bg-white wrap-1">
                                    <span class="logo-sale">
                                        SALE
                                    </span>
                                    <div class="wrap-img-san-pham">
                                        <a href="{{ route('product.detail',$product->id_chi_tiet_thiet_bi) }}">
                                            <img src="{{ asset('asset/images/'.$product->src_anh) }}" alt="sanpham" class="" 
                                            width="100%">
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="bg-mo-ta-sp" style="margin: 20px;">
                                    <p class="">{{$product->ten}}</p>
                                    <div class="display-center justify-space-between">
                                        <p class="gia-mo-ta">
                                            <span class=""><strong>{{number_format($product->gia_ban*1000,0,',','.')}}₫</strong></span>
                                            <!-- <span class="gia-sale">$70.00</span>-->
                                        </p> 
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </section>

        <section class="bo-loc"></section>

        <section class="on-sale"></section>
    </main>
    <footer class="bg-white">
        <section class="d-flex">
            <div class="vi-tri-shop item-footer">
                <h2>Vị trí shop</h2>
            </div>
            <div class="ho-tro item-footer">
                <h2>Hỗ trợ khách hàng</h2>
                <ul>
                    <li><a href="">Liên hệ với chúng tôi</a></li>
                    <li><a href="">Trung tâm hỗ trợ</a></li>
                    <li><a href="">Thông tin của chúng tôi</a></li>
                    <li><a href="">Ứng tuyển</a></li>
                </ul>
            </div>
            <div class="chinh-sach item-footer">
                <h2>Chính sách của shop</h2>
                <ul>
                    <li><a href="">Hoàn trả & ship</a></li>
                    <li><a href="">Chính sách & dịch vụ</a></li>
                    <li><a href="">Phương thức thanh toán</a></li>
                </ul>
            </div>
        </section>
        <section>
            <hr>
        </section>
       
        <section>
            <h4></h4>
            <div>
                
            </div>
        </section>
    </footer>
    <div id="modal">
        <div class="overlay"></div>
        <div class="content">
            <div class="thong-bao">
                
            </div>
        </div>
    </div>
    <script src="asset/js/banDienThoai.js"></script>
</body>
</html>