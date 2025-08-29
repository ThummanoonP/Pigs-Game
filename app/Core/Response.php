<?php
namespace Core;
class Response {
    public static function json($data, int $status = 200) {
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
