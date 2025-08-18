// src/utils/config.js

// Base URL API backend (XAMPP mode)
export const API_BASE = "http://localhost/taskflow-backend/api";

// Semua endpoint yang dipakai FE
export const API_ENDPOINTS = {
  users: { list: `${API_BASE}/users/index.php` } ,
  auth: {
    login: `${API_BASE}/auth/login.php`,
  },
  projects: {
    list: `${API_BASE}/projects/index.php`,           // GET all / POST new
    detail: (id) => `${API_BASE}/projects/detail.php?id=${id}`, // GET/PUT/DELETE
  },
  tasks: {
    list: (projectId) =>
      `${API_BASE}/tasks/index.php?project_id=${projectId}`,   // GET list by project / POST new
    create: `${API_BASE}/tasks/index.php`,                     // POST
    detail: (id) => `${API_BASE}/tasks/detail.php?id=${id}`,   // GET/PUT/DELETE
  },
};