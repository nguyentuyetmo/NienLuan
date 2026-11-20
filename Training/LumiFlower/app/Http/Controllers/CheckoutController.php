<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    // Hiển thị trang thanh toán
    public function index()
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hoa của bạn đang trống!');
        }

        $totalQuantity = array_sum(array_column($cart, 'quantity'));
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $shipping = 30000;
        $discount = $subtotal >= 1000000 ? 100000 : 0;
        $total = max(0, $subtotal + $shipping - $discount);

        return view('checkout', compact('cart', 'totalQuantity', 'subtotal', 'shipping', 'discount', 'total'));
    }

    // Xử lý đặt hàng & Trừ tồn kho trong Database
    public function store(Request $request)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index');
        }

        $validated = $request->validate([
            'customer_name'  => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'province'       => 'required|string|max:100',
            'district'       => 'required|string|max:100',
            'ward'           => 'required|string|max:100',
            'address'        => 'required|string|max:255',
            'delivery_date'  => 'required|date|after_or_equal:today',
            'delivery_time'  => 'required|string|max:50',
            'card_message'   => 'nullable|string|max:1000',
            'payment_method' => 'required|in:cod,bank',
            'agree_terms'    => 'accepted',
        ], [
            'agree_terms.accepted' => 'Vui lòng đồng ý với Điều khoản đặt hàng và Chính sách bảo mật.',
        ]);

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $shipping = 30000;
        $discount = $subtotal >= 1000000 ? 100000 : 0;
        $total = max(0, $subtotal + $shipping - $discount);

        try {
            $order = DB::transaction(function () use ($validated, $cart, $subtotal, $shipping, $discount, $total) {
                $orderCode = 'LUMI-' . strtoupper(Str::random(6));

                $order = Order::create([
                    'order_code'     => $orderCode,
                    'user_id'        => Auth::id(),
                    'customer_name'  => $validated['customer_name'],
                    'customer_phone' => $validated['customer_phone'],
                    'customer_email' => $validated['customer_email'],
                    'province'       => $validated['province'],
                    'district'       => $validated['district'],
                    'ward'           => $validated['ward'],
                    'address'        => $validated['address'],
                    'delivery_date'  => $validated['delivery_date'],
                    'delivery_time'  => $validated['delivery_time'],
                    'card_message'   => $validated['card_message'],
                    'payment_method' => $validated['payment_method'],
                    'subtotal'       => $subtotal,
                    'shipping_fee'   => $shipping,
                    'discount'       => $discount,
                    'total'          => $total,
                    'status'         => 'pending',
                ]);

                foreach ($cart as $id => $item) {
                    OrderItem::create([
                        'order_id'     => $order->id,
                        'product_id'   => Product::find($id) ? $id : null,
                        'product_name' => $item['name'],
                        'price'        => $item['price'],
                        'quantity'     => $item['quantity'],
                    ]);

                    // Tự động trừ tồn kho trong database
                    $product = Product::lockForUpdate()->find($id);
                    if ($product) {
                        $newStock = max(0, ($product->stock ?? 0) - $item['quantity']);
                        $product->update([
                            'stock'    => $newStock,
                            'in_stock' => $newStock > 0,
                        ]);
                    }
                }

                return $order;
            });

            // Xóa giỏ hàng sau khi đặt thành công
            session()->forget('cart');

            return redirect()->route('checkout.success', $order->order_code);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Có lỗi xảy ra khi đặt hàng, vui lòng thử lại!');
        }
    }

    // Trang hoàn tất đơn hàng (Bước 3)
    public function success($orderCode)
    {
        $order = Order::with('items')->where('order_code', $orderCode)->firstOrFail();
        return view('checkout-success', compact('order'));
    }
}