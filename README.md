TaskFlow PHP Backend (Compatible with A.zip FE)


## 🔗 Frontend

FE dapat diakses di repo ini: [TaskFlow-FullStack](https://github.com/FauzanSetengahSalmon/TaskFlow-FullStack)

---
## Backend (local)

Backend versi lokal: [taskflow-backend](https://github.com/FauzanSetengahSalmon/TaskFlow-FullStack/tree/Backend)
 
---

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


---

## 🚀 Setup dengan Docker

🎯 **Target Arsitektur**

- **FE** → http://localhost:3000  
- **BE** → http://localhost:8080/api (PHP + Apache container)  
- **DB** → MariaDB container (import taskflow.sql)  

### 1. Buat Docker Network
```bash
docker network create taskflow-net
```

### 2. Jalankan Database (MariaDB)
```bash
docker run -d   --name taskflow-mysql   --network taskflow-net   -e MYSQL_ROOT_PASSWORD=root   -e MYSQL_DATABASE=taskflow   mariadb:10.4
```
- root password: root  
- database otomatis dibuat: taskflow

### 3. Import SQL
```bash
docker cp "taskflow.sql" taskflow-mysql:/taskflow.sql
docker exec -it taskflow-mysql bash -c "mysql -uroot -proot taskflow < /taskflow.sql"
```

Cek tabel:
```bash
docker exec -it taskflow-mysql mysql -uroot -proot taskflow -e "SHOW TABLES;"
```

Harus ada: users, projects, tasks, auth_tokens, project_members.

### 4. Build & Run Backend
Dari folder `taskflow-backend`:
```bash
docker build -t taskflow-backend .
docker run -d   --name taskflow-api   --network taskflow-net   -p 8080:80   taskflow-backend
```

### 5. Test
Backend langsung:
```bash
curl http://localhost:8080/api/public/projects.php
```

FE config:

ubah FE config menjadi
```javascript
const API_BASE_URL = "http://localhost:8080/api";
```
