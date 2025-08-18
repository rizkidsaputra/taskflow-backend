<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/response.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$user = get_user_by_token($pdo); // nullable — guests can list public

if ($method === 'GET') {
  if ($user) {
    // Owner or member projects
    $sql = "SELECT DISTINCT p.id, p.title, p.description, p.owner_id, u.username AS owner_name,
                   p.is_private, p.created_at
            FROM projects p
            JOIN users u ON u.id = p.owner_id
            LEFT JOIN project_members m ON m.project_id = p.id
            WHERE p.owner_id = ? OR m.user_id = ?
            ORDER BY p.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user['id'], $user['id']]);
  } else {
    // Guests: only public
    $sql = "SELECT p.id, p.title, p.description, p.owner_id, u.username AS owner_name,
                   p.is_private, p.created_at
            FROM projects p
            JOIN users u ON u.id = p.owner_id
            WHERE p.is_private = 0
            ORDER BY p.created_at DESC";
    $stmt = $pdo->query($sql);
  }

  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // === Ambil tasks untuk setiap project ===
  foreach ($rows as &$proj) {
    $q = $pdo->prepare("SELECT t.id, t.title, t.description, t.status, t.deadline, t.created_at,
                               u.id AS assignee_id, u.full_name AS assignee_name
                        FROM tasks t
                        LEFT JOIN users u ON u.id = t.assignee
                        WHERE t.project_id = ?
                        ORDER BY t.id DESC");
    $q->execute([$proj['id']]);
    $proj['tasks'] = $q->fetchAll(PDO::FETCH_ASSOC);
  }

  json_ok(['projects' => $rows]);
}

if ($method === 'POST') {
  $user = require_auth($pdo);
  $d = json_input();
  $title = trim($d['title'] ?? '');
  $description = trim($d['description'] ?? '');
  $is_private = isset($d['is_private']) ? (int)$d['is_private'] : 0;
  if ($title === '') json_error('title required', 422);

  $stmt = $pdo->prepare('INSERT INTO projects (title, description, owner_id, is_private, created_at) VALUES (?,?,?,?,NOW())');
  $stmt->execute([$title, $description, $user['id'], $is_private]);
  $id = (int)$pdo->lastInsertId();

  $q = $pdo->prepare('SELECT p.id, p.title, p.description, p.owner_id, u.username AS owner_name, p.is_private, p.created_at
                      FROM projects p JOIN users u ON u.id=p.owner_id WHERE p.id=?');
  $q->execute([$id]);
  $project = $q->fetch(PDO::FETCH_ASSOC);
  $project['tasks'] = [];
  json_ok(['project' => $project], 201);
}

json_error('Method not allowed', 405);
?>