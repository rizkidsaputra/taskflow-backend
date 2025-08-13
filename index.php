<?php
/**
 * Router utama untuk API TaskFlow
 * Menangani routing ke endpoint yang sesuai
 */

require_once 'config/cors.php';

// Ambil path dari URL
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($request_uri, '/');
$path_parts = explode('/', $path);

// Routing berdasarkan path
if ($path_parts[0] === 'api') {
    
    if (count($path_parts) >= 2 && $path_parts[1] === 'login') {
        // POST /api/login
        require_once 'api/auth/login.php';
        
    } elseif (count($path_parts) >= 2 && $path_parts[1] === 'logout') {
        // POST /api/logout
        require_once 'api/auth/logout.php';
        
    } elseif (count($path_parts) >= 2 && $path_parts[1] === 'projects') {
        
        if (count($path_parts) === 2) {
            // GET/POST /api/projects
            require_once 'api/projects/index.php';
            
        } elseif (count($path_parts) === 3 && is_numeric($path_parts[2])) {
            // GET/PUT/DELETE /api/projects/{id}
            require_once 'api/projects/detail.php';
            
        } elseif (count($path_parts) === 4 && is_numeric($path_parts[2]) && $path_parts[3] === 'invite') {
            // POST /api/projects/{id}/invite
            require_once 'api/projects/invite.php';
            
        } elseif (count($path_parts) === 4 && is_numeric($path_parts[2]) && $path_parts[3] === 'tasks') {
            // GET/POST /api/projects/{id}/tasks
            require_once 'api/tasks/index.php';
            
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Endpoint tidak ditemukan"]);
        }
        
    } elseif (count($path_parts) >= 2 && $path_parts[1] === 'tasks') {
        
        if (count($path_parts) === 3 && is_numeric($path_parts[2])) {
            // GET/PUT/DELETE /api/tasks/{id}
            require_once 'api/tasks/detail.php';
            
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Endpoint tidak ditemukan"]);
        }
        
    } elseif (count($path_parts) >= 2 && $path_parts[1] === 'public') {
        
        if (count($path_parts) === 3 && $path_parts[2] === 'projects') {
            // GET /api/public/projects
            require_once 'api/public/projects.php';
            
        } elseif (count($path_parts) === 5 && $path_parts[2] === 'projects' && is_numeric($path_parts[3]) && $path_parts[4] === 'tasks') {
            // GET /api/public/projects/{id}/tasks
            require_once 'api/public/tasks.php';
            
        } elseif (count($path_parts) === 4 && $path_parts[2] === 'tasks' && is_numeric($path_parts[3])) {
            // GET /api/public/tasks/{id}
            require_once 'api/public/task_detail.php';
            
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Endpoint publik tidak ditemukan"]);
        }
        
    } else {
        http_response_code(404);
        echo json_encode(["message" => "API endpoint tidak ditemukan"]);
    }
    
} else {
    // Halaman utama - info API
    header('Content-Type: application/json');
    echo json_encode([
        "message" => "TaskFlow API",
        "version" => "1.0",
        "endpoints" => [
            "POST /api/login" => "Login member",
            "GET /api/projects" => "List proyek member",
            "POST /api/projects" => "Tambah proyek",
            "GET /api/projects/{id}" => "Detail proyek",
            "PUT /api/projects/{id}" => "Edit proyek",
            "DELETE /api/projects/{id}" => "Hapus proyek",
            "POST /api/projects/{id}/invite" => "Undang anggota",
            "GET /api/projects/{id}/tasks" => "List tugas proyek",
            "POST /api/projects/{id}/tasks" => "Tambah tugas",
            "GET /api/tasks/{id}" => "Detail tugas",
            "PUT /api/tasks/{id}" => "Edit tugas",
            "DELETE /api/tasks/{id}" => "Hapus tugas",
            "GET /api/public/projects" => "List proyek publik",
            "GET /api/public/projects/{id}/tasks" => "List tugas publik",
            "GET /api/public/tasks/{id}" => "Detail tugas publik"
        ]
    ]);
}
?>
