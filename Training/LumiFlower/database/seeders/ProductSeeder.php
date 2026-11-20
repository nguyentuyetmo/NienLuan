<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Bó hoa hồng pastel ngọt ngào',
                'category' => 'Bó hoa tươi',
                'sku' => 'LF-001',
                'price' => 450000,
                'old_price' => 550000,
                'image' => 'https://images.unsplash.com/photo-1563241527-3004b7be0ffd?q=80&w=600&auto=format&fit=crop',
                'description' => 'Bó hoa hồng kem pastel kết hợp lá bạch đàn thanh lịch, mang vẻ đẹp dịu dàng và tinh tế.',
            ],
            [
                'name' => 'Bó hoa hướng dương rực rỡ',
                'category' => 'Bó hoa tươi',
                'sku' => 'LF-002',
                'price' => 380000,
                'old_price' => 450000,
                'image' => 'https://images.unsplash.com/photo-1526047932273-341f2a7631f9?q=80&w=600&auto=format&fit=crop',
                'description' => 'Hướng dương vàng rực mang thông điệp năng lượng tích cực, chúc mừng thành công và tốt nghiệp.',
            ],
            [
                'name' => 'Giỏ hoa tone hồng tinh tế',
                'category' => 'Giỏ hoa',
                'sku' => 'LF-003',
                'price' => 550000,
                'old_price' => 650000,
                'image' => 'https://images.unsplash.com/photo-1582794543139-8ac9cb0f7b11?q=80&w=600&auto=format&fit=crop',
                'description' => 'Giỏ hoa đan mây thủ công phối hồng ohara và cẩm chướng hồng sang trọng.',
            ],
            [
                'name' => 'Bó hoa tulip hồng nhập khẩu',
                'category' => 'Bó hoa tươi',
                'sku' => 'LF-004',
                'price' => 650000,
                'old_price' => 750000,
                'image' => 'https://images.unsplash.com/photo-1520763185298-1b434c919102?q=80&w=600&auto=format&fit=crop',
                'description' => 'Hoa tulip Hà Lan nhập khẩu tươi mới mỗi ngày, tượng trưng cho tình yêu ngọt ngào.',
            ],
            [
                'name' => 'Hộp hoa mẫu đơn đài bắc kiêu sa',
                'category' => 'Hộp hoa',
                'sku' => 'LF-005',
                'price' => 720000,
                'old_price' => 850000,
                'image' => 'https://images.unsplash.com/photo-1582794543139-8ac9cb0f7b11?q=80&w=600&auto=format&fit=crop',
                'description' => 'Thiết kế hộp tròn cao cấp với hoa mẫu đơn nở rộ quyến rũ, phù hợp tặng sinh nhật và kỷ niệm.',
            ],
            [
                'name' => 'Bó hoa baby trắng bồng bềnh',
                'category' => 'Bó hoa tươi',
                'sku' => 'LF-006',
                'price' => 350000,
                'old_price' => 420000,
                'image' => 'https://images.unsplash.com/photo-1563241527-3004b7be0ffd?q=80&w=600&auto=format&fit=crop',
                'description' => 'Hoa baby trắng tinh khôi gói giấy lụa Hàn Quốc nhẹ nhàng, lưu giữ hương thơm dịu nhẹ.',
            ],
            [
                'name' => 'Lẵng hoa cúc tana thơ mộng',
                'category' => 'Giỏ hoa',
                'sku' => 'LF-007',
                'price' => 480000,
                'old_price' => 550000,
                'image' => 'https://images.unsplash.com/photo-1520763185298-1b434c919102?q=80&w=600&auto=format&fit=crop',
                'description' => 'Những bông cúc tana nhỏ xinh mang nét đẹp mộc mạc, gần gũi và ấm áp.',
            ],
            [
                'name' => 'Kệ hoa lan hồ điệp sang trọng',
                'category' => 'Hoa khai trương',
                'sku' => 'LF-008',
                'price' => 1250000,
                'old_price' => 1500000,
                'image' => 'https://images.unsplash.com/photo-1526047932273-341f2a7631f9?q=80&w=600&auto=format&fit=crop',
                'description' => 'Lan hồ điệp đại cành lớn kết hợp phụ kiện chúc mừng khai trương hồng phát.',
            ],
        ];

        foreach ($products as $item) {
            Product::create([
                'name'        => $item['name'],
                'slug'        => Str::slug($item['name']),
                'category'    => $item['category'],
                'sku'         => $item['sku'],
                'price'       => $item['price'],
                'old_price'   => $item['old_price'],
                'image'       => $item['image'],
                'images'      => [$item['image']],
                'in_stock'    => true,
                'description' => $item['description'],
                'details'     => [
                    'Danh mục'   => $item['category'],
                    'Mã sản phẩm'=> $item['sku'],
                    'Độ tươi'    => 'Cam kết tươi mới 3 - 5 ngày',
                    'Phụ kiện'   => 'Tặng kèm thiệp chúc mừng miễn phí',
                ],
            ]);
        }
    }
}