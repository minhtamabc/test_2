<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\CartController;

class ProductController extends Controller
{
      // Trang danh sách sản phẩm
      public function products()
      {
        $cart = new CartController();
        $data = [];
        $products = DB::table('chitietthietbi')
                    ->select('id_chi_tiet_thiet_bi','ten','gia_ban','src_anh')
                    ->where('trang_thai', 1)
                    ->get();
        try{
          $products = DB::table('chitietthietbi')
                ->select('id_chi_tiet_thiet_bi','ten','gia_ban','src_anh')
                ->get();

          $branch = DB::table('hang')
                ->select('ten_hang')
                ->get();
                
          $bestSeller = DB::table('thietbibanchay')
                    ->join('chitietthietbi','thietbibanchay.id_chi_tiet_thiet_bi','chitietthietbi.id_chi_tiet_thiet_bi')
                    ->select('thietbibanchay.id_chi_tiet_thiet_bi','chitietthietbi.ten','chitietthietbi.gia_ban','chitietthietbi.src_anh')
                    ->where('chitietthietbi.trang_thai', 1)
                    ->get();
                    
          $data["products"] = $products;
          $data["branch"] = $branch; 
          $data["bestSeller"] = $bestSeller;
          $data["donHang"] = $cart->countCart(session('user_id'));
        }
        catch(Exception $e){

          $data["products"] = [];
          $data["branch"] = []; 
          $data["bestSeller"] = [];
          $data["donHang"] = 0;
        }
       

        return view('home')->with('data',$data);
      }

      //lấy sản phầm dựa trên id
      function productDetail($id){
        $data = [];
        
        $chiTietThietBi = DB::table('chitietthietbi')
                      ->select('ten','gia_ban','src_anh','id_loai','id_chi_tiet_thiet_bi')
                      ->where('id_chi_tiet_thiet_bi','=',$id)
                      ->get();

        //lấy tên bảng thong số tương ứng
        $bangThongSo = DB::table('loaisanpham')
                      ->select('tenbangthongso','tenthongso')
                      ->where('id_loai_sp','=',$chiTietThietBi[0]->id_loai)
                      ->get();

        $thongSo = DB::table('chitietthietbi')
                      ->join($bangThongSo[0]->tenbangthongso,$bangThongSo[0]->tenbangthongso.'.id_chi_tiet_thiet_bi','chitietthietbi.id_chi_tiet_thiet_bi')
                      ->select($bangThongSo[0]->tenbangthongso.'.*')
                      ->where('chitietthietbi.id_chi_tiet_thiet_bi','=',$id)
                      ->get();

        $tenThongSo = DB::table($bangThongSo[0]->tenthongso)
                      ->select($bangThongSo[0]->tenthongso.'.ten_goi','loaidonvi.ten_don_vi')
                      ->leftJoin('loaidonvi',$bangThongSo[0]->tenthongso.'.id_don_vi','loaidonvi.id_don_vi')
                      ->orderBy($bangThongSo[0]->tenthongso.'.priority','asc')
                      ->get();

        // lấy thông số chi tiết của thiết bị          
        $indexOfTenThongSo = 0;
        foreach($thongSo[0] as $k=>$value){
          if($k !== 'id_chi_tiet_thiet_bi'){
            $tenThongSo[$indexOfTenThongSo]->value = $value;
            if($k == 'cong_nghe_nfc' || $k == 'micro'){
              ($value === 1 ? $tenThongSo[$indexOfTenThongSo]->value = 'Có hỗ trợ' : $tenThongSo[$indexOfTenThongSo]->value = 'Không hỗ trợ');
            }
            $indexOfTenThongSo++;
          }
        }

        //hiển thị thêm sản phẩm
        $hienThiThem = DB::table('chitietthietbi')
                      ->select('ten','gia_ban','src_anh','id_chi_tiet_thiet_bi')
                      ->where('id_chi_tiet_thiet_bi','<>',$id)
                      ->limit(5)
                      ->get();
        $donHang = "";
        if(session('user_id')){
          $cart = new CartController();
          $donHang = $cart->countCart(session('user_id'));
        }

        $data["chiTietThietBi"] = $chiTietThietBi;
        $data["thongSoThietBi"] = $tenThongSo;
        $data["hienThiThem"] = $hienThiThem;
        $data["donHang"] = $donHang;
        
        return view('productDetail')->with('data',$data);
      }
}
