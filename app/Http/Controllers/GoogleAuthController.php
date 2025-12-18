<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    // Chuyển hướng đến Google
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Xử lý callback từ Google
    public function handleGoogleCallback()
    {
        try {
            // Lấy thông tin user từ Google
            $googleUser = Socialite::driver('google')->user();
            
            // Kiểm tra xem user đã tồn tại trong database chưa
            $user = DB::table('khachhang')
                ->where('username', $googleUser->email)
                ->first();
            if ($user) {
                // Nếu đã tồn tại, đăng nhập
                session(['user_id' => $user->id_khach_hang]);
                session(['user_name' => $user->fullname]);
                session(['user_email' => $user->username]);
                
                return redirect('/')->with('success', 'Đăng nhập thành công!');
            } else {
                // Nếu chưa tồn tại, tạo user mới
                $newUserId = 'KH' . Str::random(8);
                
                DB::table('khachhang')->insert([
                    'id_khach_hang' => $newUserId,
                    'username' => $googleUser->email,
                    'password' => bcrypt(Str::random(16)), // Random password
                    'fullname' => $googleUser->name,
                    'ngay_tao' => now(),
                    'dia_chi' => null
                ]);

                // Đăng nhập user mới
                session(['user_id' => $newUserId]);
                session(['user_name' => $googleUser->name]);
                session(['user_email' => $googleUser->email]);
                
                return redirect('/')->with('success', 'Đăng ký và đăng nhập thành công!');
            }
        } catch (\Exception $e) {
            //var_dump($e);
            return redirect('/login')->with('error', 'Có lỗi xảy ra khi đăng nhập với Google: ' . $e->getMessage());
        }
    }

    // Đăng xuất
    public function logout()
    {
        session()->flush();
        return redirect('/login')->with('success', 'Đăng xuất thành công!');
    }
}