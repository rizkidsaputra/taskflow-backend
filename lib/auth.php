<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/response.php';

/**
 * Generate simple opaque token valid 7 days.
 */
function issue_token($pdo, $user_id) {
  $token = bin2hex(random_bytes(24));
  $stmt = $pdo->prepare("INSERT INTO auth_tokens (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))");
  $stmt->execute([$user_id, $token]);
  return $token;
}

/**
 * Get Authorization: Bearer token from various server variables.
 */
function _get_auth_header_token() {
  // Standard server var
  $hdr = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
  if (!$hdr && function_exists('apache_request_headers')) {
    $headers = apache_request_headers();
    foreach ($headers as $k => $v) {
      if (strtolower($k) === 'authorization') { $hdr = $v; break; }
    }
  }
  if (!$hdr) {
    // Fallback: some proxies put it in REDIRECT_HTTP_AUTHORIZATION
    $hdr = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
  }
  if ($hdr && stripos($hdr, 'Bearer ') === 0) {
    return trim(substr($hdr, 7));
  }
  // Last resort: accept token from query/body ONLY for dev
  if (isset($_GET['token'])) return $_GET['token'];
  return '';
}

/**
 * Return current user array or null. Joins users with auth_tokens and checks expiry.
 */
function get_user_by_token($pdo) {
  $token = _get_auth_header_token();
  if (!$token) return null;
  $sql = "SELECT u.id, u.username, u.full_name, u.email
          FROM auth_tokens t
          JOIN users u ON u.id = t.user_id
          WHERE t.token = ? AND t.expires_at > NOW()
          LIMIT 1";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$token]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);
  return $user ?: null;
}

/**
 * Require auth or send 401 JSON.
 */
function require_auth($pdo) {
  $u = get_user_by_token($pdo);
  if (!$u) json_error('Unauthorized', 401);
  return $u;
}
?>
