// === src/utils/api.js (final fixed) ===
import { API_ENDPOINTS } from "./config";

/**
 * Centralized request helper with automatic Bearer token
 * and consistent error handling.
 */
async function request(method, url, body, extraHeaders = {}, withAuth = true) {
  const headers = { "Content-Type": "application/json", ...extraHeaders };

  if (withAuth) {
    const token = localStorage.getItem("token");
    if (token) headers["Authorization"] = `Bearer ${token}`;
  }

  const res = await fetch(url, {
    method,
    headers,
    body: body ? JSON.stringify(body) : undefined,
  });

  let data = null;
  try {
    data = await res.json();
  } catch {
    data = null;
  }

  if (!res.ok || data?.success === false) {
    const msg = data?.message || data?.error || `${res.status} ${res.statusText}`;
    throw new Error(msg);
  }

  return data;
}

export const apiGet = (url, withAuth = true) =>
  request("GET", url, null, {}, withAuth);
export const apiPost = (url, body, withAuth = true) =>
  request("POST", url, body, {}, withAuth);
export const apiPut = (url, body, withAuth = true) =>
  request("PUT", url, body, {}, withAuth);
export const apiDelete = (url, withAuth = true) =>
  request("DELETE", url, null, {}, withAuth);

// === Auth ===
export async function login({ email, password }) {
  const res = await apiPost(API_ENDPOINTS.auth.login, { email, password }, false);

  // simpan token & user di localStorage kalau ada
  const token = res?.token || res?.data?.token;
  const user = res?.user || res?.data?.user;

  if (token) localStorage.setItem("token", token);
  if (user) localStorage.setItem("user", JSON.stringify(user));

  // BALIKIN FULL RESPONSE, jangan cuma { token, user }
  return {
    success: res.success ?? true,
    message: res.message ?? "",
    token,
    user,
  };
}

export function getCurrentUser() {
  try {
    return JSON.parse(localStorage.getItem("user")) || null;
  } catch {
    return null;
  }
}

export function logout() {
  localStorage.removeItem("token");
  localStorage.removeItem("user");
}

// === Users ===
export async function fetchUsers() {
  const res = await apiGet(API_ENDPOINTS.users.list, true);
  return res?.users || res?.data?.users || [];
}

// === Projects ===
export async function fetchProjects() {
  const res = await apiGet(API_ENDPOINTS.projects.list, true);
  return res?.projects || res?.data?.projects || [];
}

export async function createProject(payload) {
  const res = await apiPost(API_ENDPOINTS.projects.list, payload, true);
  return res?.project || res?.data?.project || res;
}

export async function updateProject(id, payload) {
  const res = await apiPut(API_ENDPOINTS.projects.detail(id), payload, true);
  return res?.project || res?.data?.project || res;
}

export async function deleteProject(id) {
  return apiDelete(API_ENDPOINTS.projects.detail(id), true);
}

// === Tasks ===
export async function fetchTasks(projectId) {
  const res = await apiGet(API_ENDPOINTS.tasks.list(projectId), true);
  return res?.tasks || res?.data?.tasks || [];
}

export async function createTask(payload) {
  const res = await apiPost(API_ENDPOINTS.tasks.create, payload, true);
  return res?.task || res?.data?.task || res;
}

export async function updateTask(id, payload) {
  const res = await apiPut(API_ENDPOINTS.tasks.detail(id), payload, true);
  return res?.task || res?.data?.task || res;
}

export async function deleteTask(id) {
  return apiDelete(API_ENDPOINTS.tasks.detail(id), true);
}