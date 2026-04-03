<?php

/**
 * ============================================================
 * BAGISTO HEADLESS COMMERCE - FRONTEND ỨNG DỤNG
 * ============================================================
 * 
 * Mô tả: Trang frontend đơn giản hiển thị dữ liệu sản phẩm
 * từ Bagisto GraphQL API sử dụng PHP cURL.
 * 
 * Cấu trúc:
 *   - Phần PHP: Gọi API, xử lý dữ liệu
 *   - Phần HTML/CSS/JS: Hiển thị giao diện người dùng
 * 
 * Tác giả: [Họ Tên Sinh Viên] - MSSV: [XXXXXXXX]
 * ============================================================
 */

// Tải autoloader của Composer để sử dụng các class đã định nghĩa
require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\GraphQLService;
use App\Services\MockDataProvider;

// ============================================================
// CẤU HÌNH - Thay đổi thông tin sinh viên tại đây
// ============================================================
$studentName = "Nguyễn Văn A";   // <-- Thay bằng họ tên thật
$studentId   = "21XXXXXXXX";      // <-- Thay bằng MSSV thật
$apiEndpoint = "http://localhost:8000/graphql";
$useRealApi  = false; // Đặt true khi Bagisto đang chạy

// ============================================================
// BƯỚC 1: Khởi tạo service và lấy dữ liệu
// ============================================================

$products   = [];
$categories = [];
$apiError   = null;

if ($useRealApi) {
    /**
     * Sử dụng GraphQLService thực để gọi Bagisto API
     * 
     * Luồng xử lý:
     * 1. Khởi tạo GraphQLService với endpoint
     * 2. Gọi getLatestProducts() -> cURL POST -> /graphql
     * 3. Nhận JSON response -> decode -> trả về mảng PHP
     * 4. Extract dữ liệu products từ response structure
     */
    try {
        $graphql = new GraphQLService($apiEndpoint);

        // Gọi API lấy categories (Query 1)
        $catResponse = $graphql->getCategories();
        $categories  = $catResponse['data']['categories']['data'] ?? [];

        // Gọi API lấy 5 sản phẩm mới nhất (Query 2)
        $prodResponse = $graphql->getLatestProducts(5);
        $products     = $prodResponse['data']['products']['data'] ?? [];

    } catch (\Exception $e) {
        $apiError = $e->getMessage();
    }
} else {
    /**
     * Sử dụng MockDataProvider khi chưa có Bagisto API
     * Trả về dữ liệu mẫu có cùng cấu trúc với response thực
     */
    $mock       = new MockDataProvider();
    $categories = $mock->getCategories()['data']['categories']['data'];
    $products   = $mock->getLatestProducts()['data']['products']['data'];
}

// ============================================================
// BƯỚC 2: Hàm tiện ích
// ============================================================

/**
 * Định dạng giá tiền theo chuẩn VND
 * 
 * @param string|float $price - Giá gốc (đơn vị đồng)
 * @return string - Chuỗi giá đã định dạng, VD: "25.990.000 ₫"
 */
function formatPrice($price): string
{
    return number_format((float)$price, 0, ',', '.') . ' ₫';
}

/**
 * Rút gọn chuỗi mô tả nếu quá dài
 * 
 * @param string $text  - Chuỗi gốc
 * @param int    $limit - Số ký tự tối đa
 * @return string
 */
function truncate(string $text, int $limit = 120): string
{
    if (mb_strlen($text) <= $limit) return $text;
    return mb_substr($text, 0, $limit) . '…';
}

// ============================================================
// BƯỚC 3: Render HTML
// ============================================================
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bagisto Headless – <?= htmlspecialchars($studentName) ?></title>

    <!-- Google Fonts: Sử dụng Syne (display) + DM Sans (body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        /* ======================================================
         * DESIGN SYSTEM - CSS Variables
         * ====================================================== */
        :root {
            --bg:        #0a0a0f;
            --bg2:       #111118;
            --surface:   #16161f;
            --border:    #2a2a3a;
            --accent:    #6c63ff;
            --accent2:   #ff6584;
            --accent3:   #43e97b;
            --text:      #e8e8f0;
            --muted:     #7a7a9a;
            --card-bg:   #131320;
            --radius:    16px;
            --radius-sm: 8px;
            --font-head: 'Syne', sans-serif;
            --font-body: 'DM Sans', sans-serif;
            --shadow:    0 8px 32px rgba(108,99,255,0.15);
            --glow:      0 0 40px rgba(108,99,255,0.25);
        }

        /* ======================================================
         * RESET & BASE
         * ====================================================== */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { scroll-behavior: smooth; }

        body {
            font-family: var(--font-body);
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Background grid pattern */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(108,99,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(108,99,255,0.03) 1px, transparent 1px);
            background-size: 60px 60px;
            pointer-events: none;
            z-index: 0;
        }

        /* ======================================================
         * HEADER - Thông tin sinh viên (yêu cầu bài làm)
         * ====================================================== */
        .site-header {
            position: relative;
            z-index: 10;
            padding: 0;
            background: linear-gradient(135deg, #0d0d1a 0%, #16082a 50%, #0a1628 100%);
            border-bottom: 1px solid var(--border);
            overflow: hidden;
        }

        /* Decorative glow blobs */
        .site-header::before {
            content: '';
            position: absolute;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(108,99,255,0.2) 0%, transparent 70%);
            top: -200px; left: -100px;
            pointer-events: none;
        }
        .site-header::after {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(255,101,132,0.15) 0%, transparent 70%);
            top: -100px; right: 100px;
            pointer-events: none;
        }

        .header-inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 48px 32px 40px;
            position: relative;
            z-index: 1;
        }

        /* Badge phía trên */
        .header-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(108,99,255,0.15);
            border: 1px solid rgba(108,99,255,0.3);
            border-radius: 100px;
            padding: 6px 16px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 20px;
        }
        .header-badge .dot {
            width: 6px; height: 6px;
            background: var(--accent3);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        /* Tên sinh viên - nổi bật nhất */
        .student-name {
            font-family: var(--font-head);
            font-size: clamp(2.5rem, 6vw, 4.5rem);
            font-weight: 800;
            line-height: 1.05;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #fff 0%, #b0aaff 40%, #ff6584 80%, #ffda77 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 12px;
        }

        /* MSSV */
        .student-id {
            font-family: var(--font-head);
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--muted);
            letter-spacing: 0.05em;
        }
        .student-id span {
            color: var(--accent3);
            font-weight: 700;
        }

        /* Stats row */
        .header-stats {
            display: flex;
            gap: 32px;
            margin-top: 32px;
            flex-wrap: wrap;
        }
        .stat-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .stat-value {
            font-family: var(--font-head);
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text);
        }
        .stat-label {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .stat-item:nth-child(1) .stat-value { color: var(--accent); }
        .stat-item:nth-child(2) .stat-value { color: var(--accent2); }
        .stat-item:nth-child(3) .stat-value { color: var(--accent3); }

        /* Nav tabs */
        .header-nav {
            margin-top: 36px;
            display: flex;
            gap: 4px;
            border-bottom: 1px solid var(--border);
        }
        .nav-tab {
            padding: 10px 20px;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--muted);
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
            transition: all 0.2s;
            user-select: none;
        }
        .nav-tab.active {
            color: var(--accent);
            border-bottom-color: var(--accent);
        }
        .nav-tab:hover:not(.active) { color: var(--text); }

        /* ======================================================
         * MAIN CONTENT LAYOUT
         * ====================================================== */
        .main-content {
            max-width: 1280px;
            margin: 0 auto;
            padding: 48px 32px;
            position: relative;
            z-index: 1;
        }

        /* Section title */
        .section-header {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            margin-bottom: 32px;
        }
        .section-title {
            font-family: var(--font-head);
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.01em;
        }
        .section-title .accent { color: var(--accent); }
        .section-count {
            font-size: 0.875rem;
            color: var(--muted);
        }

        /* Tab panels */
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        /* ======================================================
         * API STATUS BANNER
         * ====================================================== */
        .api-banner {
            background: rgba(67,233,123,0.08);
            border: 1px solid rgba(67,233,123,0.2);
            border-radius: var(--radius-sm);
            padding: 14px 20px;
            margin-bottom: 32px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.875rem;
        }
        .api-banner.mock {
            background: rgba(255,218,119,0.06);
            border-color: rgba(255,218,119,0.2);
            color: #ffda77;
        }
        .api-banner.error {
            background: rgba(255,101,132,0.08);
            border-color: rgba(255,101,132,0.25);
            color: var(--accent2);
        }
        .api-icon { font-size: 1rem; }

        /* ======================================================
         * PRODUCT CARDS GRID
         * ====================================================== */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
        }

        .product-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
            cursor: pointer;
            animation: fadeUp 0.5s ease both;
        }
        .product-card:nth-child(1) { animation-delay: 0.05s; }
        .product-card:nth-child(2) { animation-delay: 0.10s; }
        .product-card:nth-child(3) { animation-delay: 0.15s; }
        .product-card:nth-child(4) { animation-delay: 0.20s; }
        .product-card:nth-child(5) { animation-delay: 0.25s; }

        .product-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow), 0 0 0 1px rgba(108,99,255,0.3);
            border-color: rgba(108,99,255,0.4);
        }

        /* Product image placeholder */
        .card-img {
            width: 100%;
            aspect-ratio: 16/9;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            position: relative;
            overflow: hidden;
        }
        .card-img::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(108,99,255,0.1), rgba(255,101,132,0.05));
        }
        .card-img-icon { position: relative; z-index: 1; }

        /* ID badge on card */
        .card-id-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 100px;
            padding: 3px 10px;
            font-size: 10px;
            font-weight: 600;
            color: var(--muted);
            z-index: 2;
        }

        /* Card body */
        .card-body { padding: 20px; }

        .card-name {
            font-family: var(--font-head);
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 8px;
            color: var(--text);
            /* Highlight tên SV trong tên sản phẩm */
        }
        .card-name .highlight {
            color: var(--accent);
        }

        .card-desc {
            font-size: 0.8rem;
            color: var(--muted);
            line-height: 1.55;
            margin-bottom: 16px;
        }

        .card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 16px;
            border-top: 1px solid var(--border);
        }

        /* Giá sản phẩm - nổi bật */
        .card-price {
            font-family: var(--font-head);
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--accent3);
        }

        /* Nút Chi tiết */
        .btn-detail {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, var(--accent), #9b8fff);
            color: #fff;
            border: none;
            border-radius: var(--radius-sm);
            padding: 8px 16px;
            font-family: var(--font-body);
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .btn-detail:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 16px rgba(108,99,255,0.4);
        }
        .btn-detail svg {
            width: 14px; height: 14px;
            transition: transform 0.2s;
        }
        .btn-detail:hover svg { transform: translateX(3px); }

        /* ======================================================
         * CATEGORIES TABLE
         * ====================================================== */
        .categories-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        .categories-table th {
            text-align: left;
            padding: 12px 20px;
            font-family: var(--font-head);
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--muted);
            border-bottom: 1px solid var(--border);
        }
        .categories-table td {
            padding: 14px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            color: var(--text);
        }
        .categories-table tr:hover td { background: rgba(108,99,255,0.04); }
        .cat-id {
            font-family: monospace;
            color: var(--accent);
            font-weight: 600;
        }
        .cat-slug {
            font-family: monospace;
            font-size: 0.8rem;
            color: var(--muted);
            background: rgba(255,255,255,0.04);
            padding: 2px 8px;
            border-radius: 4px;
        }

        /* ======================================================
         * GRAPHQL QUERY CODE PANEL
         * ====================================================== */
        .query-panel {
            background: #0d0d14;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            margin-bottom: 24px;
        }
        .query-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 20px;
            background: rgba(108,99,255,0.08);
            border-bottom: 1px solid var(--border);
        }
        .query-label {
            font-family: var(--font-head);
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--accent);
        }
        .copy-btn {
            background: rgba(108,99,255,0.15);
            border: 1px solid rgba(108,99,255,0.3);
            border-radius: 6px;
            padding: 4px 12px;
            font-size: 0.75rem;
            color: var(--accent);
            cursor: pointer;
            transition: all 0.2s;
        }
        .copy-btn:hover { background: rgba(108,99,255,0.3); }
        .query-code {
            padding: 20px;
            font-family: 'Courier New', monospace;
            font-size: 0.82rem;
            line-height: 1.7;
            color: #c9d1d9;
            overflow-x: auto;
            white-space: pre;
        }
        /* Syntax highlight colors */
        .kw  { color: #ff7b72; }   /* keywords: query, mutation */
        .fn  { color: #d2a8ff; }   /* function names */
        .str { color: #a5d6ff; }   /* strings */
        .cmt { color: #6e7681; font-style: italic; } /* comments */
        .fld { color: #79c0ff; }   /* fields */

        /* ======================================================
         * MODAL - Chi tiết sản phẩm
         * ====================================================== */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(8px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .modal-overlay.open { display: flex; }
        .modal {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 32px;
            max-width: 560px;
            width: 100%;
            box-shadow: var(--glow);
            animation: scaleIn 0.25s ease;
        }
        .modal-close {
            float: right;
            background: rgba(255,255,255,0.06);
            border: none;
            color: var(--muted);
            width: 32px; height: 32px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.2s;
        }
        .modal-close:hover { background: rgba(255,101,132,0.2); color: var(--accent2); }
        .modal h3 {
            font-family: var(--font-head);
            font-size: 1.3rem;
            font-weight: 700;
            margin: 16px 0 12px;
        }
        .modal-price {
            font-family: var(--font-head);
            font-size: 2rem;
            font-weight: 800;
            color: var(--accent3);
            margin-bottom: 16px;
        }
        .modal-desc {
            color: var(--muted);
            font-size: 0.9rem;
            line-height: 1.7;
            margin-bottom: 20px;
        }
        .modal-meta {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .meta-chip {
            background: rgba(108,99,255,0.1);
            border: 1px solid rgba(108,99,255,0.2);
            border-radius: 100px;
            padding: 4px 12px;
            font-size: 0.75rem;
            color: var(--accent);
        }

        /* ======================================================
         * ANIMATIONS
         * ====================================================== */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.93); }
            to   { opacity: 1; transform: scale(1); }
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.4; }
        }

        /* ======================================================
         * RESPONSIVE
         * ====================================================== */
        @media (max-width: 640px) {
            .header-inner  { padding: 32px 20px 28px; }
            .main-content  { padding: 32px 20px; }
            .header-stats  { gap: 20px; }
            .products-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- ============================================================
     HEADER: Hiển thị họ tên, MSSV (yêu cầu bài làm)
     ============================================================ -->
<header class="site-header">
    <div class="header-inner">

        <!-- Badge trạng thái API -->
        <div class="header-badge">
            <span class="dot"></span>
            Bagisto Headless Commerce · GraphQL API
        </div>

        <!-- Tên sinh viên - màu sắc nổi bật theo yêu cầu -->
        <h1 class="student-name"><?= htmlspecialchars($studentName) ?></h1>

        <!-- MSSV -->
        <p class="student-id">Mã số sinh viên: <span><?= htmlspecialchars($studentId) ?></span></p>

        <!-- Thống kê nhanh -->
        <div class="header-stats">
            <div class="stat-item">
                <span class="stat-value"><?= count($products) ?></span>
                <span class="stat-label">Sản phẩm</span>
            </div>
            <div class="stat-item">
                <span class="stat-value"><?= count($categories) ?></span>
                <span class="stat-label">Danh mục</span>
            </div>
            <div class="stat-item">
                <span class="stat-value">GraphQL</span>
                <span class="stat-label">API Protocol</span>
            </div>
        </div>

        <!-- Navigation tabs -->
        <nav class="header-nav">
            <div class="nav-tab active" onclick="switchTab('products')">🛍 Sản phẩm</div>
            <div class="nav-tab" onclick="switchTab('categories')">📂 Danh mục</div>
            <div class="nav-tab" onclick="switchTab('queries')">⚡ GraphQL Queries</div>
        </nav>
    </div>
</header>


<!-- ============================================================
     MAIN CONTENT
     ============================================================ -->
<main class="main-content">

    <!-- API Status Banner -->
    <?php if ($apiError): ?>
    <div class="api-banner error">
        <span class="api-icon">⚠️</span>
        <span>Lỗi kết nối API: <?= htmlspecialchars($apiError) ?> — Đang hiển thị dữ liệu mẫu.</span>
    </div>
    <?php elseif (!$useRealApi): ?>
    <div class="api-banner mock">
        <span class="api-icon">🔸</span>
        <span><strong>Chế độ Demo:</strong> Hiển thị dữ liệu mẫu. Đặt <code>$useRealApi = true</code> và chạy Bagisto để kết nối API thực.</span>
    </div>
    <?php else: ?>
    <div class="api-banner">
        <span class="api-icon">✅</span>
        <span>Kết nối API thành công · Endpoint: <code><?= htmlspecialchars($apiEndpoint) ?></code></span>
    </div>
    <?php endif; ?>


    <!-- ==========================================================
         TAB 1: DANH SÁCH SẢN PHẨM (Query 2)
         ========================================================== -->
    <div id="tab-products" class="tab-panel active">

        <div class="section-header">
            <h2 class="section-title">
                <span class="accent">05</span> Sản phẩm mới nhất
            </h2>
            <span class="section-count">Query 2 · sortBy: created_at DESC</span>
        </div>

        <?php if (empty($products)): ?>
            <p style="color: var(--muted); text-align:center; padding: 60px 0;">
                Không có sản phẩm nào. Hãy thêm sản phẩm vào Bagisto Admin Panel.
            </p>
        <?php else: ?>
        <div class="products-grid">
            <?php
            // Icons đại diện cho từng loại sản phẩm
            $icons = ['💻', '⌨️', '🖱️', '🖥️', '🎧', '📷', '🎮', '📱'];
            foreach ($products as $i => $product):
                $icon = $icons[$i % count($icons)];

                // Highlight tên sinh viên trong tên sản phẩm
                $displayName = htmlspecialchars($product['name']);
                $svName = explode(' ', $studentName);
                $lastName = end($svName); // Lấy tên (chữ cuối)
                if ($lastName) {
                    $displayName = str_replace(
                        htmlspecialchars($lastName),
                        '<span class="highlight">' . htmlspecialchars($lastName) . '</span>',
                        $displayName
                    );
                }
            ?>
            <article class="product-card"
                     onclick="openModal(
                        '<?= htmlspecialchars(addslashes($product['name'])) ?>',
                        '<?= htmlspecialchars(addslashes(formatPrice($product['price']))) ?>',
                        '<?= htmlspecialchars(addslashes($product['description'])) ?>',
                        '<?= htmlspecialchars(addslashes($product['url_key'])) ?>',
                        '<?= htmlspecialchars(addslashes($product['id'])) ?>'
                     )">

                <!-- Ảnh / Placeholder -->
                <div class="card-img">
                    <span class="card-img-icon"><?= $icon ?></span>
                    <div class="card-id-badge">#<?= htmlspecialchars($product['id']) ?></div>
                </div>

                <!-- Nội dung -->
                <div class="card-body">
                    <h3 class="card-name"><?= $displayName ?></h3>
                    <p class="card-desc"><?= htmlspecialchars(truncate($product['description'])) ?></p>

                    <div class="card-footer">
                        <!-- Giá đã định dạng tiền tệ VND -->
                        <span class="card-price"><?= formatPrice($product['price']) ?></span>

                        <!-- Nút Chi tiết (yêu cầu bài làm) -->
                        <button class="btn-detail" onclick="event.stopPropagation()">
                            Chi tiết
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>


    <!-- ==========================================================
         TAB 2: CATEGORIES (Query 1)
         ========================================================== -->
    <div id="tab-categories" class="tab-panel">

        <div class="section-header">
            <h2 class="section-title">Danh sách <span class="accent">Categories</span></h2>
            <span class="section-count">Query 1 · id, name, slug</span>
        </div>

        <table class="categories-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên danh mục</th>
                    <th>Slug</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><span class="cat-id"><?= htmlspecialchars($cat['id']) ?></span></td>
                    <td><?= htmlspecialchars($cat['name']) ?></td>
                    <td><span class="cat-slug"><?= htmlspecialchars($cat['slug']) ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>


    <!-- ==========================================================
         TAB 3: GRAPHQL QUERIES CODE
         ========================================================== -->
    <div id="tab-queries" class="tab-panel">

        <div class="section-header">
            <h2 class="section-title">GraphQL <span class="accent">Queries</span></h2>
            <span class="section-count">Playground · /graphql</span>
        </div>

        <!-- Query 1 -->
        <div class="query-panel">
            <div class="query-header">
                <span class="query-label">Query 1 — Danh sách Categories</span>
                <button class="copy-btn" onclick="copyQuery('q1')">Copy</button>
            </div>
            <pre class="query-code" id="q1"><span class="kw">query</span> <span class="fn">GetCategories</span> {
  <span class="fld">categories</span> {
    <span class="fld">data</span> {
      <span class="fld">id</span>       <span class="cmt"># ID danh mục</span>
      <span class="fld">name</span>     <span class="cmt"># Tên hiển thị</span>
      <span class="fld">slug</span>     <span class="cmt"># URL-friendly identifier</span>
    }
  }
}</pre>
        </div>

        <!-- Query 2 -->
        <div class="query-panel">
            <div class="query-header">
                <span class="query-label">Query 2 — 05 sản phẩm mới nhất</span>
                <button class="copy-btn" onclick="copyQuery('q2')">Copy</button>
            </div>
            <pre class="query-code" id="q2"><span class="kw">query</span> <span class="fn">GetLatestProducts</span> {
  <span class="fld">products</span>(
    first: <span class="str">5</span>               <span class="cmt"># Giới hạn 5 sản phẩm</span>
    page: <span class="str">1</span>
    sortBy: <span class="str">"created_at"</span>   <span class="cmt"># Sắp xếp theo ngày tạo</span>
    sortOrder: <span class="str">"DESC"</span>     <span class="cmt"># Mới nhất trước</span>
  ) {
    <span class="fld">data</span> {
      <span class="fld">id</span>
      <span class="fld">name</span>
      <span class="fld">price</span>
      <span class="fld">description</span>
      <span class="fld">url_key</span>
    }
    <span class="fld">paginatorInfo</span> {
      <span class="fld">count</span>
      <span class="fld">total</span>
    }
  }
}</pre>
        </div>

        <!-- Query 3 -->
        <div class="query-panel">
            <div class="query-header">
                <span class="query-label">Query 3 — Lọc sản phẩm theo tên SV (Nâng cao)</span>
                <button class="copy-btn" onclick="copyQuery('q3')">Copy</button>
            </div>
            <pre class="query-code" id="q3"><span class="kw">query</span> <span class="fn">GetStudentProducts</span>(<span class="str">$name</span>: String) {
  <span class="fld">products</span>(
    filters: {
      name: <span class="str">$name</span>           <span class="cmt"># Filter theo tên chứa chuỗi</span>
    }
  ) {
    <span class="fld">data</span> {
      <span class="fld">id</span>
      <span class="fld">name</span>
      <span class="fld">price</span>
      <span class="fld">description</span>
      <span class="fld">url_key</span>
    }
    <span class="fld">paginatorInfo</span> {
      <span class="fld">count</span>
      <span class="fld">total</span>
    }
  }
}

<span class="cmt"># Variables (JSON):</span>
<span class="cmt"># {</span>
<span class="cmt">#   "name": "<?= htmlspecialchars($studentName) ?>"</span>
<span class="cmt"># }</span></pre>
        </div>

        <!-- Hướng dẫn console.log -->
        <div class="query-panel">
            <div class="query-header">
                <span class="query-label">Console Log — Định danh bài làm</span>
                <button class="copy-btn" onclick="copyQuery('qconsole')">Copy</button>
            </div>
            <pre class="query-code" id="qconsole"><span class="cmt">// Chạy lệnh này trong tab Console của DevTools (F12):</span>
<span class="fn">console</span>.<span class="fn">log</span>(<span class="str">"Bài làm của: <?= htmlspecialchars($studentName) ?>"</span>);</pre>
        </div>
    </div>

</main>


<!-- ============================================================
     MODAL: Chi tiết sản phẩm
     ============================================================ -->
<div class="modal-overlay" id="modal" onclick="closeModal(event)">
    <div class="modal">
        <button class="modal-close" onclick="document.getElementById('modal').classList.remove('open')">✕</button>
        <h3 id="modal-name">Tên sản phẩm</h3>
        <div class="modal-price" id="modal-price">0 ₫</div>
        <p class="modal-desc" id="modal-desc">Mô tả sản phẩm...</p>
        <div class="modal-meta">
            <span class="meta-chip" id="modal-id">ID: #1</span>
            <span class="meta-chip" id="modal-url">URL: /</span>
        </div>
    </div>
</div>


<!-- ============================================================
     JAVASCRIPT
     ============================================================ -->
<script>
/**
 * ============================================================
 * Tab Navigation
 * ============================================================
 */
function switchTab(tabName) {
    // Ẩn tất cả panels và bỏ active tất cả tabs
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));

    // Hiện panel và tab được chọn
    document.getElementById('tab-' + tabName).classList.add('active');
    event.target.classList.add('active');
}

/**
 * ============================================================
 * Modal: Hiển thị chi tiết sản phẩm
 * ============================================================
 */
function openModal(name, price, desc, urlKey, id) {
    document.getElementById('modal-name').textContent = name;
    document.getElementById('modal-price').textContent = price;
    document.getElementById('modal-desc').textContent = desc;
    document.getElementById('modal-id').textContent = 'ID: #' + id;
    document.getElementById('modal-url').textContent = 'URL: /' + urlKey;
    document.getElementById('modal').classList.add('open');
}

function closeModal(e) {
    if (e.target.id === 'modal') {
        document.getElementById('modal').classList.remove('open');
    }
}

/**
 * ============================================================
 * Copy query code to clipboard
 * ============================================================
 */
function copyQuery(id) {
    const el = document.getElementById(id);
    const text = el.innerText;
    navigator.clipboard.writeText(text).then(() => {
        const btn = event.target;
        btn.textContent = '✓ Đã copy!';
        setTimeout(() => btn.textContent = 'Copy', 2000);
    });
}

/**
 * ============================================================
 * PHẦN DEMO: Fetch API bằng JavaScript (tham khảo)
 * ============================================================
 * 
 * Đây là ví dụ cách gọi Bagisto GraphQL API bằng Fetch API thuần
 * (thay thế cho PHP cURL khi dùng client-side JavaScript).
 * 
 * Trong bài này, logic gọi API chính được xử lý phía PHP (server-side)
 * thông qua class GraphQLService, nhưng đoạn code dưới minh họa
 * cách làm tương đương ở phía client.
 */
async function fetchProductsFromAPI() {
    // Endpoint GraphQL của Bagisto
    const GRAPHQL_URL = 'http://localhost:8000/graphql';

    // GraphQL query lấy 5 sản phẩm mới nhất
    const query = `
        query GetLatestProducts {
            products(first: 5, sortBy: "created_at", sortOrder: "DESC") {
                data {
                    id
                    name
                    price
                    description
                    url_key
                }
            }
        }
    `;

    try {
        // Gửi POST request đến GraphQL endpoint
        // Content-Type phải là application/json theo chuẩn GraphQL over HTTP
        const response = await fetch(GRAPHQL_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ query })  // Serialize query thành JSON
        });

        // Parse JSON response
        const data = await response.json();

        // Truy xuất dữ liệu từ GraphQL response structure
        // Response luôn có dạng: { data: { [queryName]: { ... } } }
        const products = data?.data?.products?.data ?? [];

        console.log('[Bagisto API] Lấy được', products.length, 'sản phẩm:', products);
        return products;

    } catch (error) {
        console.error('[Bagisto API] Lỗi kết nối:', error.message);
        return [];
    }
}

// Chạy console.log định danh ngay khi trang load (yêu cầu bài làm)
console.log("Bài làm của: <?= htmlspecialchars($studentName) ?>");
console.log("MSSV: <?= htmlspecialchars($studentId) ?>");
console.log("=== Bagisto Headless Commerce Frontend ===");
</script>

</body>
</html>
