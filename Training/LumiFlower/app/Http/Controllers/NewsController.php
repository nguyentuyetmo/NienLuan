<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index()
    {
        // Trong thực tế, dữ liệu này sẽ lấy từ Database: Article::all();
        $articles = [
            [
                'id' => 1,
                'category' => 'Mẹo chăm sóc',
                'title' => 'Bí quyết giữ hoa tươi lâu đến 7 ngày đơn giản tại nhà',
                'date' => '14/10/2026',
                'image' => 'https://images.unsplash.com/photo-1526047932273-341f2a7631f9?q=80&w=800&auto=format&fit=crop',
                'excerpt' => 'Chỉ với một vài thao tác cắt gốc chéo 45 độ và thay nước mỗi ngày, bình hoa của bạn sẽ ngát hương cả tuần.',
                'content' => '<p>Để bình hoa luôn tươi tắn và rạng rỡ suốt cả tuần, hãy áp dụng những bí quyết đơn giản sau từ florists Lumi Flower:</p><p><strong>1. Cắt gốc hoa đúng cách:</strong> Dùng kéo sắc cắt vát gốc hoa một góc 45 độ dưới vòi nước chảy.</p><p><strong>2. Tỉa bỏ lá chân:</strong> Bỏ hết các lá ở phần thân ngập trong nước để tránh bị phân hủy sinh vi khuẩn.</p><p><strong>3. Thay nước hàng ngày:</strong> Rửa sạch bình và thay nước mát mỗi sáng.</p>'
            ],
            [
                'id' => 2,
                'category' => 'Ý nghĩa loài hoa',
                'title' => 'Ý nghĩa số lượng hoa hồng trong tình yêu ít ai biết',
                'date' => '12/10/2026',
                'image' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?q=80&w=800&auto=format&fit=crop',
                'excerpt' => '1 bông, 9 bông hay 99 bông hoa hồng có thông điệp gì đặc biệt? Cùng Lumi Flower tìm hiểu chi tiết.',
                'content' => '<p>Số lượng hoa hồng trao tặng chứa đựng thông điệp tình yêu vô cùng sâu sắc:</p><p><strong>1 Bông:</strong> Trong trái tim anh, em là duy nhất.</p><p><strong>9 Bông:</strong> Tình yêu vĩnh cửu, bền chặt theo thời gian.</p><p><strong>99 Bông:</strong> Lời hứa bên nhau trọn đời trọn kiếp.</p>'
            ],
            [
                'id' => 3,
                'category' => 'Cẩm nang cưới',
                'title' => 'Xu hướng chọn hoa cưới cầm tay cô dâu năm 2026',
                'date' => '10/10/2026',
                'image' => 'https://images.unsplash.com/photo-1563241527-3004b7be0ffd?q=80&w=800&auto=format&fit=crop',
                'excerpt' => 'Sự lên ngôi của phong cách hoa cưới pastel nhẹ nhàng mang đậm chất tự nhiên Hàn Quốc.',
                'content' => '<p>Năm 2026 ghi nhận sự trỗi dậy của phong cách hoa cưới pastel nhẹ nhàng mang đậm chất tự nhiên Hàn Quốc.</p>'
            ],
            [
                'id' => 4,
                'category' => 'Gợi ý khai trương',
                'title' => 'Top 5 loài hoa mang lại may mắn cho lễ khai trương',
                'date' => '08/10/2026',
                'image' => 'https://images.unsplash.com/photo-1582794543139-8ac9cb0f7b11?q=80&w=800&auto=format&fit=crop',
                'excerpt' => 'Hoa hướng dương, hoa đồng tiền và hoa lan hồ điệp là những lựa chọn tài lộc hàng đầu.',
                'content' => '<p>Hoa Hướng Dương, Hoa Đồng Tiền và Lan Hồ Điệp là những lựa chọn mang lại may mắn và tài lộc hàng đầu.</p>'
            ],
            [
                'id' => 5,
                'category' => 'Cắm hoa cơ bản',
                'title' => 'Hướng dẫn cắm bình hoa để bàn phòng khách chuẩn Spa',
                'date' => '05/10/2026',
                'image' => 'https://images.unsplash.com/photo-1508610048659-a06b669e3321?q=80&w=800&auto=format&fit=crop',
                'excerpt' => 'Tạo không gian thư thái cho ngôi nhà bạn bằng những nguyên tắc cắm hoa đối xứng đơn giản.',
                'content' => '<p>Tạo không gian ấm cúng cho ngôi nhà bằng nguyên tắc cắm hoa đối xứng đơn giản và tinh tế.</p>'
            ],
            [
                'id' => 6,
                'category' => 'Chăm sóc chuyên sâu',
                'title' => 'Nên dùng thuốc dưỡng hoa hay dung dịch tự pha tại nhà?',
                'date' => '01/10/2026',
                'image' => 'https://images.unsplash.com/photo-1490750967868-88aa4486c946?q=80&w=800&auto=format&fit=crop',
                'excerpt' => 'So sánh hiệu quả của nước đường, giấm trắng và gói dưỡng hoa chuyên dụng trong việc duy trì độ tươi.',
                'content' => '<p>Thuốc dưỡng hoa chuyên dụng giúp hoa nở đều và diệt khuẩn tối ưu hơn dung dịch tự pha tại nhà.</p>'
            ],
        ];

        return view('news.index', compact('articles'));
    }
}