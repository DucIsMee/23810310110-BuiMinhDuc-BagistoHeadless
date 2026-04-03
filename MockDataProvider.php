<?php

namespace App\Services;

/**
 * MockDataProvider - Cung cấp dữ liệu mẫu khi chưa có Bagisto API thực
 * 
 * Lớp này mô phỏng response từ GraphQL API để có thể chạy demo
 * mà không cần cài đặt Bagisto backend.
 * 
 * Trong môi trường thực tế, thay thế bằng GraphQLService.
 */
class MockDataProvider
{
    /**
     * Dữ liệu mẫu: Categories
     */
    public function getCategories(): array
    {
        return [
            'data' => [
                'categories' => [
                    'data' => [
                        ['id' => '1', 'name' => 'Electronics',   'slug' => 'electronics'],
                        ['id' => '2', 'name' => 'Clothing',      'slug' => 'clothing'],
                        ['id' => '3', 'name' => 'Books',         'slug' => 'books'],
                        ['id' => '4', 'name' => 'Home & Garden', 'slug' => 'home-garden'],
                        ['id' => '5', 'name' => 'Sports',        'slug' => 'sports'],
                    ]
                ]
            ]
        ];
    }

    /**
     * Dữ liệu mẫu: 5 sản phẩm mới nhất
     * Tên sản phẩm theo cú pháp: [HọTênSV]_[TênSảnPhẩm]
     */
    public function getLatestProducts(): array
    {
        return [
            'data' => [
                'products' => [
                    'data' => [
                        [
                            'id'          => '1',
                            'name'        => 'NguyenVanA_Laptop_Gaming_Pro',
                            'price'       => '25990000',
                            'description' => 'Laptop gaming cao cấp với chip Intel Core i9 thế hệ mới nhất, RAM 32GB DDR5, SSD NVMe 1TB. Màn hình 16 inch 240Hz QHD IPS. Pin 99Wh với sạc nhanh 240W.',
                            'url_key'     => 'nguyenvana-laptop-gaming-pro',
                            'images'      => [],
                        ],
                        [
                            'id'          => '2',
                            'name'        => 'NguyenVanA_Mechanical_Keyboard',
                            'price'       => '2490000',
                            'description' => 'Bàn phím cơ không dây 75% layout, switch Gateron Yellow, hot-swap PCB, foam tiêu âm. Kết nối Bluetooth 5.0 hoặc USB-C. Pin 4000mAh, RGB per-key.',
                            'url_key'     => 'nguyenvana-mechanical-keyboard',
                            'images'      => [],
                        ],
                        [
                            'id'          => '3',
                            'name'        => 'NguyenVanA_Wireless_Mouse',
                            'price'       => '1290000',
                            'description' => 'Chuột không dây gaming siêu nhẹ 59g, cảm biến PixArt 3395, polling rate 8000Hz. Kết nối 2.4GHz ultra-low latency. Vỏ chuột honeycomb thoáng mát.',
                            'url_key'     => 'nguyenvana-wireless-mouse',
                            'images'      => [],
                        ],
                        [
                            'id'          => '4',
                            'name'        => 'NguyenVanA_Monitor_27inch',
                            'price'       => '8990000',
                            'description' => 'Màn hình gaming 27 inch 2K IPS 165Hz, thời gian phản hồi 1ms GTG. HDR400, 95% DCI-P3. Cổng DisplayPort 1.4 và HDMI 2.1. Chân đế điều chỉnh được.',
                            'url_key'     => 'nguyenvana-monitor-27inch',
                            'images'      => [],
                        ],
                        [
                            'id'          => '5',
                            'name'        => 'Headset_Gaming_7.1_Surround',
                            'price'       => '1890000',
                            'description' => 'Tai nghe gaming 7.1 virtual surround, driver 50mm, micro khử ồn AI. Kết nối USB và 3.5mm. Đệm tai memory foam cao cấp, trọng lượng 280g.',
                            'url_key'     => 'headset-gaming-71-surround',
                            'images'      => [],
                        ],
                    ],
                    'paginatorInfo' => ['count' => 5, 'total' => 5]
                ]
            ]
        ];
    }

    /**
     * Dữ liệu mẫu: Sản phẩm theo tên sinh viên (Query 3)
     */
    public function getProductsByStudentName(): array
    {
        return [
            'data' => [
                'products' => [
                    'data' => [
                        [
                            'id'          => '1',
                            'name'        => 'NguyenVanA_Laptop_Gaming_Pro',
                            'price'       => '25990000',
                            'description' => 'Laptop gaming cao cấp với chip Intel Core i9.',
                            'url_key'     => 'nguyenvana-laptop-gaming-pro',
                        ],
                        [
                            'id'          => '2',
                            'name'        => 'NguyenVanA_Mechanical_Keyboard',
                            'price'       => '2490000',
                            'description' => 'Bàn phím cơ không dây 75% layout.',
                            'url_key'     => 'nguyenvana-mechanical-keyboard',
                        ],
                        [
                            'id'          => '3',
                            'name'        => 'NguyenVanA_Wireless_Mouse',
                            'price'       => '1290000',
                            'description' => 'Chuột không dây gaming siêu nhẹ.',
                            'url_key'     => 'nguyenvana-wireless-mouse',
                        ],
                    ],
                    'paginatorInfo' => ['count' => 3, 'total' => 3]
                ]
            ]
        ];
    }
}
