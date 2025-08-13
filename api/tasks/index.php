<?php
/**
 * Endpoint untuk mengelola tugas dalam proyek
 * GET /api/tasks/index.php?project_id={id} - list tugas proyek
 * POST /api/tasks/index.php - tambah tugas
 * GET /api/tasks/index.php?id={id} - detail tugas
 * PUT /api/tasks/index.php - edit tugas
 * DELETE /api/tasks/index.php - hapus tugas
 */

require_once '../../config/database.php';
require_once '../../config/cors.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $user_id = checkAuth();
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['project_id'])) {
            // List tugas dalam proyek
            $project_id = $_GET['project_id'];
            
            if (!is_numeric($project_id)) {
                sendError("ID proyek tidak valid", 400);
            }
            
            // Cek apakah user adalah member dari proyek ini
            if (!checkProjectMember($db, $user_id, $project_id)) {
                sendError("Anda tidak memiliki akses ke proyek ini", 403);
            }
            
            $stmt = $db->prepare("
                SELECT t.*, u.username as assigned_to_name
                FROM tasks t
                LEFT JOIN users u ON t.assigned_to = u.id
                WHERE t.project_id = ?
                ORDER BY t.created_at DESC
            ");
            $stmt->execute([$project_id]);
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            sendSuccess($tasks, "Berhasil mengambil daftar tugas");
            
        } elseif (isset($_GET['id'])) {
            // Detail tugas
            $task_id = $_GET['id'];
            
            if (!is_numeric($task_id)) {
                sendError("ID tugas tidak valid", 400);
            }
            
            $stmt = $db->prepare("
                SELECT t.*, u.username as assigned_to_name, p.name as project_name
                FROM tasks t
                LEFT JOIN users u ON t.assigned_to = u.id
                LEFT JOIN projects p ON t.project_id = p.id
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
            
            sendSuccess($task, "Berhasil mengambil detail tugas");
        } else {
            sendError("Parameter project_id atau id harus disertakan", 400);
        }
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Tambah tugas baru
        $input = getJsonInput();
        
        if (empty($input['project_id']) || !is_numeric($input['project_id'])) {
            sendError("ID proyek tidak valid", 400);
        }
        
        if (empty($input['title'])) {
            sendError("Judul tugas harus diisi", 400);
        }
        
        $project_id = $input['project_id'];
        
        // Cek apakah user adalah member dari proyek ini
        if (!checkProjectMember($db, $user_id, $project_id)) {
            sendError("Anda tidak memiliki akses ke proyek ini", 403);
        }
        
        // Validasi assigned_to jika ada
        $assigned_to = null;
        if (!empty($input['assigned_to'])) {
            $stmt = $db->prepare("
                SELECT u.id FROM users u
                JOIN project_members pm ON u.id = pm.user_id
                WHERE u.id = ? AND pm.project_id = ?
            ");
            $stmt->execute([$input['assigned_to'], $project_id]);
            if ($stmt->fetch()) {
                $assigned_to = $input['assigned_to'];
            } else {
                sendError("User yang ditugaskan bukan anggota proyek", 400);
            }
        }
        
        $stmt = $db->prepare("
            INSERT INTO tasks (project_id, title, description, status, assigned_to, is_public, due_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $status = in_array($input['status'] ?? '', ['todo', 'in_progress', 'done']) ? $input['status'] : 'todo';
        $due_date = !empty($input['deadline']) ? $input['deadline'] : null;
        $is_public = isset($input['is_public']) ? (bool)$input['is_public'] : false;
        
        $stmt->execute([
            $project_id,
            $input['title'],
            $input['description'] ?? '',
            $status,
            $assigned_to,
            $is_public,
            $due_date
        ]);
        
        $task_id = $db->lastInsertId();
        sendSuccess(['id' => $task_id], "Tugas berhasil dibuat", 201);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // Edit tugas
        $input = getJsonInput();
        
        if (empty($input['id']) || !is_numeric($input['id'])) {
            sendError("ID tugas tidak valid", 400);
        }
        
        $task_id = $input['id'];
        
        // Cek apakah tugas ada dan user memiliki akses
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
        
        // Update tugas
        $stmt = $db->prepare("
            UPDATE tasks 
            SET title = ?, description = ?, status = ?, assigned_to = ?, is_public = ?, due_date = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        $status = in_array($input['status'] ?? '', ['todo', 'in_progress', 'done']) ? $input['status'] : $task['status'];
        $assigned_to = isset($input['assigned_to']) ? $input['assigned_to'] : $task['assigned_to'];
        $is_public = isset($input['is_public']) ? (bool)$input['is_public'] : (bool)$task['is_public'];
        $due_date = isset($input['deadline']) ? $input['deadline'] : $task['due_date'];
        
        $stmt->execute([
            $input['title'] ?? $task['title'],
            $input['description'] ?? $task['description'],
            $status,
            $assigned_to,
            $is_public,
            $due_date,
            $task_id
        ]);
        
        sendSuccess(null, "Tugas berhasil diperbarui");
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Hapus tugas
        $input = getJsonInput();
        
        if (empty($input['id']) || !is_numeric($input['id'])) {
            sendError("ID tugas tidak valid", 400);
        }
        
        $task_id = $input['id'];
        
        // Cek apakah tugas ada dan user memiliki akses
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
        
        // Hanya creator proyek atau creator tugas yang bisa menghapus
        if ($task['project_creator'] != $user_id && $task['created_by'] != $user_id) {
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
