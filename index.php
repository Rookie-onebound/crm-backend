<?php
/**
 * Nexus CRM — PHP REST API 入口
 *
 * 启动方式: php -S localhost:8080 index.php
 * 或通过 Apache/Nginx 重写到此文件
 */

// ============= 自动加载 =============
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/DB.php';
require_once __DIR__ . '/models/Customer.php';
require_once __DIR__ . '/models/Quote.php';
require_once __DIR__ . '/models/ApiProduct.php';
require_once __DIR__ . '/controllers/CustomerController.php';
require_once __DIR__ . '/controllers/QuoteController.php';
require_once __DIR__ . '/controllers/ExportController.php';
require_once __DIR__ . '/services/ExcelExportService.php';

// 如有 Composer autoload（PhpSpreadsheet）
$autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

// ============= CORS 头 =============
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Expose-Headers: Content-Type, Content-Disposition');
header('Access-Control-Max-Age: 86400');

// 预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ============= 路由 =============
$method = $_SERVER['REQUEST_METHOD'];
$path   = rtrim(strtok($_SERVER['REQUEST_URI'] ?? '/', '?'), '/');
$path   = $path ?: '/';

// 从路径中提取 ID
function extractId(string $path, string $prefix): ?int
{
    $pattern = '#^' . preg_quote($prefix, '#') . '/(\d+)$#';
    if (preg_match($pattern, $path, $m)) {
        return (int) $m[1];
    }
    return null;
}

try {
    // ============= 客户路由 =============
    if ($path === '/api/customers' && $method === 'GET') {
        (new CustomerController())->index();

    } elseif ($path === '/api/customers' && $method === 'POST') {
        (new CustomerController())->store();

    } elseif (($id = extractId($path, '/api/customers')) !== null && $method === 'GET') {
        (new CustomerController())->show($id);

    } elseif (($id = extractId($path, '/api/customers')) !== null && $method === 'PUT') {
        (new CustomerController())->update($id);

    } elseif (($id = extractId($path, '/api/customers')) !== null && $method === 'DELETE') {
        (new CustomerController())->destroy($id);

    // ============= 报价路由 =============
    } elseif (preg_match('#^/api/quotes/customer/(\d+)$#', $path, $m) && $method === 'GET') {
        (new QuoteController())->byCustomer((int) $m[1]);

    } elseif ($path === '/api/quotes' && $method === 'POST') {
        (new QuoteController())->store();

    } elseif (($id = extractId($path, '/api/quotes')) !== null && $method === 'GET') {
        (new QuoteController())->show($id);

    } elseif (($id = extractId($path, '/api/quotes')) !== null && $method === 'PUT') {
        (new QuoteController())->update($id);

    } elseif (($id = extractId($path, '/api/quotes')) !== null && $method === 'DELETE') {
        (new QuoteController())->destroy($id);

    // ============= 导出路由 =============
    } elseif ($path === '/api/export/customers' && $method === 'GET') {
        (new ExportController())->customers();

    } elseif ($path === '/api/export/prices' && $method === 'GET') {
        (new ExportController())->prices();

    // ============= 健康检查 =============
    } elseif ($path === '/api/health' || $path === '/api') {
        Response::json([
            'status'  => 'ok',
            'service' => 'Nexus CRM API',
            'version' => '1.0.0',
            'time'    => date('Y-m-d H:i:s'),
        ]);

    // ============= 404 =============
    } else {
        Response::error('Not Found: ' . $method . ' ' . $path, 404);
    }

} catch (\PDOException $e) {
    Response::error('数据库错误: ' . $e->getMessage(), 500);
} catch (\Throwable $e) {
    Response::error('服务器错误: ' . $e->getMessage(), 500);
}
