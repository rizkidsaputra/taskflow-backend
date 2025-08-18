<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../projects/_helpers.php';
$current_user = get_user_by_token($pdo);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
  $project_id = (int)($_GET['project_id'] ?? 0);
  if (!$project_id) json_error('Missing project_id', 400);

  if (!$current_user) {
    $q = $pdo->prepare('SELECT is_private FROM projects WHERE id=?');
    $q->execute([$project_id]);
    $row = $q->fetch(PDO::FETCH_ASSOC);
    if (!$row) json_error('Project not found', 404);
    if ((int)$row['is_private'] !== 0) json_error('Forbidden', 403);

    $stmt = $pdo->prepare("SELECT t.id, t.title, t.description, t.status,
                                  t.assignee, u.username AS assignee_name,
                                  t.created_at, t.deadline
                           FROM tasks t
                           LEFT JOIN users u ON u.id = t.assignee
                           WHERE t.project_id=? 
                           ORDER BY t.created_at DESC");
    $stmt->execute([$project_id]);
    json_ok(['tasks' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
  }

  $current_user = $current_user;
  list($allowed, $proj) = user_can_access_project($pdo, $current_user['id'], $project_id);
  if (!$proj) json_error('Project not found', 404);
  if (!$allowed) json_error('Forbidden', 403);

  $stmt = $pdo->prepare("SELECT t.id, t.title, t.description, t.status,
                                t.assignee, u.username AS assignee_name,
                                t.created_at, t.deadline
                         FROM tasks t
                         LEFT JOIN users u ON u.id = t.assignee
                         WHERE t.project_id=? 
                         ORDER BY t.created_at DESC");
  $stmt->execute([$project_id]);
  json_ok(['tasks' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

if ($method === 'POST') {
  $current_user = require_auth($pdo);

  $d = json_input();
  $project_id = (int)($d['project_id'] ?? 0);
  $title = trim($d['title'] ?? '');
  $description = trim($d['description'] ?? '');
  $assignees = $d['assignees'] ?? null; $assignee = $d['assignee'] ?? null;
  if (!$project_id || $title==='') json_error('project_id and title required', 422);

  list($allowed, $p) = user_can_access_project($pdo, $current_user['id'], $project_id);
  if (!$p) json_error('Project not found', 404);
  if (!$allowed) json_error('Forbidden', 403);

  $stmt = $pdo->prepare('INSERT INTO tasks (project_id, title, description, status, assignee, created_at) 
                         VALUES (?,?,?,?,?,NOW())');
  $stmt->execute([$project_id, $title, $description, 'todo', $assignee]);

  json_ok(['id' => (int)$pdo->lastInsertId()], 'Task created');
}

json_error('Method not allowed', 405);