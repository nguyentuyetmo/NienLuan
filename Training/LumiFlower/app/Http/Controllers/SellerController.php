<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function dashboard(Request $request)
    {
        $sellerId = auth()->id();

        // Chỉ lấy các đơn hàng có chứa mẫu hoa thuộc về người bán đang đăng nhập
        $baseOrders = Order::whereHas('items', function ($q) use ($sellerId) {
            $q->whereIn('product_id', Product::where('user_id', $sellerId)->pluck('id'));
        });

        $pendingOrders    = (clone $baseOrders)->where('status', 'pending')->count();
        $processingOrders = (clone $baseOrders)->whereIn('status', ['confirmed', 'shipping'])->count();
        $completedToday   = (clone $baseOrders)->where('status', 'completed')->whereDate('updated_at', today())->count();
        $lowStockCount    = Product::where('user_id', $sellerId)->where('stock', '<=', 5)->count();

        // Chỉ load các sản phẩm trong đơn thuộc về người bán này
        $sellerProductIds = Product::where('user_id', $sellerId)->pluck('id');
        $ordersQuery = (clone $baseOrders)
            ->with(['items' => function ($q) use ($sellerProductIds) {
                $q->whereIn('product_id', $sellerProductIds);
            }])
            ->latest();

        if ($request->filled('status')) {
            $ordersQuery->where('status', $request->status);
        }

        $orders   = $ordersQuery->paginate(10)->withQueryString();
        $products = Product::where('user_id', $sellerId)->orderBy('stock', 'asc')->get();

        // 8 danh mục chuẩn của Lumi Flower + gộp thêm danh mục có trong bảng products (nếu có)
        $defaultCategories = collect([
            'Hoa bó tươi',
            'Giỏ & Lẵng hoa',
            'Hoa cưới cầm tay',
            'Hoa Khai Trương',
            'Hoa Sinh Nhật',
            'Hoa Tình Yêu',
            'Hoa Tốt Nghiệp',
            'Hoa Chia Buồn',
        ]);

        $dbCategories = Product::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category');

        $categories = $defaultCategories->merge($dbCategories)->unique()->values();

        return view('seller.dashboard', compact(
            'pendingOrders',
            'processingOrders',
            'completedToday',
            'lowStockCount',
            'orders',
            'products',
            'categories'
        ));
    }

    public function updateOrderStatus(Request $request, Order $order)
    {
        $sellerId = auth()->id();
        $sellerProductIds = Product::where('user_id', $sellerId)->pluck('id');

        // Kiểm tra đơn hàng có chứa sản phẩm của người bán này không
        $hasAccess = $order->items()->whereIn('product_id', $sellerProductIds)->exists();
        abort_if(!$hasAccess, 403, 'Bạn không có quyền xử lý đơn hàng này.');

        $request->validate([
            'status' => 'required|in:pending,confirmed,shipping,completed,cancelled',
        ]);

        if ($request->status === 'cancelled' && $order->status !== 'cancelled') {
            foreach ($order->items as $item) {
                Product::where('id', $item->product_id)->increment('stock', $item->quantity);
            }
        }

        $order->update(['status' => $request->status]);

        return back()->with('status', "Đã cập nhật trạng thái đơn {$order->order_code}!");
    }

    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:180',
            'category'   => 'required|string|max:100',
            'price'      => 'required|integer|min:1000',
            'stock'      => 'required|integer|min:0',
            'image'      => 'nullable|string|max:500|required_without:image_file',
            'image_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072|required_without:image',
        ], [
            'image.required_without'      => 'Vui lòng nhập link ảnh hoặc chọn file ảnh hoa từ máy.',
            'image_file.required_without' => 'Vui lòng chọn file ảnh hoa từ máy hoặc nhập link ảnh.',
            'image_file.image'            => 'Tệp tải lên phải là hình ảnh (JPG, PNG, WEBP).',
            'image_file.max'              => 'Dung lượng ảnh tối đa là 3MB.',
        ]);

        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('products', 'public');
            $imagePath = '/storage/' . $path;
        } else {
            $imagePath = trim($validated['image']);
        }

        Product::create([
            'user_id'     => auth()->id(),
            'name'        => $validated['name'],
            'category'    => $validated['category'],
            'price'       => $validated['price'],
            'stock'       => $validated['stock'],
            'image'       => $imagePath,
            'is_approved' => false,
        ]);

        return back()->with('status', "Đã gửi mẫu hoa {$validated['name']}, đang chờ Admin phê duyệt!");
    }

    public function updateProduct(Request $request, Product $product)
    {
        // Chặn người bán chỉnh sửa sản phẩm của người bán khác
        abort_if($product->user_id !== auth()->id(), 403, 'Bạn không có quyền chỉnh sửa mẫu hoa này.');

        $validated = $request->validate([
            'price' => 'required|integer|min:1000',
            'stock' => 'required|integer|min:0',
        ]);

        $product->update($validated);

        return back()->with('status', "Đã cập nhật giá & tồn kho cho {$product->name}!");
    }
}