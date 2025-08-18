<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/_helpers.php';
$user = require_auth($pdo);

$data = json_input();
$project_id = (int)($data['project_id'] ?? 0);
$username = trim($data['username'] ?? '');
if (!$project_id || $username==='') json_error('project_id and username required', 422);

$q = $pdo->prepare('SELECT id, owner_id FROM projects WHERE id=?');
$q->execute([$project_id]);
$proj = $q->fetch();
if (!$proj) json_error('Project not found', 404);
if ((int)$proj['owner_id'] !== (int)$user['id']) json_error('Only owner can invite', 403);

$u = $pdo->prepare('SELECT id FROM users WHERE username=?');
$u->execute([$username]);
$target = $u->fetch();
if (!$target) json_error('User not found', 404);

$pdo->prepare('INSERT IGNORE INTO project_members (project_id, user_id) VALUES (?,?)')->execute([$project_id, $target['id']]);
json_ok([], 'User invited');
?>
