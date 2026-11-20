<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    // Hiển thị trang Danh sách yêu thích khi bấm vào trái tim trên Header
    public function index()
    {
        $wishlists = Wishlist::with('product')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('wishlist.index', compact('wishlists'));
    }

    // Thêm / Bỏ tim qua AJAX (không tải lại trang)
    public function toggle(Request $request, $productId)
    {
        if (!Auth::check()) {
            return response()->json([
                'status'  => 'unauthenticated',
                'message' => 'Vui lòng đăng nhập để lưu sản phẩm yêu thích!'
            ], 401);
        }

        $userId = Auth::id();
        $existing = Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            Wishlist::create([
                'user_id'    => $userId,
                'product_id' => $productId,
            ]);
            $liked = true;
        }

        $count = Wishlist::where('user_id', $userId)->count();

        return response()->json([
            'status' => 'success',
            'liked'  => $liked,
            'count'  => $count,
        ]);
    }
}