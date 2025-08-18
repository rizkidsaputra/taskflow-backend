# Taskflow – FE + BE + SQL (XAMPP)

## Struktur
- backend/taskflow-backend/taskflow-backend/  -> letakkan folder ini di htdocs: C:\xampp\htdocs\taskflow-backend
- frontend/                                    -> jalankan React: npm install && npm start
- taskflow.sql                                 -> import ke MySQL (database name: taskflow)

## Endpoint base (FE)
Pastikan src/utils/config.js mengarah ke:
http://localhost/taskflow-backend/taskflow-backend/api

## Login
admin / admin

## Guest Mode
- Tanpa login: Dashboard menampilkan project & task PUBLIC (is_private = 0).
- Setelah login: bisa CRUD project/task (sesuai akses).