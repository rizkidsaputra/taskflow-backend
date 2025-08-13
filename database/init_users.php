<?php
/**
 * Script untuk membuat hash password yang benar untuk 4 user awal
 * Jalankan sekali saja saat setup, lalu hapus file ini untuk keamanan
 */

require_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Data user awal
    $users = [
        ['username' => 'lovind', 'password' => 'lovind123'],
        ['username' => 'danish', 'password' => 'danish123'],
        ['username' => 'fauzan', 'password' => 'fauzan123'],
        ['username' => 'rizki', 'password' => 'rizki123']
    ];
    
    // Hapus user lama jika ada
    $db->exec("DELETE FROM users");
    
    // Insert user baru dengan password hash yang benar
    $stmt = $db->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'member')");
    
    foreach ($users as $user) {
        $password_hash = password_hash($user['password'], PASSWORD_DEFAULT);
        $stmt->execute([$user['username'], $password_hash]);
        echo "User {$user['username']} berhasil ditambahkan\n";
    }
    
    echo "Semua user berhasil ditambahkan. Hapus file ini untuk keamanan!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
