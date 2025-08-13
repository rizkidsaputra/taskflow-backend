<?php
/**
 * Endpoint untuk akses publik - detail tugas publik
 * GET /api/public/tasks/{id}
 */

require_once '../../config/database.php';
require_once '../../config/cors.php';
require_once '../../utils/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError("Method not allowed", 405);
}

// Ambil ID tugas dari URL
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));
$task_id = end($path_parts);

if (!is_numeric($task_id)) {
    sendError("ID tugas tidak valid", 400);
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Ambil detail tugas publik
    $stmt = $db->prepare("
        SELECT t.id, t.title, t.description, t.status, t.due_date, t.created_at,
               u.username as assigned_to_name,
               p.name as project_name, p.is_public as project_is_public
        FROM tasks t
        LEFT JOIN users u ON t.assigned_to = u.id
        LEFT JOIN projects p ON t.project_id = p.id
        WHERE t.id = ? AND t.is_public = true AND p.is_public = true
    ");
    $stmt->execute([$task_id]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$task) {
        sendError("Tugas tidak ditemukan atau tidak publik", 404);
    }
    
    // Hapus info yang tidak perlu untuk publik
    unset($task['project_is_public']);
    
    sendSuccess($task, "Berhasil mengambil detail tugas publik");
    
} catch (Exception $e) {
    sendError("Terjadi kesalahan server: " . $e->getMessage(), 500);
}
?>
