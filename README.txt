TaskFlow PHP Backend (Compatible with A.zip FE)

Setup (XAMPP):
1) Buat database MySQL: taskflow_db
2) Import file SQL: sql/schema.sql
3) Copy folder ini ke: C:/xampp/htdocs/taskflow-backend/
4) Jalankan Apache & MySQL (XAMPP).
5) FE config: const API_BASE_URL = "http://localhost/taskflow-backend/api";

Creds:
- username: admin
- password: admin123

Endpoints (sesuai FE):
- POST   /api/auth/login.php
- POST   /api/auth/logout.php
- GET    /api/projects/index.php
- POST   /api/projects/index.php
- GET    /api/projects/detail.php?id=:id
- PUT    /api/projects/detail.php?id=:id
- DELETE /api/projects/detail.php?id=:id
- GET/POST/DELETE /api/projects/members.php?id=:id
- POST   /api/projects/invite.php
- GET/POST /api/tasks/index.php (GET butuh ?project_id=:id)
- GET/PUT/DELETE /api/tasks/detail.php?id=:id
- GET    /api/public/projects.php
- GET    /api/public/tasks.php?project_id=:id
- GET    /api/public/task_detail.php?id=:id
