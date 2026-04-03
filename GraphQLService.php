<?php

namespace App\Services;

/**
 * GraphQLService - Lớp dịch vụ xử lý toàn bộ giao tiếp với Bagisto GraphQL API
 * 
 * Lớp này đóng gói các phương thức gọi API thông qua cURL,
 * giúp tái sử dụng code và tách biệt logic API khỏi tầng hiển thị.
 */
class GraphQLService
{
    /**
     * URL endpoint của GraphQL API trên Bagisto
     * Mặc định trỏ đến localhost - thay đổi nếu deploy lên server khác
     */
    private string $endpoint;

    public function __construct(string $endpoint = 'http://localhost:8000/graphql')
    {
        $this->endpoint = $endpoint;
    }

    /**
     * Phương thức gốc để thực hiện bất kỳ GraphQL query/mutation nào
     * 
     * @param string $query  - Chuỗi GraphQL query
     * @param array  $variables - Biến truyền vào query (nếu có)
     * @return array - Kết quả trả về từ API dạng mảng PHP
     * @throws \RuntimeException nếu kết nối thất bại
     */
    public function query(string $query, array $variables = []): array
    {
        // Chuẩn bị payload gửi đến API theo chuẩn GraphQL over HTTP
        $payload = json_encode([
            'query'     => $query,
            'variables' => $variables,
        ]);

        // Khởi tạo cURL session
        $ch = curl_init($this->endpoint);

        // Cấu hình cURL: POST request với Content-Type JSON
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,   // Trả về response thay vì in ra màn hình
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT        => 30,     // Timeout 30 giây
            CURLOPT_SSL_VERIFYPEER => false,  // Bỏ qua SSL verification (dev only)
        ]);

        // Thực thi request và lấy response
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Xử lý lỗi kết nối cURL
        if ($curlError) {
            throw new \RuntimeException("cURL Error: {$curlError}");
        }

        // Giải mã JSON response thành mảng PHP
        $result = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Invalid JSON response from API (HTTP {$httpCode})");
        }

        return $result;
    }

    // =========================================================================
    // QUERY 1: Lấy danh sách Categories
    // =========================================================================

    /**
     * Query 1: Lấy toàn bộ danh mục sản phẩm
     * Trả về: id, name, slug của từng category
     */
    public function getCategories(): array
    {
        $query = <<<'GQL'
        query GetCategories {
            categories {
                data {
                    id
                    name
                    slug
                }
            }
        }
        GQL;

        return $this->query($query);
    }

    // =========================================================================
    // QUERY 2: Lấy 05 sản phẩm mới nhất
    // =========================================================================

    /**
     * Query 2: Lấy 5 sản phẩm mới nhất từ hệ thống
     * Trả về: id, name, price, description, url_key
     * Sử dụng tham số first=5 và sort theo created_at DESC
     */
    public function getLatestProducts(int $limit = 5): array
    {
        $query = <<<'GQL'
        query GetLatestProducts($limit: Int) {
            products(
                first: $limit,
                page: 1,
                sortBy: "created_at",
                sortOrder: "DESC"
            ) {
                data {
                    id
                    name
                    price
                    description
                    url_key
                    images {
                        path
                        url
                    }
                }
                paginatorInfo {
                    count
                    total
                }
            }
        }
        GQL;

        return $this->query($query, ['limit' => $limit]);
    }

    // =========================================================================
    // QUERY 3 (Nâng cao): Lọc sản phẩm theo tên sinh viên
    // =========================================================================

    /**
     * Query 3: Sử dụng filters để lọc sản phẩm theo tên
     * 
     * @param string $studentName - Họ tên sinh viên (dùng để filter)
     * Trả về các sản phẩm có tên chứa chuỗi tên sinh viên
     */
    public function getProductsByStudentName(string $studentName): array
    {
        $query = <<<'GQL'
        query GetStudentProducts($name: String) {
            products(
                filters: {
                    name: $name
                }
            ) {
                data {
                    id
                    name
                    price
                    description
                    url_key
                }
                paginatorInfo {
                    count
                    total
                }
            }
        }
        GQL;

        return $this->query($query, ['name' => $studentName]);
    }
}
