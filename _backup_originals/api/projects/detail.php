<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/_helpers.php';
$current_user = get_user_by_token($pdo);

$id = (int)($_GET['id'] ?? 0);
if (!$id) json_error('Missing id', 400);
list($allowed, $proj) = user_can_access_project($pdo, $current_user['id'], $id);
if (!$proj) json_error('Not found', 404);
if (!$allowed) json_error('Forbidden', 403);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'GET') {
  $id = (int)($_GET['id'] ?? 0);
  if (!$id) json_error('Missing id', 400);
  if (!$current_user) {
    $q = $pdo->prepare('SELECT p.id, p.title, p.description, p.owner_id, p.is_private, u.username AS owner_name FROM projects p JOIN users u ON u.id=p.owner_id WHERE p.id=?');
    $q->execute([$id]);
    $row = $q->fetch(PDO::FETCH_ASSOC);
    if (!$row) json_error('Not found', 404);
    if ((int)$row['is_private'] !== 0) json_error('Forbidden', 403);
    json_ok(['data' => $row]);
  }
  $current_user = $current_user;

  $q = $pdo->prepare('SELECT p.id, p.title, p.description, p.owner_id, u.username AS owner_name, p.is_private, p.created_at FROM projects p JOIN users u ON u.id=p.owner_id WHERE p.id=?');
  $q->execute([$id]);
  json_ok(['data' => $q->fetch()]);
}
if ($method === 'PUT') {
  if ((int)$proj['owner_id'] !== (int)$current_user['id']) json_error('Only owner can edit', 403);
  $d = json_input();
  $title = $d['title'] ?? null;
  $description = $d['description'] ?? null;
  $is_private = $d['is_private'] ?? null;
  $upd = $pdo->prepare('UPDATE projects SET title=COALESCE(?, title), description=COALESCE(?, description), is_private=COALESCE(?, is_private) WHERE id=?');
  $upd->execute([$title, $description, $is_private, $id]);
  json_ok([], 'Project updated');
}
if ($method === 'DELETE') {
  if ((int)$proj['owner_id'] !== (int)$current_user['id']) json_error('Only owner can delete', 403);
  $pdo->prepare('DELETE FROM projects WHERE id=?')->execute([$id]);
  $pdo->prepare('DELETE FROM project_members WHERE project_id=?')->execute([$id]);
  $pdo->prepare('DELETE FROM tasks WHERE project_id=?')->execute([$id]);
  json_ok([], 'Project deleted');
}
json_error('Method not allowed', 405);
?>
