<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/response.php';

function issue_token($pdo, $user_id) {
  $token = bin2hex(random_bytes(24));
  $stmt = $pdo->prepare("INSERT INTO auth_tokens (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))");
  $stmt->execute([$user_id, $token]);
  return $token;
}
  
function get_user_by_token($pdo) {
  $hdr = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
  if (stripos($hdr, 'Bearer ') !== 0) return null;
  $token = substr($hdr, 7);
  $stmt = $pdo->prepare("SELECT u.id, u.username, u.full_name FROM auth_tokens t JOIN users u ON u.id=t.user_id WHERE t.token=? AND t.expires_at > NOW() LIMIT 1");
  $stmt->execute([$token]);
  return $stmt->fetch() ?: null;
}

function require_auth($pdo) {
  $u = get_user_by_token($pdo);
  if (!$u) json_error('Unauthorized', 401);
  return $u;
}
?>
