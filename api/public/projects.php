<?php
require_once __DIR__ . '/../../config/cors.php';
// Only public projects
$stmt = $pdo->query('SELECT p.id, p.title, p.description, u.username AS owner_name, p.created_at FROM projects p JOIN users u ON u.id=p.owner_id WHERE p.is_private=0 ORDER BY p.created_at DESC');
echo json_encode(["success"=>true,"data"=>$stmt->fetchAll()]);
?>
