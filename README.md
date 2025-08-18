<h1 align="center">📌 TaskFlow Backend API</h1>
<p align="center">
  ⚙️ Backend untuk aplikasi manajemen tugas kolaboratif <strong>TaskFlow</strong> menggunakan <strong>PHP Native</strong>.<br>
  Dirancang untuk manajemen proyek, kolaborasi tim, dan API berbasis JSON yang siap digunakan.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Language-PHP-blue?style=flat-square" />
  <img src="https://img.shields.io/badge/Database-MySQL-orange?style=flat-square" />
  <img src="https://img.shields.io/badge/Docker-Ready-green?style=flat-square" />
  <img src="https://img.shields.io/badge/Status-Stable-brightgreen?style=flat-square" />
</p>

---

## 🚀 Fitur Utama
- 🔑 Autentikasi member (4 user awal)
- 📂 CRUD Proyek & Tugas
- 👥 Sistem kolaborasi tim
- 🌐 Akses publik untuk guest
- 📡 API berbasis JSON
- 🐳 Containerized dengan Docker

---

## 🗄 Struktur Database
**Users**  
`id`, `username`, `password_hash`, `role`, `created_at`  

**Projects**  
`id`, `name`, `description`, `created_by`, `due_date`, `is_public`, `created_at`  

**Project_Members**  
`project_id`, `user_id`, `joined_at`  

**Tasks**  
`id`, `project_id`, `title`, `description`, `status`, `assigned_to`, `is_public`, `due_date`, `created_at`, `updated_at`

---

## 🌐 API Endpoints
### 🔐 Autentikasi
- `POST /api/auth/login.php` – Login member
- `POST /api/auth/logout.php` – Logout member

### 📂 Proyek
- `GET /api/projects/index.php` – List proyek
- `POST /api/projects/index.php` – Tambah proyek
- `GET /api/projects/index.php?id={id}` – Detail proyek
- `PUT /api/projects/index.php` – Edit proyek
- `DELETE /api/projects/index.php` – Hapus proyek
- `POST /api/projects/index.php?action=invite&id={id}` – Undang anggota
- `GET /api/projects/members.php?project_id={id}` – List anggota proyek

### 📋 Tugas
- `GET /api/tasks/index.php?project_id={id}` – List tugas proyek
- `POST /api/tasks/index.php` – Tambah tugas
- `GET /api/tasks/index.php?id={id}` – Detail tugas
- `PUT /api/tasks/index.php` – Edit tugas
- `DELETE /api/tasks/index.php` – Hapus tugas

### 🌍 Akses Publik
- `GET /api/public/projects.php` – List proyek publik
- `GET /api/public/tasks.php?project_id={id}` – List tugas publik
- `GET /api/public/tasks.php?id={id}` – Detail tugas publik

---

## 📦 Instalasi
### Opsi 1 – Docker (PHP-FPM) - CMD Windows
## 1. Jalankan MySQL container
```cmd
docker run -d --name mysql-taskflow ^
  -e MYSQL_ROOT_PASSWORD=root123 ^
  -e MYSQL_DATABASE=taskflow ^
  -p 3306:3306 ^
  mysql:8.0
```

---

## 2. Tunggu MySQL siap (±60 detik)
```cmd
timeout /t 60
```

---

## 3. Import database schema
```cmd
docker cp database/setup.sql mysql-taskflow:/setup.sql
docker exec mysql-taskflow mysql -uroot -proot123 taskflow -e "source /setup.sql"
```

---

## 4. Verifikasi database berhasil terimport
```cmd
docker exec mysql-taskflow mysql -uroot -proot123 -e "SHOW TABLES;" taskflow
```

---

## 5. Build image dan jalankan container PHP
```cmd
docker build -t taskflow-backend .
docker run -d --name taskflow-api ^
  --link mysql-taskflow:mysql ^
  -p 8000:8000 ^
  taskflow-backend
```

---

## 6. Setup user awal & jalankan server
```cmd
docker exec -it taskflow-api bash

:: Jalankan PHP built-in server
php -S 0.0.0.0:8000 -t /var/www/html ^

:: Inisialisasi 4 user awal
php /var/www/html/database/init_users.php

:: Keluar container
exit
```

⚠️ **Penting:** Hapus `database/init_users.php` setelah selesai setup:
```cmd
docker exec -it taskflow-api rm /var/www/html/database/init_users.php
```


---

### Opsi 2 – XAMPP (Manual)
1. Install XAMPP dan aktifkan Apache & MySQL  
2. Copy folder ke `C:\xampp\htdocs\taskflow-backend`  
3. Import `database/setup.sql` lewat phpMyAdmin  
4. Jalankan `database/init_users.php` sekali, lalu hapus  
5. Test API dengan `curl` atau Postman

---

## Testing API dengan Curl (Windows CMD)

### 1. Autentikasi

**Login dan Simpan Session:**
```cmd
curl -X POST http://localhost/taskflow-backend/api/auth/login.php ^
  -H "Content-Type: application/json" ^
  -d "{\"username\":\"lovind\",\"password\":\"password123\"}" ^
  -c cookies.txt
```

**Logout:**
```cmd
curl -X POST http://localhost/taskflow-backend/api/auth/logout.php ^
  -b cookies.txt
```

### 2. Manajemen Proyek

**List Proyek Member:**
```cmd
curl -X GET http://localhost/taskflow-backend/api/projects/index.php ^
  -b cookies.txt
```

**Tambah Proyek:**
```cmd
curl -X POST http://localhost/taskflow-backend/api/projects/index.php ^
  -H "Content-Type: application/json" ^
  -b cookies.txt ^
  -d "{\"name\":\"Website Redesign\",\"description\":\"Redesign company website\",\"is_public\":true,\"due_date\":\"2024-12-31\"}"
```

**Detail Proyek:**
```cmd
curl -X GET "http://localhost/taskflow-backend/api/projects/index.php?id=1" ^
  -b cookies.txt
```

**Edit Proyek:**
```cmd
curl -X PUT http://localhost/taskflow-backend/api/projects/index.php ^
  -H "Content-Type: application/json" ^
  -b cookies.txt ^
  -d "{\"id\":1,\"name\":\"Website Redesign - Updated\",\"description\":\"Complete website redesign with new features\",\"is_public\":false,\"due_date\":\"2025-01-15\"}"
```

**Hapus Proyek:**
```cmd
curl -X DELETE http://localhost/taskflow-backend/api/projects/index.php ^
  -H "Content-Type: application/json" ^
  -b cookies.txt ^
  -d "{\"id\":1}"
```

### 3. Manajemen Anggota Proyek

**Undang Anggota ke Proyek:**
```cmd
curl -X POST "http://localhost/taskflow-backend/api/projects/index.php?action=invite&id=1" ^
  -H "Content-Type: application/json" ^
  -b cookies.txt ^
  -d "{\"username\":\"danish\"}"
```

**List Anggota Proyek:**
```cmd
curl -X GET "http://localhost/taskflow-backend/api/projects/members.php?project_id=1" ^
  -b cookies.txt
```

**Hapus Anggota dari Proyek:**
```cmd
curl -X DELETE http://localhost/taskflow-backend/api/projects/members.php ^
  -H "Content-Type: application/json" ^
  -b cookies.txt ^
  -d "{\"project_id\":1,\"user_id\":2}"
```

### 4. Manajemen Tugas

**List Tugas Proyek:**
```cmd
curl -X GET "http://localhost/taskflow-backend/api/tasks/index.php?project_id=1" ^
  -b cookies.txt
```

**Tambah Tugas:**
```cmd
curl -X POST http://localhost/taskflow-backend/api/tasks/index.php ^
  -H "Content-Type: application/json" ^
  -b cookies.txt ^
  -d "{\"project_id\":1,\"title\":\"Design Homepage\",\"description\":\"Create new homepage design mockup\",\"status\":\"todo\",\"priority\":\"high\",\"assigned_to\":2,\"is_public\":true,\"deadline\":\"2024-12-31\"}"
```

**Detail Tugas:**
```cmd
curl -X GET "http://localhost/taskflow-backend/api/tasks/index.php?id=1" ^
  -b cookies.txt
```

**Edit Tugas:**
```cmd
curl -X PUT http://localhost/taskflow-backend/api/tasks/index.php ^
  -H "Content-Type: application/json" ^
  -b cookies.txt ^
  -d "{\"id\":1,\"title\":\"Design Homepage - Revised\",\"description\":\"Create homepage design with client feedback\",\"status\":\"in_progress\",\"priority\":\"medium\",\"assigned_to\":3,\"is_public\":true,\"deadline\":\"2024-12-25\"}"
```

**Hapus Tugas:**
```cmd
curl -X DELETE http://localhost/taskflow-backend/api/tasks/index.php ^
  -H "Content-Type: application/json" ^
  -b cookies.txt ^
  -d "{\"id\":1}"
```

### 5. Akses Publik (Guest - Tanpa Login)

**List Proyek Publik:**
```cmd
curl -X GET http://localhost/taskflow-backend/api/public/projects.php
```

**List Tugas Publik dari Proyek:**
```cmd
curl -X GET "http://localhost/taskflow-backend/api/public/tasks.php?project_id=1"
```

**Detail Tugas Publik:**
```cmd
curl -X GET "http://localhost/taskflow-backend/api/public/tasks.php?id=1"
```


---

## 🔒 Keamanan
- ✅ Password hash dengan `password_hash()`
- ✅ Prepared statements
- ✅ Validasi input
- ✅ Session-based auth
- ⚠️ Hapus `database/init_users.php` setelah setup
- ⚠️ Ganti password default database

---

## 📂 Struktur Folder
```
taskflow-backend/
├── api/
│   ├── auth/
│   ├── projects/
│   ├── tasks/
│   └── public/
├── config/
├── utils/
├── database/
├── .htaccess
├── Dockerfile
└── README.md
```

<p align="center">Backend TaskFlow siap digunakan! 🚀</p>
