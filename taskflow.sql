
-- Taskflow Database Schema and Dummy Data

CREATE DATABASE IF NOT EXISTS taskflow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE taskflow;

-- Users table
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
);

-- Projects table
CREATE TABLE IF NOT EXISTS projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  is_private TINYINT(1) DEFAULT 0
);

-- Tasks table
CREATE TABLE IF NOT EXISTS tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project_id INT NOT NULL,
  title VARCHAR(100) NOT NULL,
  description TEXT,
  status ENUM('todo','on_progress','done') DEFAULT 'todo',
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

-- Insert default user (password = 123456)
INSERT INTO users (username, password) VALUES
('admin', '$2y$10$M/pX9uA/5xO4SP.oBLmO5uVm9Lo8UKUoN/yFs5yWqFZJB9IkoHZ3K');

-- Insert projects
INSERT INTO projects (name, description, is_private) VALUES
('Project A', 'Deskripsi project A', 0),
('Project B', 'Deskripsi project B', 1);

-- Insert tasks for Project A (id=1)
INSERT INTO tasks (project_id, title, description, status) VALUES
(1, 'Setup environment', 'Install dependencies dan konfigurasi project', 'todo'),
(1, 'Build authentication', 'Membuat sistem login', 'on_progress'),
(1, 'Deploy ke server', 'Upload ke hosting', 'done');

-- Insert tasks for Project B (id=2)
INSERT INTO tasks (project_id, title, description, status) VALUES
(2, 'Draft proposal', 'Menyusun dokumen proposal awal', 'todo'),
(2, 'Review dokumen', 'Cek ulang sebelum submit', 'done');
