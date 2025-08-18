<?php
require_once __DIR__ . '/../../lib/response.php';

function user_can_access_project($pdo, $user_id, $project_id) {
  $q = $pdo->prepare('SELECT p.owner_id, p.is_private FROM projects p WHERE p.id=?');
  $q->execute([$project_id]);
  $p = $q->fetch();
  if (!$p) return [false, null];
  if ((int)$p['is_private'] === 0) return [true, $p];
  if ((int)$p['owner_id'] === (int)$user_id) return [true, $p];
  $m = $pdo->prepare('SELECT 1 FROM project_members WHERE project_id=? AND user_id=? LIMIT 1');
  $m->execute([$project_id, $user_id]);
  if ($m->fetch()) return [true, $p];
  return [false, $p];
}
?>
