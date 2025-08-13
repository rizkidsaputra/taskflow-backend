<?php
/**
 * Endpoint untuk akses publik - list proyek publik
 * GET /api/public/projects
 */

require_once '../../config/database.php';
require_once '../../config/cors.php';
require_once '../../utils/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError("Method not allowed", 405);
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Ambil proyek publik saja
    $stmt = $db->prepare("
        SELECT p.id, p.name, p.description, p.due_date, p.created_at,
               u.username as created_by_name,
               COUNT(t.id) as task_count
        FROM projects p
        LEFT JOIN users u ON p.created_by = u.id
        LEFT JOIN tasks t ON p.id = t.project_id AND t.is_public = true
        WHERE p.is_public = true
        GROUP BY p.id
        ORDER BY p.created_at DESC
    ");
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendSuccess($projects, "Berhasil mengambil daftar proyek publik");
    
} catch (Exception $e) {
    sendError("Terjadi kesalahan server: " . $e->getMessage(), 500);
}
?>
