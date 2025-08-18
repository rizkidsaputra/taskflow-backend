<?php
require_once __DIR__ . '/../../config/cors.php';

$hdr = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (stripos($hdr, 'Bearer ') === 0) {
  $token = substr($hdr, 7);
  $stmt = $pdo->prepare('DELETE FROM auth_tokens WHERE token = ?');
  $stmt->execute([$token]);
}
json_ok([], 'Logged out');
?>
