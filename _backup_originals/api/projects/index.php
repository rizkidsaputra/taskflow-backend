  <?php
  require_once __DIR__ . '/../../config/cors.php';
  require_once __DIR__ . '/../../lib/auth.php';
  $current_user = get_user_by_token($pdo);

  $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

  if ($method === 'GET') {
    // Guest hanya bisa lihat project public
    if (!$current_user) {
      $stmt = $pdo->query(
        'SELECT p.id, p.title, p.description, p.owner_id, u.username AS owner_name,
                p.is_private, p.created_at
        FROM projects p 
        JOIN users u ON u.id = p.owner_id
        WHERE p.is_private = 0 
        ORDER BY p.created_at DESC'
      );
      $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // tambahkan task ke setiap project
      foreach ($projects as &$proj) {
        $tstmt = $pdo->prepare("SELECT t.id, t.title, t.description, t.status, 
                                      t.assignee, u.username AS assignee_name,
                                      t.created_at, t.deadline
                                FROM tasks t
                                LEFT JOIN users u ON u.id = t.assignee
                                WHERE t.project_id=? 
                                ORDER BY t.created_at DESC");
        $tstmt->execute([$proj['id']]);
        $proj['tasks'] = $tstmt->fetchAll(PDO::FETCH_ASSOC);
      }

      json_ok(['projects' => $projects]);
    }

    // User login
    $user = $current_user;
    $stmt = $pdo->prepare(
      'SELECT DISTINCT p.id, p.title, p.description, p.owner_id, u.username AS owner_name,
                        p.is_private, p.created_at
      FROM projects p
      JOIN users u ON u.id = p.owner_id
      LEFT JOIN project_members m ON m.project_id = p.id
      WHERE p.is_private = 0 OR p.owner_id = ? OR m.user_id = ?
      ORDER BY p.created_at DESC'
    );
    $stmt->execute([$user['id'], $user['id']]);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // tambahkan task ke setiap project
    foreach ($projects as &$proj) {
      $tstmt = $pdo->prepare("SELECT t.id, t.title, t.description, t.status, 
                                    t.assignee, u.username AS assignee_name,
                                    t.created_at, t.deadline
                              FROM tasks t
                              LEFT JOIN users u ON u.id = t.assignee
                              WHERE t.project_id=? 
                              ORDER BY t.created_at DESC");
      $tstmt->execute([$proj['id']]);
      $proj['tasks'] = $tstmt->fetchAll(PDO::FETCH_ASSOC);
    }

    json_ok(['projects' => $projects]);
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
  $q = $pdo->prepare('SELECT p.id, p.title, p.description, p.owner_id, u.username AS owner_name, p.is_private, p.created_at FROM projects p JOIN users u ON u.id=p.owner_id WHERE p.id=?');
  $q->execute([$id]);
  $project = $q->fetch(PDO::FETCH_ASSOC);
  $project['tasks'] = [];
  json_ok(['project' => $project], 'Project created');
}
