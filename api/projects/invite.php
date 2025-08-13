<?php
/**
 * Endpoint untuk mengundang anggota ke proyek
 * POST /api/projects/{id}/invite
 */

require_once '../../config/database.php';
require_once '../../config/cors.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError("Method not allowed", 405);
}

// Ambil ID proyek dari URL
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));
$project_id = $path_parts[count($path_parts) - 2]; // ID sebelum 'invite'

if (!is_numeric($project_id)) {
    sendError("ID proyek tidak valid", 400);
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $user_id = checkAuth();
    
    // Cek apakah user adalah creator proyek
    $stmt = $db->prepare("SELECT created_by FROM projects WHERE id = ?");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$project) {
        sendError("Proyek tidak ditemukan", 404);
    }
    
    if ($project['created_by'] != $user_id) {
        sendError("Hanya pembuat proyek yang dapat mengundang anggota", 403);
    }
    
    $input = getJsonInput();
    
    if (empty($input['username'])) {
        sendError("Username harus diisi", 400);
    }
    
    // Cari user berdasarkan username
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$input['username']]);
    $invited_user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$invited_user) {
        sendError("User tidak ditemukan", 404);
    }
    
    // Cek apakah user sudah menjadi member
    $stmt = $db->prepare("SELECT 1 FROM project_members WHERE project_id = ? AND user_id = ?");
    $stmt->execute([$project_id, $invited_user['id']]);
    
    if ($stmt->fetch()) {
        sendError("User sudah menjadi anggota proyek ini", 400);
    }
    
    // Tambahkan sebagai member
    $stmt = $db->prepare("INSERT INTO project_members (project_id, user_id) VALUES (?, ?)");
    $stmt->execute([$project_id, $invited_user['id']]);
    
    sendSuccess(null, "Anggota berhasil diundang ke proyek", 201);
    
} catch (Exception $e) {
    sendError("Terjadi kesalahan server: " . $e->getMessage(), 500);
}
?>
