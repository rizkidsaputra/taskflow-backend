<?php
require_once __DIR__ . '/../../config/cors.php';
$id = (int)($_GET['id'] ?? 0);
if (!$id) { echo json_encode(["success"=>false,"message"=>"Missing id"]); exit; }
$stmt = $pdo->prepare('SELECT t.id, t.project_id, t.title, t.description, t.status, t.assignee, t.created_at, t.deadline FROM tasks t JOIN projects p ON p.id=t.project_id WHERE t.id=? AND p.is_private=0');
$stmt->execute([$id]);
$task = $stmt->fetch();
if (!$task) { echo json_encode(["success"=>false,"message"=>"Not found"]); exit; }
echo json_encode(["success"=>true,"data"=>$task]);
?>
