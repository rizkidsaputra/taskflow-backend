<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../projects/_helpers.php';
$current_user = require_auth($pdo);

$id = (int)($_GET['id'] ?? 0);
if (!$id) json_error('Missing id', 400);

$task = $pdo->prepare('SELECT t.*, p.owner_id, p.is_private FROM tasks t JOIN projects p ON p.id=t.project_id WHERE t.id=?');
$task->execute([$id]);
$t = $task->fetch();
if (!$t) json_error('Not found', 404);

// check access
if ((int)$t['is_private'] === 1) {
  if ((int)$t['owner_id'] !== (int)$current_user['id']) {
    $m = $pdo->prepare('SELECT 1 FROM project_members WHERE project_id=? AND user_id=? LIMIT 1');
    $m->execute([$t['project_id'], $current_user['id']]);
    if (!$m->fetch()) json_error('Forbidden', 403);
  }
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'GET') {
  json_ok(['data' => $t]);
}
if ($method === 'PUT') {
  $d = json_input();
  $title = $d['title'] ?? $t['title'];
  $description = $d['description'] ?? $t['description'];
  $status = $d['status'] ?? $t['status'];
  $assignees = $d['assignees'] ?? null; $assignee = $d['assignee'] ?? $t['assignee'];
  $upd = $pdo->prepare('UPDATE tasks SET title=?, description=?, status=?, assignee=? WHERE id=?');
  $upd->execute([$title, $description, $status, $assignee, $id]);
  if (is_array($assignees)) {
    $pdo->prepare('DELETE FROM task_assignees WHERE task_id=?')->execute([$id]);
    $ins = $pdo->prepare('INSERT IGNORE INTO task_assignees (task_id, user_id) VALUES (?,?)');
    foreach ($assignees as $uid) { $ins->execute([$id, (int)$uid]); }
  }
  json_ok([], 'Task updated');
}
if ($method === 'DELETE') {
  // Only project owner can delete
  if ((int)$t['owner_id'] !== (int)$current_user['id']) json_error('Only owner can delete', 403);
  $pdo->prepare('DELETE FROM tasks WHERE id=?')->execute([$id]);
  json_ok([], 'Task deleted');
}
json_error('Method not allowed', 405);
?>
