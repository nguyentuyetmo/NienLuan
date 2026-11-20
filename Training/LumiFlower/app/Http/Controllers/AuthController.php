<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // Hiển thị form đăng nhập
    public function showLogin()
    {
        return view('auth.login');
    }

    // Xử lý đăng nhập & điều hướng theo phân quyền
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            // Kiểm tra trạng thái hoạt động của tài khoản
            if (! $user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Tài khoản của bạn đã bị tạm khóa. Vui lòng liên hệ Quản trị viên.',
                ])->onlyInput('email');
            }

            // Điều hướng tự động theo vai trò (Role)
            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'))->with('success', 'Đăng nhập Quản trị viên thành công!');
            }

            if ($user->isSeller()) {
                return redirect()->intended(route('seller.dashboard'))->with('success', 'Đăng nhập Kênh Người Bán Hoa thành công!');
            }

            // Khách hàng: tự động quay lại trang /thanh-toan nếu bấm thanh toán trước đó
            return redirect()->intended(route('home'))->with('success', 'Đăng nhập thành công!');
        }

        return back()->withErrors([
            'email' => 'Email hoặc mật khẩu không chính xác.',
        ])->onlyInput('email');
    }

    // Hiển thị form đăng ký
    public function showRegister()
    {
        return view('auth.register');
    }

    // Xử lý đăng ký tài khoản mới (mặc định vai trò Customer)
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'role'      => 'customer',
            'is_admin'  => false,
            'is_active' => true,
        ]);

        Auth::login($user);

        return redirect()->intended(route('home'))->with('success', 'Đăng ký tài khoản thành công!');
    }

    // Đăng xuất
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    // 1. Hiển thị form nhập email quên mật khẩu
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    // 2. Tạo token đặt lại mật khẩu lưu vào Database
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'Không tìm thấy tài khoản nào đăng ký với email này.',
        ]);

        $token = Str::random(64);

        // Lưu token vào bảng password_reset_tokens có sẵn của Laravel
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token'      => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Tạo link đặt lại mật khẩu
        $resetUrl = route('password.reset', ['token' => $token, 'email' => $request->email]);

        return back()
            ->with('status', 'Đã tạo liên kết khôi phục mật khẩu cho email ' . $request->email . '!')
            ->with('reset_link', $resetUrl);
    }

    // 3. Hiển thị form nhập mật khẩu mới
    public function showResetPassword(Request $request, $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    // 4. Cập nhật mật khẩu mới vào bảng users
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email|exists:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (! $record || ! Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.']);
        }

        // Cập nhật mật khẩu mới trong PostgreSQL
        User::where('email', $request->email)->update([
            'password' => Hash::make($request->password),
        ]);

        // Xóa token sau khi dùng xong
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('status', 'Đặt lại mật khẩu thành công! Vui lòng đăng nhập lại.');
    }
}