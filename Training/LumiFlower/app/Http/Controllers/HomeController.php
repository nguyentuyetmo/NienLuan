<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // 1. Lấy dữ liệu động của Hero Banner từ bảng settings
        $settings = Setting::pluck('value', 'key')->toArray();
        $defaultImage = Product::where('is_approved', true)->latest()->value('image')
            ?? 'https://images.unsplash.com/photo-1563241527-3004b7be0ffd?q=80&w=800&auto=format&fit=crop';

        $heroBanner = [
            'badge'       => $settings['hero_badge'] ?? 'SHOP HOA TƯƠI CAO CẤP CẦN THƠ',
            'title'       => $settings['hero_title'] ?? "Gửi trọn yêu thương\nqua từng đóa hoa tươi",
            'description' => $settings['hero_description'] ?? 'Lumi Flower mang tới bộ sưu tập hoa bó, giỏ hoa, hoa chúc mừng thiết kế tinh tế. Cung cấp dịch vụ thiệp chúc mừng & giao tận tay trong 2h.',
            'image'       => !empty($settings['hero_image']) ? asset($settings['hero_image']) : $defaultImage,
            'tag_title'   => $settings['hero_tag_title'] ?? 'Hoa Tươi 100% Nhập Mới',
            'tag_sub'     => $settings['hero_tag_sub'] ?? 'Cam kết tươi trên 3 ngày',
        ];

        // 2. Lấy danh sách sản phẩm đã được Admin duyệt cho Trang chủ
        $bestSellers = Product::where('is_approved', true)->take(4)->get();
        $favorites   = Product::where('is_approved', true)->latest()->take(4)->get();

        // 3. Truyền sang view home.blade.php
        return view('home', compact('heroBanner', 'bestSellers', 'favorites'));
    }
}