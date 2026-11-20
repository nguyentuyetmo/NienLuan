<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        // 1. Thống kê tổng quan từ PostgreSQL
        $totalRevenue   = Order::where('status', '!=', 'cancelled')->sum('total');
        $totalOrders    = Order::count();
        $pendingOrders  = Order::where('status', 'pending')->count();
        $totalProducts  = Product::where('is_approved', true)->count();
        $totalCustomers = User::where('role', 'customer')->where('is_admin', false)->count();
        $totalSellers   = User::where('role', 'seller')->count();

        // 2. Dữ liệu biểu đồ doanh thu 7 ngày gần nhất
        $revenueLabels = [];
        $revenueData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $revenueLabels[] = $date->format('d/m');
            $revenueData[] = (int) Order::where('status', '!=', 'cancelled')
                ->whereDate('created_at', $date)
                ->sum('total');
        }

        // 3. Thống kê số lượng đơn theo từng trạng thái (Biểu đồ tròn)
        $statusCounts = [
            'pending'   => $pendingOrders,
            'confirmed' => Order::where('status', 'confirmed')->count(),
            'shipping'  => Order::where('status', 'shipping')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        // 4. Top 5 mẫu hoa bán chạy nhất
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', '!=', 'cancelled')
            ->select(
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.price * order_items.quantity) as total_sales')
            )
            ->groupBy('order_items.product_name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        // 5. Lọc đơn hàng theo trạng thái hoặc từ khóa tìm kiếm
        $ordersQuery = Order::with(['items.product.seller'])->latest();

        if ($request->filled('status')) {
            $ordersQuery->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $ordersQuery->where(function ($q) use ($search) {
                $q->where('order_code', 'ilike', "%{$search}%")
                  ->orWhere('customer_name', 'ilike', "%{$search}%")
                  ->orWhere('customer_phone', 'ilike', "%{$search}%");
            });
        }

        $orders = $ordersQuery->paginate(10)->withQueryString();

        // 6. Danh sách sản phẩm (ưu tiên mẫu chưa duyệt và mẫu sắp hết hàng lên đầu)
        $products = Product::with('seller')->orderBy('is_approved', 'asc')->orderBy('stock', 'asc')->get();
        $lowStockProducts = $products->where('is_approved', true)->where('stock', '<=', 5)->values();

        // 7. Danh sách toàn bộ người dùng & lọc phân quyền
        $usersQuery = User::latest();

        if ($request->filled('user_role')) {
            $usersQuery->where('role', $request->user_role);
        }

        if ($request->filled('user_search')) {
            $uSearch = trim($request->user_search);
            $usersQuery->where(function ($q) use ($uSearch) {
                $q->where('name', 'ilike', "%{$uSearch}%")
                  ->orWhere('email', 'ilike', "%{$uSearch}%");
            });
        }

        $users = $usersQuery->get();

        // Tự động mở đúng Tab đang thao tác sau khi lọc/tìm kiếm/duyệt sản phẩm
        $activeTab = $request->query('tab') ?: (
            ($request->filled('search') || $request->filled('status') || $request->has('page'))
                ? 'orders'
                : (($request->filled('user_role') || $request->filled('user_search')) ? 'users' : 'overview')
        );

        return view('admin.dashboard', compact(
            'totalRevenue',
            'totalOrders',
            'pendingOrders',
            'totalProducts',
            'totalCustomers',
            'totalSellers',
            'revenueLabels',
            'revenueData',
            'statusCounts',
            'topProducts',
            'lowStockProducts',
            'orders',
            'products',
            'users',
            'activeTab'
        ));
    }

    // Cập nhật trạng thái đơn hàng động
    public function updateOrderStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,shipping,completed,cancelled',
        ]);

        // Nếu hủy đơn thì hoàn lại tồn kho sản phẩm
        if ($request->status === 'cancelled' && $order->status !== 'cancelled') {
            foreach ($order->items as $item) {
                Product::where('id', $item->product_id)->increment('stock', $item->quantity);
            }
        }

        $order->update(['status' => $request->status]);

        return back()->with('status', "Đã cập nhật trạng thái đơn {$order->order_code}!");
    }

    // Cập nhật nhanh số lượng tồn kho hoa
    public function updateStock(Request $request, Product $product)
    {
        $request->validate([
            'stock' => 'required|integer|min:0',
        ]);

        $product->update(['stock' => $request->stock]);

        return redirect()->route('admin.dashboard', ['tab' => 'products'])
            ->with('status', "Đã cập nhật tồn kho cho {$product->name}!");
    }

    // Duyệt mẫu hoa mới do Người bán đăng lên
    public function approveProduct(Product $product)
    {
        $product->update(['is_approved' => true]);

        return redirect()->route('admin.dashboard', ['tab' => 'products'])
            ->with('status', "Đã duyệt và mở bán mẫu hoa: {$product->name}!");
    }

    // Từ chối và xóa mẫu hoa không đạt yêu cầu
    public function destroyProduct(Product $product)
    {
        $name = $product->name;
        $product->delete();

        return redirect()->route('admin.dashboard', ['tab' => 'products'])
            ->with('status', "Đã từ chối và xóa mẫu hoa: {$name}!");
    }

    // Tạo tài khoản nhân viên bán hoa, admin hoặc khách hàng mới
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:120',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:admin,seller,customer',
        ]);

        User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => $validated['password'],
            'role'      => $validated['role'],
            'is_admin'  => $validated['role'] === 'admin',
            'is_active' => true,
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'users'])
            ->with('status', "Đã tạo tài khoản {$validated['name']} với quyền " . strtoupper($validated['role']) . "!");
    }

    // Thay đổi phân quyền người dùng (Admin / Người bán hoa / Khách hàng)
    public function updateUserRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:admin,seller,customer',
        ]);

        if ($user->id === auth()->id() && $request->role !== 'admin') {
            return redirect()->route('admin.dashboard', ['tab' => 'users'])
                ->with('error', 'Bạn không thể tự hạ quyền Quản trị viên của chính mình!');
        }

        $user->update([
            'role'     => $request->role,
            'is_admin' => $request->role === 'admin',
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'users'])
            ->with('status', "Đã cập nhật quyền cho {$user->name}!");
    }

    // Khóa hoặc mở khóa tài khoản
    public function toggleUserStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.dashboard', ['tab' => 'users'])
                ->with('error', 'Không thể tự khóa tài khoản đang đăng nhập!');
        }

        $user->update(['is_active' => ! $user->is_active]);
        $state = $user->is_active ? 'mở khóa' : 'tạm khóa';

        return redirect()->route('admin.dashboard', ['tab' => 'users'])
            ->with('status', "Đã {$state} tài khoản {$user->name}!");
    }

    // Xóa người dùng
    public function destroyUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.dashboard', ['tab' => 'users'])
                ->with('error', 'Không thể tự xóa tài khoản của chính mình!');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('admin.dashboard', ['tab' => 'users'])
            ->with('status', "Đã xóa tài khoản {$userName} khỏi hệ thống!");
    }
}