<?php
/**
 * Endpoint untuk detail, edit, dan hapus tugas
 * GET /api/tasks/{id} - detail tugas
 * PUT /api/tasks/{id} - edit tugas
 * DELETE /api/tasks/{id} - hapus tugas
 */

require_once '../../config/database.php';
require_once '../../config/cors.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

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
    $user_id = checkAuth();
    
    // Ambil info tugas dan cek akses
    $stmt = $db->prepare("
        SELECT t.*, p.created_by as project_creator
        FROM tasks t
        JOIN projects p ON t.project_id = p.id
        WHERE t.id = ?
    ");
    $stmt->execute([$task_id]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$task) {
        sendError("Tugas tidak ditemukan", 404);
    }
    
    // Cek apakah user adalah member dari proyek ini
    if (!checkProjectMember($db, $user_id, $task['project_id'])) {
        sendError("Anda tidak memiliki akses ke tugas ini", 403);
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Detail tugas
        $stmt = $db->prepare("
            SELECT t.*, u.username as assigned_to_name, p.name as project_name
            FROM tasks t
            LEFT JOIN users u ON t.assigned_to = u.id
            LEFT JOIN projects p ON t.project_id = p.id
            WHERE t.id = ?
        ");
        $stmt->execute([$task_id]);
        $task_detail = $stmt->fetch(PDO::FETCH_ASSOC);
        
        sendSuccess($task_detail, "Berhasil mengambil detail tugas");
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // Edit tugas
        $input = getJsonInput();
        
        if (empty($input['title'])) {
            sendError("Judul tugas harus diisi", 400);
        }
        
        // Validasi assigned_to jika ada
        $assigned_to = null;
        if (!empty($input['assigned_to'])) {
            $stmt = $db->prepare("
                SELECT u.id FROM users u
                JOIN project_members pm ON u.id = pm.user_id
                WHERE u.id = ? AND pm.project_id = ?
            ");
            $stmt->execute([$input['assigned_to'], $task['project_id']]);
            if ($stmt->fetch()) {
                $assigned_to = $input['assigned_to'];
            } else {
                sendError("User yang ditugaskan bukan anggota proyek", 400);
            }
        }
        
        $stmt = $db->prepare("
            UPDATE tasks 
            SET title = ?, description = ?, status = ?, assigned_to = ?, due_date = ?
            WHERE id = ?
        ");
        
        $status = in_array($input['status'] ?? '', ['todo', 'in_progress', 'done']) ? $input['status'] : $task['status'];
        $due_date = !empty($input['due_date']) ? $input['due_date'] : null;
        
        $stmt->execute([
            $input['title'],
            $input['description'] ?? $task['description'],
            $status,
            $assigned_to,
            $due_date,
            $task_id
        ]);
        
        sendSuccess(null, "Tugas berhasil diperbarui");
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Hapus tugas (hanya creator proyek atau assigned user yang bisa hapus)
        if ($task['project_creator'] != $user_id && $task['assigned_to'] != $user_id) {
            sendError("Anda tidak memiliki izin untuk menghapus tugas ini", 403);
        }
        
        $stmt = $db->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$task_id]);
        
        sendSuccess(null, "Tugas berhasil dihapus");
        
    } else {
        sendError("Method not allowed", 405);
    }
    
} catch (Exception $e) {
    sendError("Terjadi kesalahan server: " . $e->getMessage(), 500);
}
?>
