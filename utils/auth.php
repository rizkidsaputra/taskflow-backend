<?php
/**
 * Utility untuk autentikasi dan validasi token
 */

/**
 * Memulai session dan mengecek apakah user sudah login
 */
function checkAuth() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(["message" => "Unauthorized. Silakan login terlebih dahulu."]);
        exit();
    }
    
    return $_SESSION['user_id'];
}

/**
 * Mendapatkan informasi user yang sedang login
 */
function getCurrentUser($db) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    $stmt = $db->prepare("SELECT id, username, role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Mengecek apakah user adalah member dari project tertentu
 */
function checkProjectMember($db, $user_id, $project_id) {
    $stmt = $db->prepare("
        SELECT 1 FROM project_members 
        WHERE user_id = ? AND project_id = ?
        UNION
        SELECT 1 FROM projects 
        WHERE id = ? AND created_by = ?
    ");
    $stmt->execute([$user_id, $project_id, $project_id, $user_id]);
    return $stmt->fetch() !== false;
}
?>
