<?php
/**
 * Request & Response 工具类
 */

class Request
{
    /** 获取 JSON 请求体 */
    public static function body(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    /** 获取请求方法 */
    public static function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    /** 获取 URI 路径（不含查询字符串） */
    public static function path(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = strtok($uri, '?');  // 去掉查询参数
        $uri = rtrim($uri, '/');
        return $uri ?: '/';
    }
}

class Response
{
    /** JSON 成功响应 */
    public static function json(mixed $data, int $code = 200): never
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /** JSON 错误响应 */
    public static function error(string $message, int $code = 400): never
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
