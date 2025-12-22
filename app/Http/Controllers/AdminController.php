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

    //day la bug
    function abc(){

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
   
}
