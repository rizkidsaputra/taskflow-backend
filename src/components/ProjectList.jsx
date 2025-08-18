// === src/components/ProjectList.jsx ===
import React, { useState } from "react";
import {
  createTask as apiCreateTask,
  updateTask as apiUpdateTask,
  deleteTask as apiDeleteTask,
} from "../utils/api";
import { API_ENDPOINTS } from "../utils/config";
import TaskList from "./TaskList";
import TaskForm from "./TaskForm";

function formatDate(iso) {
  if (!iso) return "-";
  const d = new Date(iso);
  const dd = String(d.getDate()).padStart(2, "0");
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  const yy = d.getFullYear();
  const hh = String(d.getHours()).padStart(2, "0");
  const min = String(d.getMinutes()).padStart(2, "0");
  return `${dd}/${mm}/${yy} ${hh}:${min}`;
}

export default function ProjectList({
  projects,
  user,
  allUsers,
  onDelete, // (projectId) => void
  onUpdateProject, // (projectId, patchedProjectObj) => void
  onUpdateTasks, // (projectId, updatedTasks) => void
  onEditProject, // (projectObj) => void
}) {
  const [showTaskForm, setShowTaskForm] = useState(false);
  const [currentProjectId, setCurrentProjectId] = useState(null);
  const [editingTask, setEditingTask] = useState(null);

  const mustLogin = (msg = "Anda harus login untuk melakukan aksi ini") => {
    alert(msg);
    return false;
  };

  const openAddTask = (projectId) => {
    if (!user) return mustLogin("Anda harus login untuk menambah tugas");
    setCurrentProjectId(projectId);
    setEditingTask(null);
    setShowTaskForm(true);
  };

  const openEditTask = (projectId, task) => {
    if (!user) return mustLogin("Anda harus login untuk mengedit tugas");
    setCurrentProjectId(projectId);
    setEditingTask(task);
    setShowTaskForm(true);
  };

  const closeTaskForm = () => {
    setShowTaskForm(false);
    setEditingTask(null);
    setCurrentProjectId(null);
  };

  const saveTask = async (task) => {
    if (!user) return mustLogin("Anda harus login untuk menyimpan tugas");
    const project = projects.find((p) => p.id === currentProjectId);
    if (!project) return;

    try {
      if (editingTask && task.id) {
        // update task
        await apiUpdateTask(task.id, {
          project_id: currentProjectId,
          title: task.title,
          description: task.description,
          status: task.status,
          assignee: task.assignee || null,
          deadline: task.deadline || null,
        });
      } else {
        // create new task
        await apiCreateTask({
          project_id: currentProjectId,
          title: task.title,
          description: task.description,
          status: task.status || "todo",
          assignee: task.assignee || null,
          deadline: task.deadline || null,
        });
      }

      // Refresh tasks dari server
      const res = await fetch(API_ENDPOINTS.tasks.list(currentProjectId), {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token") || ""}`,
        },
      });
      const data = await res.json();
      // backend balikin { success: true, data: [...] }
      onUpdateTasks(currentProjectId, data?.data || []); // ✅ bukan data.tasks
    } catch (e) {
      alert(e.message || "Gagal menyimpan task");
    } finally {
      closeTaskForm();
    }
  };

  const deleteTask = async (projectId, taskId) => {
    if (!user) return mustLogin("Anda harus login untuk menghapus tugas");
    if (!window.confirm("Hapus tugas ini?")) return;

    try {
      await apiDeleteTask(taskId);

      // update state di FE
      const project = projects.find((p) => p.id === projectId);
      if (!project) return;
      const updatedTasks = (project.tasks || []).filter((t) => t.id !== taskId);
      onUpdateTasks(projectId, updatedTasks);
    } catch (e) {
      alert(e.message || "Gagal menghapus task");
    }
  };

  if (!projects?.length) {
    return (
      <p className="text-gray-400">Tidak ada project untuk ditampilkan.</p>
    );
  }

  return (
    <div className="grid gap-6 md:grid-cols-2 items-start">
      {projects
        .filter((project) => {
          // Guest: hanya boleh lihat project public
          if (!user) return !project.is_private;

          // Owner: boleh lihat semua project sendiri
          if (project.owner_name === user.username) return true;

          // User lain: boleh lihat kalau dia jadi assignee salah satu task
          const isAssigned = (project.tasks || []).some(
            (t) => t.assignee_id === user.id
          );
          return isAssigned;
        })
        .map((project) => {
          const tasks = project.tasks || [];
          const canEditProject = !!user && user.username === project.owner_name;

          return (
            <section
              key={project.id}
              className="bg-gray-800 rounded-2xl p-5 shadow-lg"
            >
              <header className="flex justify-between items-start mb-4">
                <div className="min-w-0">
                  <h3 className="text-xl font-bold truncate">
                    {project.title}
                  </h3>
                  <div className="flex flex-wrap items-center gap-2 text-sm text-gray-400 mt-1">
                    <span>{project.is_private ? "Private" : "Public"}</span>
                    <span>Owner: {project.owner_name}</span>
                    <span className="whitespace-nowrap">
                      Created: {formatDate(project.created_at)}
                    </span>
                    <span>{tasks.length} task</span>
                  </div>
                </div>

                <div className="flex gap-2 items-center shrink-0">
                  {canEditProject && (
                    <>
                      <button
                        onClick={() => onEditProject(project)}
                        title="Edit Project"
                        className="p-2 rounded hover:bg-white/5"
                      >
                        ✏️
                      </button>
                      <button
                        onClick={() => onDelete(project.id)}
                        title="Delete Project"
                        className="p-2 rounded hover:bg-red-600/20 text-red-400"
                      >
                        🗑️
                      </button>
                    </>
                  )}
                </div>
              </header>

              {user && (
                <div className="mb-4">
                  <button
                    onClick={() => openAddTask(project.id)}
                    className="text-sm bg-gray-700 hover:bg-gray-600 px-3 py-1 rounded-md"
                  >
                    + Add Task
                  </button>
                </div>
              )}

              <TaskList
                tasks={tasks}
                user={user}
                projectOwner={project.owner_name}
                onEdit={(task) => openEditTask(project.id, task)}
                onDelete={(taskId) => deleteTask(project.id, taskId)}
              />

              {showTaskForm && currentProjectId === project.id && (
                <TaskForm
                  task={editingTask}
                  allUsers={allUsers}
                  projectId={project.id}
                  onSave={saveTask}
                  onCancel={closeTaskForm}
                />
              )}
            </section>
          );
        })}
    </div>
  );
}
