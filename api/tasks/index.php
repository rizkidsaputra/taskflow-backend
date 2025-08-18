<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/response.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    // Optional filter by project_id
    $projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;

    if ($projectId > 0) {
        $stmt = $pdo->prepare("SELECT t.id, t.project_id, t.title, t.description, t.status, t.deadline, t.created_at,
                                      u.id AS assignee_id, u.full_name AS assignee_name
                               FROM tasks t
                               LEFT JOIN users u ON u.id = t.assignee
                               WHERE t.project_id = ?
                               ORDER BY t.id DESC");
        $stmt->execute([$projectId]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        json_response(['success' => true, 'data' => $tasks]);
    } else {
        // return all tasks (admin view)
        $stmt = $pdo->query("SELECT t.id, t.project_id, t.title, t.description, t.status, t.deadline, t.created_at,
                                     u.id AS assignee_id, u.full_name AS assignee_name
                              FROM tasks t
                              LEFT JOIN users u ON u.id = t.assignee
                              ORDER BY t.id DESC");
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        json_response(['success' => true, 'data' => $tasks]);
    }
}

if ($method === 'POST') {
    $u = require_auth($pdo);
    $d = json_input();

    $title = trim($d['title'] ?? '');
    $description = trim($d['description'] ?? '');
    $projectId = (int)($d['project_id'] ?? 0);
    $deadline = $d['deadline'] ?? null;
    $assignee = isset($d['assignee']) && $d['assignee'] !== '' ? (int)$d['assignee'] : null;

    if (!$title || !$projectId) {
        json_response(['success' => false, 'message' => 'Title & project_id required'], 400);
    }

    $stmt = $pdo->prepare("INSERT INTO tasks (title, description, project_id, deadline, assignee) VALUES (?,?,?,?,?)");
    $stmt->execute([$title, $description, $projectId, $deadline, $assignee]);
    $taskId = $pdo->lastInsertId();

    // Return the created task with assignee_name to simplify FE refresh
    $stmt = $pdo->prepare("SELECT t.id, t.project_id, t.title, t.description, t.status, t.deadline, t.created_at,
                                  u.id AS assignee_id, u.full_name AS assignee_name
                           FROM tasks t
                           LEFT JOIN users u ON u.id = t.assignee
                           WHERE t.id = ?");
    $stmt->execute([$taskId]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    json_response(['success' => true, 'data' => ['task' => $task]], 201);
}

json_response(['success' => false, 'message' => 'Method not allowed'], 405);
?>
