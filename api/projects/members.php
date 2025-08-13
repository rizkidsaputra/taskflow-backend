<?php
/**
 * Endpoint untuk mengelola anggota proyek
 * GET /api/projects/members.php?project_id={id} - List anggota proyek
 * DELETE /api/projects/members.php - Hapus anggota dari proyek
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
        // List anggota proyek
        $project_id = $_GET['project_id'] ?? null;
        
        if (!$project_id || !is_numeric($project_id)) {
            sendError("ID proyek harus diisi dan valid", 400);
        }
        
        // Cek apakah user adalah member atau creator proyek
        $stmt = $db->prepare("
            SELECT 1 FROM projects p 
            LEFT JOIN project_members pm ON p.id = pm.project_id 
            WHERE p.id = ? AND (p.created_by = ? OR pm.user_id = ?)
        ");
        $stmt->execute([$project_id, $user_id, $user_id]);
        
        if (!$stmt->fetch()) {
            sendError("Anda tidak memiliki akses ke proyek ini", 403);
        }
        
        // Ambil daftar anggota
        $stmt = $db->prepare("
            SELECT u.id, u.username, 
                   CASE WHEN p.created_by = u.id THEN 'creator' ELSE 'member' END as role,
                   COALESCE(pm.joined_at, p.created_at) as joined_at
            FROM projects p
            LEFT JOIN project_members pm ON p.id = pm.project_id
            LEFT JOIN users u ON (pm.user_id = u.id OR p.created_by = u.id)
            WHERE p.id = ? AND u.id IS NOT NULL
            ORDER BY role DESC, joined_at ASC
        ");
        $stmt->execute([$project_id]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        sendSuccess($members, "Daftar anggota proyek berhasil diambil");
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Hapus anggota dari proyek
        $input = getJsonInput();
        
        if (empty($input['project_id']) || empty($input['user_id'])) {
            sendError("ID proyek dan ID user harus diisi", 400);
        }
        
        // Cek apakah user adalah creator proyek
        $stmt = $db->prepare("SELECT created_by FROM projects WHERE id = ?");
        $stmt->execute([$input['project_id']]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$project) {
            sendError("Proyek tidak ditemukan", 404);
        }
        
        if ($project['created_by'] != $user_id) {
            sendError("Hanya pembuat proyek yang dapat menghapus anggota", 403);
        }
        
        // Tidak bisa menghapus creator
        if ($project['created_by'] == $input['user_id']) {
            sendError("Tidak dapat menghapus pembuat proyek", 400);
        }
        
        // Hapus anggota
        $stmt = $db->prepare("DELETE FROM project_members WHERE project_id = ? AND user_id = ?");
        $stmt->execute([$input['project_id'], $input['user_id']]);
        
        if ($stmt->rowCount() > 0) {
            sendSuccess(null, "Anggota berhasil dihapus dari proyek");
        } else {
            sendError("Anggota tidak ditemukan dalam proyek", 404);
        }
        
    } else {
        sendError("Method not allowed", 405);
    }
    
} catch (Exception $e) {
    sendError("Terjadi kesalahan server: " . $e->getMessage(), 500);
}
?>
