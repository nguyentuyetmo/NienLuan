<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $bestSellers = Product::where('is_approved', true)->orderBy('id', 'asc')->take(4)->get();
        $favorites   = Product::where('is_approved', true)->orderBy('id', 'desc')->take(4)->get();

        return view('home', compact('bestSellers', 'favorites'));
    }

    public function products(Request $request)
    {
        // Chỉ hiển thị các mẫu hoa đã được Admin duyệt
        $query = Product::where('is_approved', true);

        // 1. Tìm kiếm theo tên (dùng ilike cho PostgreSQL để không phân biệt hoa/thường)
        if ($request->filled('search')) {
            $query->where('name', 'ilike', '%' . $request->search . '%');
        }

        // 2. Lọc theo danh mục
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // 3. Lọc theo khoảng giá
        if ($request->filled('price_range')) {
            switch ($request->price_range) {
                case 'under-300':
                    $query->where('price', '<', 300000);
                    break;
                case '300-500':
                    $query->whereBetween('price', [300000, 500000]);
                    break;
                case '500-1000':
                    $query->whereBetween('price', [500000, 1000000]);
                    break;
                case 'over-1000':
                    $query->where('price', '>', 1000000);
                    break;
            }
        }

        // 4. Sắp xếp giá
        if ($request->filled('sort')) {
            if ($request->sort === 'price-asc') {
                $query->orderBy('price', 'asc');
            } elseif ($request->sort === 'price-desc') {
                $query->orderBy('price', 'desc');
            } else {
                $query->latest();
            }
        } else {
            $query->latest();
        }

        // Lấy danh sách sản phẩm và giữ nguyên các tham số query khi chuyển trang
        $products = $query->paginate(12)->withQueryString();

        return view('products', compact('products'));
    }

    public function show($id)
    {
        // Chỉ cho phép khách xem chi tiết sản phẩm đã được duyệt
        $product = Product::where('is_approved', true)->find($id);

        if (!$product) {
            $product = (object) [
                'id'          => $id,
                'name'        => 'Bó Hoa Hồng Đỏ Yêu Thương Premium',
                'price'       => 590000,
                'old_price'   => 690000,
                'sku'         => 'LUMI-ROSE-0' . $id,
                'category'    => 'Hoa Tình Yêu',
                'in_stock'    => true,
                'description' => 'Bó hoa hồng đỏ cao cấp phối cùng hoa baby trắng tinh khôi và lá đô-la nhập khẩu. Thiết kế mang phong cách hiện đại, tinh tế, gửi gắm thông điệp yêu thương chân thành nhất.',
                'images'      => [
                    'https://images.unsplash.com/photo-1561181286-d3fee7d55364?q=80&w=800&auto=format&fit=crop',
                ],
            ];
        } else {
            if (empty($product->images)) {
                $product->images = [
                    $product->image ?? 'https://images.unsplash.com/photo-1561181286-d3fee7d55364?q=80&w=800&auto=format&fit=crop',
                ];
            }
        }

        // Lấy ngẫu nhiên 4 sản phẩm đã duyệt khác sản phẩm hiện tại cho mục "Có thể bạn sẽ thích"
        $relatedProducts = Product::where('is_approved', true)
            ->where('id', '!=', $id)
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('show', compact('product', 'relatedProducts'));
    }
}