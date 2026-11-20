<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // Hiển thị trang giỏ hàng
    public function index()
    {
        $cart = session()->get('cart', []);

        $totalQuantity = array_sum(array_column($cart, 'quantity'));
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $shipping = $subtotal > 0 ? 30000 : 0;
        $discount = $subtotal >= 1000000 ? 100000 : 0; // Giảm 100.000đ cho đơn từ 1.000.000đ
        $total = max(0, $subtotal + $shipping - $discount);

        return view('cart', compact('cart', 'totalQuantity', 'subtotal', 'shipping', 'discount', 'total'));
    }

    // Thêm sản phẩm vào giỏ hàng (có kiểm tra tồn kho)
    public function add(Request $request, $id)
    {
        $product = Product::find($id);
        $quantity = max(1, (int) $request->input('quantity', 1));

        // Lấy thông tin từ DB (hoặc dữ liệu dự phòng)
        $name     = $product ? $product->name : 'Bó hoa tươi Lumi #' . $id;
        $price    = $product ? $product->price : 450000;
        $image    = $product ? $product->image : 'https://images.unsplash.com/photo-1563241527-3004b7be0ffd?q=80&w=400&auto=format&fit=crop';
        $category = $product ? ($product->category ?? 'Tiêu chuẩn') : 'Tiêu chuẩn';
        $stock    = $product ? ($product->stock ?? 20) : 20;

        $cart = session()->get('cart', []);
        $currentInCart = isset($cart[$id]) ? $cart[$id]['quantity'] : 0;

        // Chặn nếu tổng số lượng trong giỏ + số lượng mới vượt quá tồn kho
        if ($currentInCart + $quantity > $stock) {
            $msg = 'Không thể thêm! "' . $name . '" chỉ còn ' . $stock . ' sản phẩm trong kho'
                 . ($currentInCart > 0 ? ' (giỏ hàng của bạn đã có ' . $currentInCart . ').' : '.');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }

            return redirect()->back()->with('error', $msg);
        }

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] += $quantity;
            $cart[$id]['stock'] = $stock;
        } else {
            $cart[$id] = [
                'id'       => $id,
                'name'     => $name,
                'price'    => $price,
                'image'    => $image,
                'category' => $category,
                'quantity' => $quantity,
                'stock'    => $stock,
            ];
        }

        session()->put('cart', $cart);

        $totalQuantity = array_sum(array_column($cart, 'quantity'));

        if ($request->expectsJson()) {
            return response()->json([
                'success'       => true,
                'message'       => 'Đã thêm "' . $name . '" vào giỏ hàng!',
                'totalQuantity' => $totalQuantity,
            ]);
        }

        // Chuyển thẳng sang giỏ hàng nếu bấm nút "Đặt hoa ngay"
        if ($request->filled('redirect_to_cart')) {
            return redirect()->route('cart.index')->with('success', 'Đã thêm "' . $name . '" vào giỏ hàng!');
        }

        return redirect()->back()->with('success', 'Đã thêm "' . $name . '" vào giỏ hàng!');
    }

    // Cập nhật số lượng (+ / -) trong trang giỏ hàng (có kiểm tra tồn kho)
    public function update(Request $request, $id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            $action = $request->input('action');

            if ($action === 'increase') {
                $product = Product::find($id);
                $stock = $product ? ($product->stock ?? 20) : ($cart[$id]['stock'] ?? 20);

                if ($cart[$id]['quantity'] + 1 > $stock) {
                    return redirect()->route('cart.index')
                        ->with('error', 'Sản phẩm "' . $cart[$id]['name'] . '" chỉ còn tối đa ' . $stock . ' bó trong kho!');
                }

                $cart[$id]['quantity']++;
                $cart[$id]['stock'] = $stock;
            } elseif ($action === 'decrease') {
                $cart[$id]['quantity']--;
                if ($cart[$id]['quantity'] <= 0) {
                    unset($cart[$id]);
                }
            }

            session()->put('cart', $cart);
        }

        return redirect()->route('cart.index');
    }

    // Xóa sản phẩm khỏi giỏ
    public function remove($id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }

        return redirect()->route('cart.index');
    }
}