
# Taskflow - Versi htdocs (Frontend + Backend)

## Struktur
- `backend/taskflow-backend/` → copy ke `htdocs/` (misalnya `C:/xampp/htdocs/taskflow-backend/`)
- `frontend/` → aplikasi React

## Cara Jalanin
1. **Backend**
   - Copy folder `backend/taskflow-backend/` ke dalam `htdocs/` XAMPP/Laragon.
   - Hidupkan Apache + MySQL dari XAMPP/Laragon.
   - API otomatis tersedia di:
     http://localhost/taskflow-backend/taskflow-backend/api

2. **Frontend**
   - Buka terminal:
     ```bash
     cd frontend
     npm install
     npm start
     ```
   - FE akan jalan di http://localhost:3000

## Endpoint Mapping
- Projects
  - List: GET `/projects/index.php`
  - Create: POST `/projects/index.php`
  - Detail: GET `/projects/detail.php?id={id}`
  - Update: PUT `/projects/detail.php?id={id}`
  - Delete: DELETE `/projects/detail.php?id={id}`

- Tasks
  - List: GET `/tasks/index.php?project_id={projectId}`
  - Create: POST `/tasks/index.php`
  - Detail: GET `/tasks/detail.php?id={id}`
  - Update: PUT `/tasks/detail.php?id={id}`
  - Delete: DELETE `/tasks/detail.php?id={id}`

## Catatan
- Jangan ubah desain, hanya config API diarahkan ke `htdocs`.
- Pastikan DB sudah dikonfigurasi di backend (`config.php`).
