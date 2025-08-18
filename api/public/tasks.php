<?php
require_once __DIR__ . '/../../config/cors.php';
$project_id = (int)($_GET['project_id'] ?? 0);
if (!$project_id) { echo json_encode(["success"=>false,"message"=>"Missing project_id"]); exit; }
$stmt = $pdo->prepare('SELECT id, project_id, title, description, status, assignee, created_at, deadline FROM tasks WHERE project_id=? ORDER BY id DESC');
$stmt->execute([$project_id]);
echo json_encode(["success"=>true,"data"=>$stmt->fetchAll()]);
?>
