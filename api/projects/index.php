<?php
/**
 * Endpoint untuk mengelola proyek
 * GET /api/projects - list proyek member
 * GET /api/projects?id=1 - detail proyek
 * POST /api/projects - tambah proyek
 * POST /api/projects?action=invite - undang anggota ke proyek
 * PUT /api/projects - edit proyek
 * DELETE /api/projects - hapus proyek
 */

require_once '../../config/database.php';
require_once '../../config/cors.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $user_id = checkAuth();
        
        if (isset($_GET['id'])) {
            // Detail proyek
            $project_id = $_GET['id'];
            
            $stmt = $db->prepare("
                SELECT p.*, u.username as created_by_name,
                       COUNT(t.id) as task_count,
                       COUNT(CASE WHEN t.status = 'done' THEN 1 END) as completed_tasks
                FROM projects p
                LEFT JOIN users u ON p.created_by = u.id
                LEFT JOIN project_members pm ON p.id = pm.project_id
                LEFT JOIN tasks t ON p.id = t.project_id
                WHERE p.id = ? AND (p.created_by = ? OR pm.user_id = ?)
                GROUP BY p.id
            ");
            $stmt->execute([$project_id, $user_id, $user_id]);
            $project = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$project) {
                sendError("Proyek tidak ditemukan atau Anda tidak memiliki akses", 404);
            }
            
            // Get project members
            $stmt = $db->prepare("
                SELECT u.id, u.username, u.full_name, pm.role, pm.joined_at
                FROM project_members pm
                JOIN users u ON pm.user_id = u.id
                WHERE pm.project_id = ?
                ORDER BY pm.joined_at ASC
            ");
            $stmt->execute([$project_id]);
            $project['members'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            sendSuccess($project, "Berhasil mengambil detail proyek");
        } else {
            // List proyek member yang sedang login
            $stmt = $db->prepare("
                SELECT DISTINCT p.*, u.username as created_by_name,
                       COUNT(t.id) as task_count
                FROM projects p
                LEFT JOIN users u ON p.created_by = u.id
                LEFT JOIN project_members pm ON p.id = pm.project_id
                LEFT JOIN tasks t ON p.id = t.project_id
                WHERE p.created_by = ? OR pm.user_id = ?
                GROUP BY p.id
                ORDER BY p.created_at DESC
            ");
            $stmt->execute([$user_id, $user_id]);
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            sendSuccess($projects, "Berhasil mengambil daftar proyek");
        }
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id = checkAuth();
        
        if (isset($_GET['action']) && $_GET['action'] === 'invite') {
            // Invite member to project
            if (empty($_GET['id'])) {
                sendError("ID proyek harus diisi", 400);
            }
            
            $project_id = $_GET['id'];
            
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
            
        } else {
            // Tambah proyek baru
            $input = getJsonInput();
            
            // Validasi input
            if (empty($input['name'])) {
                sendError("Nama proyek harus diisi", 400);
            }
            
            $stmt = $db->prepare("
                INSERT INTO projects (name, description, created_by, due_date, is_public) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $due_date = !empty($input['due_date']) ? $input['due_date'] : null;
            $is_public = isset($input['is_public']) ? (bool)$input['is_public'] : false;
            
            $stmt->execute([
                $input['name'],
                $input['description'] ?? '',
                $user_id,
                $due_date,
                $is_public
            ]);
            
            $project_id = $db->lastInsertId();
            
            // Tambahkan creator sebagai member proyek
            $stmt = $db->prepare("INSERT INTO project_members (project_id, user_id) VALUES (?, ?)");
            $stmt->execute([$project_id, $user_id]);
            
            sendSuccess(['id' => $project_id], "Proyek berhasil dibuat", 201);
        }
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $user_id = checkAuth();
        $input = getJsonInput();
        
        // Validasi input
        if (empty($input['id'])) {
            sendError("ID proyek harus diisi", 400);
        }
        if (empty($input['name'])) {
            sendError("Nama proyek harus diisi", 400);
        }
        
        $project_id = $input['id'];
        
        // Cek apakah user adalah creator proyek
        $stmt = $db->prepare("SELECT created_by FROM projects WHERE id = ?");
        $stmt->execute([$project_id]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$project) {
            sendError("Proyek tidak ditemukan", 404);
        }
        
        if ($project['created_by'] != $user_id) {
            sendError("Anda tidak memiliki izin untuk mengedit proyek ini", 403);
        }
        
        // Update proyek
        $stmt = $db->prepare("
            UPDATE projects 
            SET name = ?, description = ?, due_date = ?, is_public = ?
            WHERE id = ?
        ");
        
        $due_date = !empty($input['due_date']) ? $input['due_date'] : null;
        $is_public = isset($input['is_public']) ? (bool)$input['is_public'] : false;
        
        $stmt->execute([
            $input['name'],
            $input['description'] ?? '',
            $due_date,
            $is_public,
            $project_id
        ]);
        
        sendSuccess(['id' => $project_id], "Proyek berhasil diperbarui");
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $user_id = checkAuth();
        $input = getJsonInput();
        
        // Validasi input
        if (empty($input['id'])) {
            sendError("ID proyek harus diisi", 400);
        }
        
        $project_id = $input['id'];
        
        // Cek apakah user adalah creator proyek
        $stmt = $db->prepare("SELECT created_by FROM projects WHERE id = ?");
        $stmt->execute([$project_id]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$project) {
            sendError("Proyek tidak ditemukan", 404);
        }
        
        if ($project['created_by'] != $user_id) {
            sendError("Anda tidak memiliki izin untuk menghapus proyek ini", 403);
        }
        
        // Hapus semua data terkait proyek (cascade delete)
        $db->beginTransaction();
        
        try {
            // Hapus project members
            $stmt = $db->prepare("DELETE FROM project_members WHERE project_id = ?");
            $stmt->execute([$project_id]);
            
            // Hapus tasks
            $stmt = $db->prepare("DELETE FROM tasks WHERE project_id = ?");
            $stmt->execute([$project_id]);
            
            // Hapus proyek
            $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
            $stmt->execute([$project_id]);
            
            $db->commit();
            sendSuccess(null, "Proyek berhasil dihapus");
            
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
        
    } else {
        sendError("Method not allowed", 405);
    }
    
} catch (Exception $e) {
    sendError("Terjadi kesalahan server: " . $e->getMessage(), 500);
}
?>
