<?php
/**
 * Endpoint untuk akses publik - list tugas publik dalam proyek
 * GET /api/public/tasks.php?project_id={id} - List tugas publik dalam proyek
 * GET /api/public/tasks.php?id={id} - Detail tugas publik
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
    
    if (isset($_GET['id']) && !isset($_GET['project_id'])) {
        $task_id = $_GET['id'];
        
        if (!is_numeric($task_id)) {
            sendError("ID tugas tidak valid", 400);
        }
        
        // Get task detail with project info
        $stmt = $db->prepare("
            SELECT t.id, t.title, t.description, t.status, t.due_date, t.created_at,
                   u.username as assigned_to_name, p.name as project_name, p.is_public as project_public
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
        
        sendSuccess($task, "Berhasil mengambil detail tugas publik");
        
    } else if (isset($_GET['project_id'])) {
        $project_id = $_GET['project_id'];
        
        if (!is_numeric($project_id)) {
            sendError("ID proyek tidak valid", 400);
        }
        
        // Cek apakah proyek publik
        $stmt = $db->prepare("SELECT is_public FROM projects WHERE id = ?");
        $stmt->execute([$project_id]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$project) {
            sendError("Proyek tidak ditemukan", 404);
        }
        
        if (!$project['is_public']) {
            sendError("Proyek ini tidak publik", 403);
        }
        
        // Ambil tugas publik dalam proyek
        $stmt = $db->prepare("
            SELECT t.id, t.title, t.description, t.status, t.due_date, t.created_at,
                   u.username as assigned_to_name
            FROM tasks t
            LEFT JOIN users u ON t.assigned_to = u.id
            WHERE t.project_id = ? AND t.is_public = true
            ORDER BY t.created_at DESC
        ");
        $stmt->execute([$project_id]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        sendSuccess($tasks, "Berhasil mengambil daftar tugas publik");
        
    } else {
        sendError("Parameter project_id atau id harus disediakan", 400);
    }
    
} catch (Exception $e) {
    sendError("Terjadi kesalahan server: " . $e->getMessage(), 500);
}
?>
