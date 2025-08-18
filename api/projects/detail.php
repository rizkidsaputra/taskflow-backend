<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/response.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) json_error('Missing id', 400);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
  $user = get_user_by_token($pdo); // optional
  // Allow if public or member/owner
  $sql = "SELECT p.*, u.username AS owner_name FROM projects p JOIN users u ON u.id=p.owner_id WHERE p.id=?";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$id]);
  $proj = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$proj) json_error('Not found', 404);

  if ($proj['is_private']) {
    $u = require_auth($pdo);
    // check membership/ownership
    $q = $pdo->prepare("SELECT 1 FROM project_members WHERE project_id=? AND user_id=?");
    $q->execute([$id, $u['id']]);
    $isMember = (bool)$q->fetchColumn();
    if ((int)$proj['owner_id'] !== (int)$u['id'] && !$isMember) json_error('Forbidden', 403);
  }
  json_ok(['project' => $proj]);
}

if ($method === 'PUT' || $method === 'PATCH') {
  $u = require_auth($pdo);
  // Only owner can update
  $q = $pdo->prepare("SELECT owner_id FROM projects WHERE id=?");
  $q->execute([$id]);
  $ownerId = $q->fetchColumn();
  if (!$ownerId) json_error('Not found', 404);
  if ((int)$ownerId !== (int)$u['id']) json_error('Only owner can update', 403);

  $d = json_input();
  $title = array_key_exists('title',$d) ? trim($d['title']) : null;
  $description = array_key_exists('description',$d) ? trim($d['description']) : null;
  $is_private = array_key_exists('is_private',$d) ? (int)$d['is_private'] : null;

  $stmt = $pdo->prepare("UPDATE projects SET
            title = COALESCE(?, title),
            description = COALESCE(?, description),
            is_private = COALESCE(?, is_private)
          WHERE id=?");
  $stmt->execute([$title, $description, $is_private, $id]);
  json_ok([], 200);
}

if ($method === 'DELETE') {
  $u = require_auth($pdo);
  // Only owner can delete
  $q = $pdo->prepare("SELECT owner_id FROM projects WHERE id=?");
  $q->execute([$id]);
  $ownerId = $q->fetchColumn();
  if (!$ownerId) json_error('Not found', 404);
  if ((int)$ownerId !== (int)$u['id']) json_error('Only owner can delete', 403);

  $pdo->prepare('DELETE FROM tasks WHERE project_id=?')->execute([$id]);
  $pdo->prepare('DELETE FROM project_members WHERE project_id=?')->execute([$id]);
  $pdo->prepare('DELETE FROM projects WHERE id=?')->execute([$id]);
  json_ok([], 200);
}

json_error('Method not allowed', 405);
