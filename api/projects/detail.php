<?php
/**
 * Endpoint untuk detail, edit, dan hapus proyek
 * GET /api/projects/{id} - detail proyek
 * PUT /api/projects/{id} - edit proyek
 * DELETE /api/projects/{id} - hapus proyek
 */

require_once '../../config/database.php';
require_once '../../config/cors.php';
require_once '../../utils/response.php';
require_once '../../utils/auth.php';

// Ambil ID proyek dari URL
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));
$project_id = end($path_parts);

if (!is_numeric($project_id)) {
    sendError("ID proyek tidak valid", 400);
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $user_id = checkAuth();
    
    // Cek apakah user adalah member dari proyek ini
    if (!checkProjectMember($db, $user_id, $project_id)) {
        sendError("Anda tidak memiliki akses ke proyek ini", 403);
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Detail proyek
        $stmt = $db->prepare("
            SELECT p.*, u.username as created_by_name
            FROM projects p
            LEFT JOIN users u ON p.created_by = u.id
            WHERE p.id = ?
        ");
        $stmt->execute([$project_id]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$project) {
            sendError("Proyek tidak ditemukan", 404);
        }
        
        // Ambil daftar member proyek
        $stmt = $db->prepare("
            SELECT u.id, u.username
            FROM users u
            JOIN project_members pm ON u.id = pm.user_id
            WHERE pm.project_id = ?
            ORDER BY u.username
        ");
        $stmt->execute([$project_id]);
        $project['members'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        sendSuccess($project, "Berhasil mengambil detail proyek");
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // Edit proyek (hanya creator yang bisa edit)
        $stmt = $db->prepare("SELECT created_by FROM projects WHERE id = ?");
        $stmt->execute([$project_id]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($project['created_by'] != $user_id) {
            sendError("Hanya pembuat proyek yang dapat mengedit", 403);
        }
        
        $input = getJsonInput();
        
        if (empty($input['name'])) {
            sendError("Nama proyek harus diisi", 400);
        }
        
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
        
        sendSuccess(null, "Proyek berhasil diperbarui");
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Hapus proyek (hanya creator yang bisa hapus)
        $stmt = $db->prepare("SELECT created_by FROM projects WHERE id = ?");
        $stmt->execute([$project_id]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$project) {
            sendError("Proyek tidak ditemukan", 404);
        }
        
        if ($project['created_by'] != $user_id) {
            sendError("Hanya pembuat proyek yang dapat menghapus", 403);
        }
        
        $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$project_id]);
        
        sendSuccess(null, "Proyek berhasil dihapus");
        
    } else {
        sendError("Method not allowed", 405);
    }
    
} catch (Exception $e) {
    sendError("Terjadi kesalahan server: " . $e->getMessage(), 500);
}
?>
