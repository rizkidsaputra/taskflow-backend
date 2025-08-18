<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/_helpers.php';
$user = require_auth($pdo);

$project_id = (int)($_GET['id'] ?? 0);
if (!$project_id) json_error('Missing id', 400);
list($allowed, $proj) = user_can_access_project($pdo, $user['id'], $project_id);
if (!$proj) json_error('Not found', 404);
if (!$allowed) json_error('Forbidden', 403);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
  $m = $pdo->prepare('SELECT m.user_id, u.username, u.full_name FROM project_members m JOIN users u ON u.id=m.user_id WHERE m.project_id=?');
  $m->execute([$project_id]);
  json_ok(['data'=>$m->fetchAll()]);
}
if ($method === 'POST') {
  if ((int)$proj['owner_id'] !== (int)$user['id']) json_error('Only owner can add member', 403);
  $d = json_input();
  $user_id = (int)($d['user_id'] ?? 0);
  if (!$user_id) json_error('user_id required', 422);
  $pdo->prepare('INSERT IGNORE INTO project_members (project_id, user_id) VALUES (?,?)')->execute([$project_id, $user_id]);
  json_ok([], 'Member added');
}
if ($method === 'DELETE') {
  if ((int)$proj['owner_id'] !== (int)$user['id']) json_error('Only owner can remove member', 403);
  $uid = (int)($_GET['user_id'] ?? 0);
  if (!$uid) json_error('user_id required', 422);
  $pdo->prepare('DELETE FROM project_members WHERE project_id=? AND user_id=?')->execute([$project_id, $uid]);
  json_ok([], 'Member removed');
}
json_error('Method not allowed', 405);
?>
