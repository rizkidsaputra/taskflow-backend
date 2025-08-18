<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../lib/auth.php';

$user = require_auth($pdo);

$stmt = $pdo->query('SELECT id, username, full_name, email FROM users ORDER BY username ASC');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

json_ok(['users' => $users]);
?>