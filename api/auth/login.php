<?php
/**
 * Endpoint untuk login member
 * POST /api/login
 */

require_once '../../config/database.php';
require_once '../../config/cors.php';
require_once '../../utils/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError("Method not allowed", 405);
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $input = getJsonInput();
    
    // Validasi input
    if (empty($input['username']) || empty($input['password'])) {
        sendError("Username dan password harus diisi", 400);
    }
    
    // Cari user berdasarkan username
    $stmt = $db->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
    $stmt->execute([$input['username']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !password_verify($input['password'], $user['password_hash'])) {
        sendError("Username atau password salah", 401);
    }
    
    // Mulai session dan simpan data user
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    
    // Response sukses tanpa password hash
    unset($user['password_hash']);
    sendSuccess($user, "Login berhasil");
    
} catch (Exception $e) {
    sendError("Terjadi kesalahan server: " . $e->getMessage(), 500);
}
?>
