<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    function index(){
        $data = [];

        $choDuyet = DB::table('donhang')
                    ->select('id_don_hang')
                    ->where('trang_thai_don_hang','=',1)
                    ->get();
        $tongSP = DB::table('chitietthietbi')
                    ->select('id_chi_tiet_thiet_bi')
                    ->get();
        $data["choDuyet"] = count($choDuyet);
        $data["tongSP"] = count($tongSP);

        return view('admin.index')->with('data',$data);
    }
    function login(){
        return view('admin.login');
    }

    function handleLogin(){
        if(isset($_POST["username"]) && isset($_POST["password"])){
            if($_POST["username"] != "" || $_POST["password"] != ""){

                $username = strip_tags(trim($_POST["username"]));
                $password = strip_tags(trim($_POST["password"]));

                if(preg_match("/^[a-zA-Z0-9\.]{6,}$/",$username) && (strlen($password) > 5)){
                    $admin = DB::table('userhethong')
                                ->select('id_he_thong','username','password','fullname')
                                ->where('username','=',$username)
                                ->get();
                    if(count($admin) != 0){
                        if(Hash::check($password,$admin[0]->password)){
                            session(["admin_id" => $admin[0]->id_he_thong]);
                            session(["admin_name" => $admin[0]->fullname]);
                            return  redirect('/trang-chu')->with('success', 'Đăng nhập thành công !');
                        }
                    }
                }
            }  
        }
        return redirect('/trang-chu/login')->with('error', 'Có lỗi xảy ra khi đăng nhập: ');
    }

    public function logout()
    {
        session()->flush();
        return redirect('/trang-chu/login')->with('success', 'Đăng xuất thành công!');
    }

   // Quản lý sản phẩm (Có lọc và hiển thị thông số)
    function productManagement(Request $request){
        // 1. Lấy danh sách loại để làm bộ lọc
        $categories = DB::table('loaisanpham')->get();

        // 2. Query cơ bản lấy thông tin chung
        $query = DB::table('chitietthietbi')
                    ->join('hang', 'chitietthietbi.id_hang', '=', 'hang.id_hang')
                    ->join('loaisanpham', 'chitietthietbi.id_loai', '=', 'loaisanpham.id_loai_sp')
                    ->select(
                        'chitietthietbi.*', 
                        'hang.ten_hang', 
                        'loaisanpham.ten_loai_sp', 
                        'loaisanpham.tenbangthongso',
                        'loaisanpham.id_loai_sp'
                    )
                    ->orderBy('nam_phat_hang', 'desc');

        // 3. Xử lý bộ lọc
        if($request->has('category') && $request->category != 'all'){
            $query->where('chitietthietbi.id_loai', $request->category);
        }

        $products = $query->paginate(10);

        // 4. Vòng lặp lấy thông số chi tiết cho từng sản phẩm
        foreach($products as $product){
            if(!empty($product->tenbangthongso)){
                // XỬ LÝ KHÁC BIỆT TÊN CỘT ID
                // Bảng sạc dự phòng dùng 'id_chitiet_thiet_bi', các bảng khác dùng 'id_chi_tiet_thiet_bi'
                $foreignKey = ($product->id_loai_sp == 4) ? 'id_chitiet_thiet_bi' : 'id_chi_tiet_thiet_bi';

                // Lấy dòng thông số
                $spec = DB::table($product->tenbangthongso)
                          ->where($foreignKey, $product->id_chi_tiet_thiet_bi)
                          ->first();
                
                $product->thong_so = $spec;
            } else {
                $product->thong_so = null;
            }
        }

        $products->appends(['category' => $request->category]);

        return view('admin.sanpham.sanpham', compact('products', 'categories'));
    }

    // 2. Hiển thị Form thêm sản phẩm
    function createProduct(){
        $hangs = DB::table('hang')->get(); 
        $loais = DB::table('loaisanpham')->get();
        return view('admin.sanpham.create', compact('hangs', 'loais'));
    }

    // 3. Xử lý lưu sản phẩm vào Database
    function storeProduct(Request $request){
        $id = Str::random(10); 
        
        // 1. Xử lý ảnh
        $imageName = 'no-image.png';
        if($request->hasFile('anh')){
            $file = $request->file('anh');
            $imageName = $file->getClientOriginalName();
            $file->move(public_path('asset/css/images'), $imageName);
        }

        // 2. Lưu thông tin chung
        DB::table('chitietthietbi')->insert([
            'id_chi_tiet_thiet_bi' => $id,
            'ten' => $request->ten,
            'gia_ban' => $request->gia,
            'so_luong_ton_kho' => $request->soluong,
            'id_hang' => $request->hang,
            'id_loai' => $request->loai,
            'nam_phat_hang' => $request->nam,
            'src_anh' => $imageName
        ]);

        // 3. Lưu thông số kỹ thuật (FULL CỘT)
        
        // ID 1: Điện thoại (Bảng thongsodienthoai)
        if($request->loai == 1){
            DB::table('thongsodienthoai')->insert([
                'id_chi_tiet_thiet_bi' => $id,
                'ram' => $request->dt_ram,
                'bo_nho_trong' => $request->dt_rom,
                'kich_thuoc_man_hinh' => $request->dt_manhinh,
                'chip_set' => $request->dt_chip,
                'pin' => $request->dt_pin,
                'he_dieu_hanh' => $request->dt_os,          // Mới thêm
                'cong_nghe_nfc' => $request->dt_nfc,         // Mới thêm
                'camera_sau' => $request->dt_camsau,         // Mới thêm
                'camera_truoc' => $request->dt_camtruoc,     // Mới thêm
                'do_phan_giai_mh' => $request->dt_dophangiai,// Mới thêm
                'the_sim' => $request->dt_sim,               // Mới thêm
                'cpu' => $request->dt_cpu                    // Mới thêm
            ]);
        }
        // ID 2: Tai nghe có dây (Bảng thongsotainghecoday)
        elseif($request->loai == 2){
            DB::table('thongsotainghecoday')->insert([
                'id_chi_tiet_thiet_bi' => $id,
                'mirco' => $request->tn_micro,        // Lưu ý: DB bạn ghi là 'mirco'
                'cong_ket_noi' => $request->tn_ketnoi,
                'dieu_kien' => $request->tn_dieukhien // Mới thêm (DB ghi là dieu_kien)
            ]);
        }
        // ID 3: Tai nghe không dây (Bảng thongsotainghekhongday)
        elseif($request->loai == 3){
            DB::table('thongsotainghekhongday')->insert([
                'id_chi_tiet_thiet_bi' => $id,
                'micro' => $request->tn_micro,
                'thoi_gian_su_dung' => $request->tn_thoigian,
                'dieu_khien' => $request->tn_dieukhien,      // Mới thêm
                'cong_nghe_am_thanh' => $request->tn_amthanh // Mới thêm
            ]);
        }
        // ID 4: Sạc dự phòng (Bảng thongsosacduphong)
        elseif($request->loai == 4){
            DB::table('thongsosacduphong')->insert([
                'id_chitiet_thiet_bi' => $id,
                'dung_luong' => $request->sdp_dungluong,
                'cong_suat_sac' => $request->sdp_congsuat,
                'cong_sac_ra' => $request->sdp_cong_ra,      // Mới thêm
                'cong_sac_vao' => $request->sdp_cong_vao     // Mới thêm
            ]);
        }

        return redirect()->route('admin.product')->with('success', 'Thêm sản phẩm thành công!');
    }

    public function editProduct($id){
        // Lấy thông tin cơ bản
        $product = DB::table('chitietthietbi')->where('id_chi_tiet_thiet_bi', $id)->first();
        if(!$product) return redirect()->route('admin.product')->with('error', 'Không tìm thấy SP');

        $hangs = DB::table('hang')->get();
        $loais = DB::table('loaisanpham')->get();

        // Lấy thông số kỹ thuật riêng (tương tự như lúc hiển thị danh sách)
        $spec = null;
        // Tìm tên bảng thông số dựa vào loại sản phẩm
        $loaiInfo = DB::table('loaisanpham')->where('id_loai_sp', $product->id_loai)->first();
        
        if($loaiInfo && $loaiInfo->tenbangthongso){
            // Chú ý: Bảng sạc dự phòng tên cột ID hơi khác, cần xử lý khéo
            $colName = ($product->id_loai == 4) ? 'id_chitiet_thiet_bi' : 'id_chi_tiet_thiet_bi';
            
            $spec = DB::table($loaiInfo->tenbangthongso)
                      ->where($colName, $id)
                      ->first();
        }

        return view('admin.sanpham.edit', compact('product', 'hangs', 'loais', 'spec'));
    }

   public function updateProduct(Request $request, $id){
        // 1. Xử lý ảnh
        $oldProduct = DB::table('chitietthietbi')->where('id_chi_tiet_thiet_bi', $id)->first();
        $imageName = $oldProduct->src_anh;

        if($request->hasFile('anh')){
            $file = $request->file('anh');
            $imageName = $file->getClientOriginalName();
            $file->move(public_path('asset/css/images'), $imageName); 
        }

        // 2. Update bảng chính
        DB::table('chitietthietbi')
            ->where('id_chi_tiet_thiet_bi', $id)
            ->update([
                'ten' => $request->ten,
                'gia_ban' => $request->gia,
                'so_luong_ton_kho' => $request->soluong,
                'id_hang' => $request->hang,
                'id_loai' => $request->loai,
                'src_anh' => $imageName
            ]);

        // 3. Update bảng thông số (FULL CỘT)
        if($request->loai == 1){
            DB::table('thongsodienthoai')->updateOrInsert(
                ['id_chi_tiet_thiet_bi' => $id],
                [
                    'ram' => $request->dt_ram,
                    'bo_nho_trong' => $request->dt_rom,
                    'kich_thuoc_man_hinh' => $request->dt_manhinh,
                    'chip_set' => $request->dt_chip,
                    'pin' => $request->dt_pin,
                    'he_dieu_hanh' => $request->dt_os,
                    'cong_nghe_nfc' => $request->dt_nfc,
                    'camera_sau' => $request->dt_camsau,
                    'camera_truoc' => $request->dt_camtruoc,
                    'do_phan_giai_mh' => $request->dt_dophangiai,
                    'the_sim' => $request->dt_sim,
                    'cpu' => $request->dt_cpu
                ]
            );
        }
        elseif($request->loai == 2){
            DB::table('thongsotainghecoday')->updateOrInsert(
                ['id_chi_tiet_thiet_bi' => $id],
                [
                    'mirco' => $request->tn_micro,
                    'cong_ket_noi' => $request->tn_ketnoi,
                    'dieu_kien' => $request->tn_dieukhien
                ]
            );
        }
        elseif($request->loai == 3){
            DB::table('thongsotainghekhongday')->updateOrInsert(
                ['id_chi_tiet_thiet_bi' => $id],
                [
                    'micro' => $request->tn_micro,
                    'thoi_gian_su_dung' => $request->tn_thoigian,
                    'dieu_khien' => $request->tn_dieukhien,
                    'cong_nghe_am_thanh' => $request->tn_amthanh
                ]
            );
        }
        elseif($request->loai == 4){
            DB::table('thongsosacduphong')->updateOrInsert(
                ['id_chitiet_thiet_bi' => $id],
                [
                    'dung_luong' => $request->sdp_dungluong,
                    'cong_suat_sac' => $request->sdp_congsuat,
                    'cong_sac_ra' => $request->sdp_cong_ra,
                    'cong_sac_vao' => $request->sdp_cong_vao
                ]
            );
        }

        return redirect()->route('admin.product')->with('success', 'Cập nhật thành công!');
    }

    public function deleteProduct($id){
        // Kiểm tra xem sản phẩm có tồn tại không
        $check = DB::table('chitietthietbi')->where('id_chi_tiet_thiet_bi', $id)->first();
        
        if($check){
            DB::table('chitietthietbi')->where('id_chi_tiet_thiet_bi', $id)->delete();
            return redirect()->back()->with('success', 'Đã xóa sản phẩm thành công!');
        } else {
            return redirect()->back()->with('error', 'Sản phẩm không tồn tại!');
        }
    }

    // CHỨC NĂNG ẨN/HIỆN SẢN PHẨM
    public function toggleProductStatus($id){
        // Lấy trạng thái hiện tại
        $product = DB::table('chitietthietbi')->where('id_chi_tiet_thiet_bi', $id)->first();

        if($product){
            // Đảo ngược trạng thái (1 -> 0, 0 -> 1)
            $newStatus = $product->trang_thai == 1 ? 0 : 1;
            
            DB::table('chitietthietbi')
                ->where('id_chi_tiet_thiet_bi', $id)
                ->update(['trang_thai' => $newStatus]);
                
            $msg = $newStatus == 1 ? 'Đã hiển thị sản phẩm!' : 'Đã ẩn sản phẩm!';
            return redirect()->back()->with('success', $msg);
        }

        return redirect()->back()->with('error', 'Lỗi: Không tìm thấy sản phẩm');
    }

    
    function orderManagement($trangThai=1){
        $data = [];
        // danh sách order
        $orders = DB::table('donhang')
                    ->join('khachhang','khachhang.id_khach_hang','donhang.id_khach_hang')
                    ->select('id_don_hang','fullname','tong_tien','loai_thanh_toan','trang_thai_don_hang','ngay_giao')
                    ->where('trang_thai_don_hang','=',$trangThai)
                    ->get();
        if($trangThai == 2)
            $data["confirm"] = $orders;
        else if($trangThai == 3)
            $data["delivery"] = $orders;
        else if($trangThai == 5)
            $data["finish"] = $orders;
        else
            $data["orders"] = $orders;
        return view('admin.donhang.donhang')->with('data',$data);
    }

    function orderConfirm(){
        if(isset($_POST["idDonHang"])){
            $idDonHang = $_POST["idDonHang"];
            $data = [];
            // danh sách order đã duyệt
            $orders = DB::table('donhang')
                        ->join('khachhang','khachhang.id_khach_hang','donhang.id_khach_hang')
                        ->select('id_don_hang','fullname','tong_tien','loai_thanh_toan','dia_chi','sdt')
                        ->where('trang_thai_don_hang','=',1)
                        ->where('donhang.id_don_hang','=',$idDonHang)
                        ->get();

            $details = DB::table('chitietdonhang')
                        ->join('chitietthietbi','chitietthietbi.id_chi_tiet_thiet_bi','chitietdonhang.id_chi_tiet_thiet_bi')
                        ->select('ten','gia_ban','so_luong','tong_tien','src_anh')
                        ->where('chitietdonhang.id_don_hang','=',$idDonHang)
                        ->get();

            $data["order"] = $orders;
            $data["detail"] = $details;
            $data["vanchuyen"] = false;
            return view('admin.donhang.xacnhan')->with('data',$data);
        }
        return view('admin.donhang.xacnhan')->with('data',[]);
    }

    function confirmStep2(Request $request){
        $idDonHang = $request->input('idDonHang');
        if($idDonHang){
            $data = DB::table('donhang')
                        ->where('id_don_hang','=',$idDonHang)
                        ->update([
                            'trang_thai_don_hang' => 2
                        ]);
            if($data > 0)  
                return redirect()->route('admin.order',2)->with('success','Đã chuyển đơn hàng sang trạng thái đã duyệt !');
        }
        return redirect()->route('admin.order',1)->with('error','Không cập nhật được, vui lòng thử lại sau !');
     }

    function detailOrder(){
        if(isset($_POST["idDonHang"])){
            $idDonHang = $_POST["idDonHang"];
            $data = [];
            // danh sách order đã duyệt
            $orders = DB::table('donhang')
                        ->join('khachhang','khachhang.id_khach_hang','donhang.id_khach_hang')
                        ->select('id_don_hang','fullname','tong_tien','loai_thanh_toan','dia_chi','sdt')
                        ->where('trang_thai_don_hang','=',2)
                        ->where('donhang.id_don_hang','=',$idDonHang)
                        ->get();

            $details = DB::table('chitietdonhang')
                        ->join('chitietthietbi','chitietthietbi.id_chi_tiet_thiet_bi','chitietdonhang.id_chi_tiet_thiet_bi')
                        ->select('ten','gia_ban','so_luong','tong_tien','src_anh')
                        ->where('chitietdonhang.id_don_hang','=',$idDonHang)
                        ->get();

            $data["order"] = $orders;
            $data["detail"] = $details;
            $data["vanchuyen"] = true;
            return view('admin.donhang.xacnhan')->with('data',$data);
        }
        return view('admin.donhang.xacnhan')->with('data',[]);
    }

    function delivery(Request $request){
        $idDonHang = $request->input('idDonHang');
        if($idDonHang){
            $data = DB::table('donhang')
                        ->where('id_don_hang','=',$idDonHang)
                        ->update([
                            'trang_thai_don_hang' => 3
                        ]);
            if($data > 0)  
                return redirect()->route('admin.order',3)->with('success','Đã chuyển đơn hàng sang trạng thái vận chuyển !');
        }
        return redirect()->route('admin.order',2)->with('error','Không cập nhật được, vui lòng thử lại sau !');
     }

     function confirmFinish(){
        if(isset($_POST["idDonHang"])){
            date_default_timezone_set('Asia/Ho_Chi_Minh');
            $idDonHang = $_POST["idDonHang"];
            // danh sách order đã duyệt
            $data = DB::table('donhang')
                        ->where('id_don_hang','=',$idDonHang)
                        ->update([
                            'trang_thai_don_hang' => 5,
                            'ngay_giao' => date("Y-m-d H:i:s")
                        ]);
            if($data > 0)
                return redirect()->route('admin.order',5)->with('success','Đơn hàng đã hoàn thành !');
        }
        return redirect()->route('admin.order',3)->with('error','Không cập nhật được, vui lòng thử lại sau !');
     }

     function detail($idDonHang){;
        $data = [];
        // danh sách order đã duyệt
        $orders = DB::table('donhang')
                    ->join('khachhang','khachhang.id_khach_hang','donhang.id_khach_hang')
                    ->select('id_don_hang','fullname','tong_tien','loai_thanh_toan','dia_chi','sdt','ngay_giao')
                    ->where('trang_thai_don_hang','=',5)
                    ->where('donhang.id_don_hang','=',$idDonHang)
                    ->get();

        $details = DB::table('chitietdonhang')
                    ->join('chitietthietbi','chitietthietbi.id_chi_tiet_thiet_bi','chitietdonhang.id_chi_tiet_thiet_bi')
                    ->select('ten','gia_ban','so_luong','tong_tien','src_anh')
                    ->where('chitietdonhang.id_don_hang','=',$idDonHang)
                    ->get();
        if(count($orders) < 1 )
            return  redirect()->route('admin.order',5)->with('error','Không thẻ xem chi tiết, vui lòng thử lại sau !');
        $data["order"] = $orders;
        $data["detail"] = $details;
        $data["vanchuyen"] = 2;
        return view('admin.donhang.xacnhan')->with('data',$data);
     }
}
