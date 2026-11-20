<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactController extends Controller
{
    // Hiển thị giao diện trang liên hệ
    public function index()
    {
        return view('contact'); // Nếu file của bạn là contact.blade.php
    }

    // Xử lý gửi tin nhắn tư vấn
    public function send(Request $request)
    {
        // Kiểm tra dữ liệu bắt buộc (* Họ tên và * Số điện thoại)
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'required|string|max:20',
            'email'   => 'nullable|email|max:255',
            'message' => 'nullable|string|max:1000',
        ], [
            'name.required'  => 'Vui lòng nhập họ và tên của bạn.',
            'phone.required' => 'Vui lòng nhập số điện thoại liên hệ.',
            'email.email'    => 'Địa chỉ email không đúng định dạng.',
        ]);

        // Bạn có thể xử lý lưu vào Database hoặc gửi mail ở đây
        // Contact::create($validated);

        return redirect()->back()->with('success', 'Cảm ơn bạn! Lumi Flower đã nhận được tin nhắn và sẽ liên hệ hỗ trợ bạn ngay.');
    }
}