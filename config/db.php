<?php
$host = "taskflow-mysql";
$port = "3306";
$db_name = "taskflow";   // use the database created from taskflow.sql
$username = "root";
$password = "root";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db_name;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>