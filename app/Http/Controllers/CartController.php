<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartController extends Controller
{
    // Hiển thị giỏ hàng
    public function index()
    {
        $idProducts = DB::table('donhang')
                        ->join('chitietdonhang','chitietdonhang.id_don_hang','donhang.id_don_hang')
                        ->join('khachhang','khachhang.id_khach_hang','donhang.id_khach_hang')
                        ->select('chitietdonhang.id_chi_tiet_thiet_bi','chitietdonhang.so_luong','chitietdonhang.tong_tien','donhang.id_don_hang','dia_chi','sdt')
                        ->where('khachhang.id_khach_hang','=',session('user_id'))
                        ->where('donhang.trang_thai_don_hang',6)
                        ->get();

        $cartItems = [];
        $total = 0;
        $i = 0;
        foreach($idProducts as $id){
            $item = DB::table('chitietthietbi')
                            ->select('ten','gia_ban','id_chi_tiet_thiet_bi','so_luong_ton_kho','src_anh')
                            ->where('id_chi_tiet_thiet_bi','=',$id->id_chi_tiet_thiet_bi)
                            ->get();
            $cartItems[$i] = new \stdClass();

            $cartItems[$i]->name = $item[0]->ten;
            $cartItems[$i]->ton_kho = $item[0]->so_luong_ton_kho;
            $cartItems[$i]->id = $item[0]->id_chi_tiet_thiet_bi;
            $cartItems[$i]->gia_ban = $item[0]->gia_ban*1000;
            $cartItems[$i]->src_anh = $item[0]->src_anh;
           
            $cartItems[$i]->quantity = $id->so_luong;
            $cartItems[$i]->price = (int)$id->tong_tien*1000; 
            $cartItems[$i]->idDonHang = $id->id_don_hang;
            $cartItems[$i]->dia_chi = $id->dia_chi;
            $cartItems[$i]->sdt = $id->sdt;

           
            $total += $cartItems[$i]->price;
            
            $i++;
        }

        return view('cart', compact('cartItems', 'total'));
    }

    // Thêm sản phẩm vào giỏ hàng
    public function add(Request $request)
    {
        if(isset($_POST["btnThemVaoGio"])){
            $idProduct = $_POST["btnThemVaoGio"];
            //kiểm tra xem có đặt hàng chưa
            $donhang = DB::table('donhang')
                        ->select('id_don_hang')
                        ->where('id_khach_hang','=',session('user_id'))
                        ->where('trang_thai_don_hang',6)
                        ->get();

            $giaSP = DB::table('chitietthietbi')
                    ->select('gia_ban')
                    ->where('id_chi_tiet_thiet_bi','=',$idProduct)
                    ->get();

            if($giaSP[0]->gia_ban !== '')        
                $giaSP = (int)($giaSP[0]->gia_ban);
            else
                return redirect()->route('cart.index')->with('error', 'Đã có lỗi xảy ra, vui lòng thử lại sau !');

            //chưa đặt
            $soLuong = 1;
            if(!count($donhang)){
                $idDonHang = 'DH'.Str::random(8);

                $data = DB::table('donhang')->insert( [
                    'id_don_hang' => $idDonHang,
                    'id_khach_hang' => session('user_id'),
                    'trang_thai_don_hang' => 6
                ]);

                DB::table('chitietdonhang')->insert([
                    'id_don_hang' => $idDonHang,
                    'id_chi_tiet_thiet_bi' => $idProduct,
                    'so_luong' => $soLuong,
                    'tong_tien' => $giaSP*$soLuong
                ]);
            }
            else{
                //đã đặt - check sản phẩm
                $idDH = DB::table('chitietdonhang')
                            ->select('id_don_hang','so_luong')
                            ->where('id_chi_tiet_thiet_bi','=',$idProduct)
                            ->where('id_don_hang','=',$donhang[0]->id_don_hang)
                            ->get();
                // chưa tồn tại sản phẩm
                if(!count($idDH)){
                    DB::table('chitietdonhang')->insert([
                        'id_don_hang' => $donhang[0]->id_don_hang,
                        'id_chi_tiet_thiet_bi' => $idProduct,
                        'so_luong' => 1,
                        'tong_tien' => $giaSP*$soLuong
                    ]);
                }
                else{
                    DB::table('chitietdonhang')
                        ->where('id_don_hang','=',$idDH[0]->id_don_hang)
                        ->update([
                            'so_luong' => (int)$idDH[0]->so_luong + 1,
                            'tong_tien' => (int)$giaSP*($idDH[0]->so_luong + 1)
                        ]);
                }
            }
            return redirect()->route('cart.index')->with('sucess', 'Cập nhật vào giỏ hảng thành công !');
        }
        return redirect()->route('cart.index')->with('error', 'Đã có lỗi xảy ra, vui lòng thử lại sau !');
    }

    // Cập nhật số lượng
    public function update(Request $request, $idProduct,$idDonHang)
    {
        if(isset($_POST["update-quantity"])){
            $dataPrice = DB::table('chitietthietbi')
                            ->select('gia_ban')
                            ->where('id_chi_tiet_thiet_bi','=',$idProduct)
                            ->get();

            $giaSP = (int)$dataPrice[0]->gia_ban;
            $soLuong = (int) $_POST["quantity"];
            DB::table('chitietdonhang')
                ->where('id_don_hang','=',$idDonHang)
                ->where('id_chi_tiet_thiet_bi','=',$idProduct)
                ->update([
                    'so_luong' => $soLuong,
                    'tong_tien' => $soLuong*$giaSP
                ]);
            return redirect()->route('cart.index')->with('success', 'Đã cập nhật số lượng thành công!');
        }
        return redirect()->route('cart.index')->with('error', 'Đã có lỗi xảy ra, vui lòng thử lại sau !');
    }

    // Xóa sản phẩm khỏi giỏ hàng
    public function remove(Request $request,$idProduct,$idDonHang)
    {
        $state = DB::table('chitietdonhang')
                    ->where('id_don_hang','=',$idDonHang)
                    ->where('id_chi_tiet_thiet_bi','=',$idProduct)
                    ->delete();
        if($state)
            return redirect()->route('cart.index')->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng!');
        else
            return redirect()->route('cart.index')->with('error', 'Đã có lỗi xảy ra, vui lòng thử lại sau !');
    }

    // Xóa toàn bộ giỏ hàng
    public function clear($idDonHang)
    {
        $state = DB::table('chitietdonhang')
                    ->where('id_don_hang','=',$idDonHang)
                    ->delete();

        $state = DB::table('donhang')
                    ->where('id_don_hang','=',$idDonHang)
                    ->delete();
        if($state)
            return redirect()->route('cart.index')->with('success', 'Đã xóa toàn bộ giỏ hàng!');
        else
            return redirect()->route('cart.index')->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau !');
    }

    public function countCart($idKhachHang){
        $data = DB::table('donhang')
                    ->join('chitietdonhang','donhang.id_don_hang','chitietdonhang.id_don_hang')
                    ->select('chitietdonhang.id_don_hang')
                    ->where('donhang.id_khach_hang','=',$idKhachHang)
                    ->where('trang_thai_don_hang','=',6)
                    ->get();
        return count($data);
    }
}