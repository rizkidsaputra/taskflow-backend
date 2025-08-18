<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../lib/auth.php';

/**
 * Login dengan email ATAU username + password.
 * Return: { success, data: { token, user: {id, username, full_name, email} } }
 */
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') { json_error('Method not allowed', 405); }

$d = json_input();
$email = trim($d['email'] ?? '');
$password = $d['password'] ?? '';

if ($email === '' || $password === '') json_error('Email/Username dan password wajib', 422);

// Cari user by email atau username
$stmt = $pdo->prepare('SELECT id, username, full_name, email, password FROM users WHERE email = ? OR username = ? LIMIT 1');
$stmt->execute([$email, $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) json_error('User tidak ditemukan', 401);

// Verifikasi password (hash)
if (!password_verify($password, $user['password'])) { json_error('Password salah', 401); }

$token = issue_token($pdo, (int)$user['id']);

json_ok([
  'token' => $token,
  'user' => [
    'id' => (int)$user['id'],
    'username' => $user['username'],
    'full_name' => $user['full_name'],
    'email' => $user['email'],
  ]
]);
?>