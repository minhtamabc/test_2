<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(){
      
        $data = [];
        $donHang = DB::table('donhang')
                        ->join('trangthaidonhang','trangthaidonhang.id_trang_thai','donhang.trang_thai_don_hang')
                        ->join('loaithanhtoan','loaithanhtoan.id_loai_thanh_toan','donhang.loai_thanh_toan')
                        ->select('id_don_hang','tong_tien','ten_trang_thai','ten_loai_thah_toan','trang_thai_don_hang')
                        ->where('id_khach_hang','=',session('user_id'))
                        ->whereBetWeen('trang_thai_don_hang',[1,5])
                        ->orderBy('ngay_dat','desc')
                        ->get();

        $data["donHang"] = $donHang;
        return view('myOrder')->with('data',$data);
       
    }

    public function order(){
        if(isset($_POST["thanhtoan"]) && isset($_POST["ptThanhToan"]) && isset($_POST["amount"]) && isset($_POST["idDonHang"])){
            if($_POST["diachi"] == "" || $_POST["sdt"] == "")
                return redirect()->route('cart.index')->with('error','Chưa có địa chỉ hoặc số điện thoại');
            if(!preg_match("/^0[0-9]{9}$/",trim($_POST["sdt"])))
                return redirect()->route('cart.index')->with('error','Sai định dạng số điện thoại !');
            $t = DB::table('khachhang')
                ->where('id_khach_hang','=',session('user_id'))
                ->update([
                    'dia_chi' => $_POST["diachi"],
                    'sdt' => $_POST["sdt"]
                ]);

            $cod = $_POST["ptThanhToan"];
            $total = $_POST["amount"];
            $idDonHang = $_POST["idDonHang"];
            date_default_timezone_set('Asia/Ho_Chi_Minh');
            //cod
            if($cod == "1"){
                $data = DB::table('donhang')
                            ->where('id_don_hang','=',$idDonHang)
                            ->update([
                                'tong_tien' => ($total/1000),
                                'ngay_dat' => date('Y-m-d H:i:s'),
                                'loai_thanh_toan' => 1,
                                'trang_thai_don_hang' => 1
                            ]);

                if($data > 0) 
                  return redirect()->route('order.index')->with('success','Đã đặt hàng thành công !');
            }
            //vnpay
            else if($cod == "2"){
                $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
                $vnp_Returnurl = "http://127.0.0.1:8000/vnpay-return/";
                $vnp_TmnCode = "5XVFV4NZ";
                $vnp_HashSecret = "13D02GO4YMBASUAPINGOQFEV67EZ4APH"; 
                    
                $vnp_TxnRef = $idDonHang; //Mã đơn hàng. Trong thực tế Merchant cần insert đơn hàng vào DB và gửi mã này 
                $vnp_OrderInfo = 'Thanh toán dịch vụ';
                $vnp_OrderType = 'billpayment';
                $vnp_Amount = $total * 100;
                $vnp_Locale = 'vn';
                $vnp_BankCode = 'VNBANK';
                $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
                //Add Params of 2.0.1 Version
                $vnp_ExpireDate = date("YmdHis",strtotime(date("YmdHis")."+10 minute"));
            
                $inputData = array(
                    "vnp_Version" => "2.1.0",
                    "vnp_TmnCode" => $vnp_TmnCode,
                    "vnp_Amount" => $vnp_Amount,
                    "vnp_Command" => "pay",
                    "vnp_CreateDate" => date('YmdHis'),
                    "vnp_CurrCode" => "VND",
                    "vnp_IpAddr" => $vnp_IpAddr,
                    "vnp_Locale" => $vnp_Locale,
                    "vnp_OrderInfo" => $vnp_OrderInfo,
                    "vnp_OrderType" => $vnp_OrderType,
                    "vnp_ReturnUrl" => $vnp_Returnurl,
                    "vnp_TxnRef" => $vnp_TxnRef,
                    "vnp_ExpireDate"=>$vnp_ExpireDate
                );
                
                if (isset($vnp_BankCode) && $vnp_BankCode != "") {
                    $inputData['vnp_BankCode'] = $vnp_BankCode;
                }
                if (isset($vnp_Bill_State) && $vnp_Bill_State != "") {
                    $inputData['vnp_Bill_State'] = $vnp_Bill_State;
                }
                
                //var_dump($inputData);
                ksort($inputData);
                $query = "";
                $i = 0;
                $hashdata = "";
                foreach ($inputData as $key => $value) {
                    if ($i == 1) {
                        $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                    } else {
                        $hashdata .= urlencode($key) . "=" . urlencode($value);
                        $i = 1;
                    }
                    $query .= urlencode($key) . "=" . urlencode($value) . '&';
                }
                
                $vnp_Url = $vnp_Url . "?" . $query;
                if (isset($vnp_HashSecret)) {
                    $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret);//  
                    $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
                }

                $data = DB::table('donhang')
                            ->where('id_don_hang','=',$idDonHang)
                            ->update([
                                'tong_tien' => ($total/1000),
                                'ngay_dat' => date('Y-m-d H:i:s'),
                                'loai_thanh_toan' => 2
                            ]);

                return redirect($vnp_Url);
            }
        }
        return redirect()->route('order.index')->with('error','Đã có lỗi xảy ra, vui lòng thử lại sau !');
    }
    //9704198526191432198
    public function vnpayReturn(){
       if(isset($_GET["vnp_TxnRef"]) && isset($_GET["vnp_ResponseCode"])){
            $idDonHang = $_GET["vnp_TxnRef"];

             // lấy lại session
            $user = DB::table('khachhang')
                        ->join('donhang','khachhang.id_khach_hang','donhang.id_khach_hang')
                        ->select('khachhang.id_khach_hang','username','fullname')
                        ->where('donhang.id_don_hang','=',$idDonHang)
                        ->get();
                        
            if(count($user) > 0){
                session(['user_id' => $user[0]->id_khach_hang]);
                session(['user_name' => $user[0]->fullname]);
                session(['user_email' => $user[0]->username]);

                $state = $_GET["vnp_ResponseCode"];
                if($state == "00"){
                    $total = ((int) $_GET["vnp_Amount"])/100;

                    $data = DB::table('trangthaithanhtoan')
                        ->insert([
                            'id_don_hang' => $idDonHang,
                            'trang_thai_thanh_toan' => 1 
                        ]);
                    $data = DB::table('donhang')
                        ->where('id_don_hang','=',$idDonHang)
                        ->update([
                            'trang_thai_don_hang' => 1
                        ]);

                    if($data > 0) 
                        return redirect()->route('order.index')->with('success','Đơn hàng đã được đặt thành công !'); 
                }
                else if($state == "24")
                    return redirect()->route('cart.index')->with('error','Bạn đã hủy giao dịch !'); 
                
            } 
       }
        return redirect()->route('cart.index')->with('error','Đã có lỗi trong lúc giao dịch');
    }

    function myHistory(){
        if(session('user_id')){
            $data = [];
            // danh sách order đã duyệt
            $orders = DB::table('donhang')
                        ->join('khachhang','khachhang.id_khach_hang','donhang.id_khach_hang')
                        ->select('id_don_hang','fullname','tong_tien','loai_thanh_toan','ngay_giao','ngay_dat','trang_thai_don_hang')
                        ->whereIn('trang_thai_don_hang',[7,8])
                        ->where('khachhang.id_khach_hang','=',session('user_id'))
                        ->orderBy('ngay_dat','desc')
                        ->get();
            $data["order"] = $orders;
            return view('history')->with('data',$data);
        }
        return  redirect()->route('order.index')->with('error','Không thể xem lịch sử, vui lòng thử lại sau !');
    }

    function detailOneOfHistory($idDonHang){
        $data = [];
        $donHang = DB::table('donhang')
                    ->join('khachhang','khachhang.id_khach_hang','donhang.id_khach_hang')
                    ->select('id_don_hang','fullname','tong_tien','loai_thanh_toan','ngay_dat','dia_chi','sdt')
                    ->where('donhang.id_don_hang','=',$idDonHang)
                    ->get();
        if(count($donHang) < 1)
            return redirect()->route('order.index')->with('error','Đã có lỗi xảy ra, vui lòng thủ lại sau !');

        $chitiet =  DB::table('donhang')
                    ->join('chitietdonhang','donhang.id_don_hang','chitietdonhang.id_don_hang')
                    ->join('chitietthietbi','chitietthietbi.id_chi_tiet_thiet_bi','chitietdonhang.id_chi_tiet_thiet_bi')
                    ->select('ten','gia_ban','src_anh','so_luong','chitietdonhang.tong_tien')
                    ->where('chitietdonhang.id_don_hang','=',$idDonHang)
                    ->get();
        
        $data["donHang"] = $donHang;
        $data["chitiet"] = $chitiet;
        return view('history')->with('data',$data);
    }

    function huyDon(Request $request){
        $data = DB::table('donhang')
                    ->where('id_don_hang','=',$request->input('idDonHang'))
                    ->update([
                        'trang_thai_don_hang' => 7
                    ]);
        if($data > 0)
            return redirect()->route('order.history')->with('success','Đã hủy đơn hàng !');
        return redirect()->route('order.history')->with('error','Đã có lỗi xảy ra, vui lòng thử lại sau !');
    }

    function daNhanDuocHang(Request $request){
        $data = DB::table('donhang')
        ->where('id_don_hang','=',$request->input('idDonHang'))
        ->update([
            'trang_thai_don_hang' => 8
        ]);
        if($data > 0)
            return redirect()->route('order.history')->with('success','Cảm ơn đã phản hồi đơn hàng !');
        return redirect()->route('order.history')->with('error','Đã có lỗi xảy ra, vui lòng thử lại sau !');
    }
}
