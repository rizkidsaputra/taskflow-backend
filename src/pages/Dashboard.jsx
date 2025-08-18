import React, { useEffect, useState } from "react";
import ProjectForm from "../components/ProjectForm";
import ProjectList from "../components/ProjectList";
import {
  fetchProjects as apiFetchProjects,
  createProject as apiCreateProject,
  updateProject as apiUpdateProject,
  deleteProject as apiDeleteProject,
  fetchUsers as apiFetchUsers,
} from "../utils/api";

export default function Dashboard({ user }) {
  const [projects, setProjects] = useState([]);
  const [showForm, setShowForm] = useState(false);
  const [editProject, setEditProject] = useState(null);
  const [loading, setLoading] = useState(false);
  const [allUsers, setAllUsers] = useState([]);

  // === Ambil data project dari backend ===
  const fetchProjects = async () => {
    setLoading(true);
    try {
      const data = await apiFetchProjects();
      setProjects(data || []);
    } catch (err) {
      console.error("Gagal fetch projects:", err);
    } finally {
      setLoading(false);
    }
  };

  // === Ambil semua user (hanya jika login) ===
  const fetchAllUsers = async () => {
    if (!user) return; // ⛔ jangan fetch kalau guest
    try {
      const data = await apiFetchUsers();
      setAllUsers(data || []);
    } catch (err) {
      console.error("Gagal fetch users:", err);
    }
  };

  useEffect(() => {
    fetchProjects();
    fetchAllUsers(); // hanya dijalankan kalau ada user
  }, [user]);

  // === Tambah project ===
  const handleAdd = async (project) => {
    try {
      const data = await apiCreateProject(project);
      setProjects((prev) => [...prev, data]);
      setShowForm(false);
    } catch (err) {
      console.error("Gagal tambah project:", err);
    }
  };

  // === Edit project ===
  const handleUpdate = async (project) => {
    try {
      const updated = await apiUpdateProject(project.id, project);

      // merge supaya field lama (tasks dll) tetap ada
      setProjects((prev) =>
        prev.map((p) =>
          p.id === project.id ? { ...p, ...project, ...updated } : p
        )
      );

      setShowForm(false);
      setEditProject(null);
    } catch (err) {
      console.error("Gagal update project:", err);
    }
  };

  // === Hapus project ===
  const handleDelete = async (id) => {
    if (!window.confirm("Hapus project ini?")) return;
    try {
      await apiDeleteProject(id);
      setProjects((prev) => prev.filter((p) => p.id !== id));
    } catch (err) {
      console.error("Gagal hapus project:", err);
    }
  };

  const handleUpdateTasks = (projectId, updatedTasks) => {
    try {
      setProjects((prev) =>
        prev.map((p) =>
          p.id === projectId ? { ...p, tasks: updatedTasks } : p
        )
      );
    } catch (err) {
      console.error("Gagal update tasks:", err);
    }
  };

  return (
    <div className="p-6 mt-14">
      <header className="flex justify-between items-center mb-6">
        <div className="pl-2">
          <h2 className="text-2xl font-bold">Dashboard</h2>
          <p className="text-sm text-gray-400">
            {user ? `View Dashboard ${user.username}` : "View Guest"}
          </p>

          {!user && (
            <p className="text-sm text-gray-400">
              Anda belum login, hanya bisa melihat project publik.
            </p>
          )}
        </div>

        <div className="flex gap-2">
          {user && (
            <button
              onClick={() => {
                setEditProject(null);
                setShowForm(true);
              }}
              className="bg-gray-500 text-white px-4 py-1.5 rounded-md"
            >
              + Add Project
            </button>
          )}
        </div>
      </header>

      {loading ? (
        <p>Loading projects...</p>
      ) : (
        <>
          {showForm && (
            <ProjectForm
              project={editProject}
              allUsers={allUsers}
              onSave={editProject ? handleUpdate : handleAdd}
              onCancel={() => {
                setEditProject(null);
                setShowForm(false);
              }}
            />
          )}

          <ProjectList
            projects={projects}
            user={user}
            allUsers={allUsers}
            onDelete={handleDelete}
            onUpdateTasks={handleUpdateTasks}
            onEditProject={(project) => {
              setEditProject(project);
              setShowForm(true);
            }}
          />
        </>
      )}
    </div>
  );
}
