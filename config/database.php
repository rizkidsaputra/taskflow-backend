<?php
/**
 * Konfigurasi koneksi database menggunakan PDO
 */
class Database {
    private $host = "mysql"; // Docker container hostname
    private $db_name = "taskflow";
    private $username = "root";
    private $password = "root123"; // MySQL container password
    public $conn;
    
    /**
     * Mendapatkan koneksi database
     */
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
}
?>
