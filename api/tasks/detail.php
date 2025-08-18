<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/response.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) json_response(['success'=>false,'message'=>'Missing id'], 400);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $stmt = $pdo->prepare("SELECT t.id, t.project_id, t.title, t.description, t.status, t.deadline, t.created_at,
                                  u.id AS assignee_id, u.full_name AS assignee_name
                           FROM tasks t
                           LEFT JOIN users u ON u.id = t.assignee
                           WHERE t.id = ?");
    $stmt->execute([$id]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$task) json_response(['success'=>false,'message'=>'Not found'], 404);
    json_response(['success'=>true,'data'=>$task]);
}

if ($method === 'PUT' || $method === 'PATCH') {
    $u = require_auth($pdo);
    $d = json_input();

    $title = isset($d['title']) ? trim($d['title']) : null;
    $description = isset($d['description']) ? trim($d['description']) : null;
    $status = isset($d['status']) ? $d['status'] : null;
    $deadline = array_key_exists('deadline', $d) ? $d['deadline'] : null;
    $assignee = array_key_exists('assignee', $d) ? ($d['assignee'] !== '' ? (int)$d['assignee'] : null) : null;

    $sets = [];
    $vals = [];

    if ($title !== null) { $sets[] = 'title = ?'; $vals[] = $title; }
    if ($description !== null) { $sets[] = 'description = ?'; $vals[] = $description; }
    if ($status !== null) { $sets[] = 'status = ?'; $vals[] = $status; }
    if ($deadline !== null) { $sets[] = 'deadline = ?'; $vals[] = $deadline; }
    if ($assignee !== null) { $sets[] = 'assignee = ?'; $vals[] = $assignee; }

    if (!$sets) json_response(['success'=>false,'message'=>'No fields to update'], 400);

    $vals[] = $id;
    $sql = 'UPDATE tasks SET ' . implode(', ', $sets) . ' WHERE id = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($vals);

    // Ambil task terbaru biar FE langsung bisa refresh data
    $stmt = $pdo->prepare("SELECT t.id, t.project_id, t.title, t.description, t.status, t.deadline, t.created_at,
                                  u.id AS assignee_id, u.full_name AS assignee_name
                           FROM tasks t
                           LEFT JOIN users u ON u.id = t.assignee
                           WHERE t.id = ?");
    $stmt->execute([$id]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    json_response(['success'=>true,'data'=>$task]);
}

if ($method === 'DELETE') {
    $u = require_auth($pdo);
    $pdo->prepare("DELETE FROM tasks WHERE id=?")->execute([$id]);
    json_response(['success'=>true,'deleted'=>true,'id'=>$id]);
}

json_response(['success'=>false,'message'=>'Method not allowed'], 405);
?>