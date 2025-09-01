<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../lib/response.php';

if (function_exists('apache_request_headers')) {
    $headers = apache_request_headers();
    if (isset($headers['Authorization']) && !isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $_SERVER['HTTP_AUTHORIZATION'] = $headers['Authorization'];
    }
}

$hdr = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

if (stripos($hdr, 'Bearer ') === 0) {
    $token = substr($hdr, 7);

    $stmt = $pdo->prepare('DELETE FROM auth_tokens WHERE token = ?');
    $stmt->execute([$token]);

    if ($stmt->rowCount() > 0) {
        json_ok([], 'Logged out');
    } else {
        json_response(['success' => false, 'message' => 'Invalid token'], 401);
    }
} else {
    json_response(['success' => false, 'message' => 'Authorization header missing'], 400);
}

