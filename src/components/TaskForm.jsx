// === src/components/TaskForm.jsx ===
import React, { useMemo, useState } from "react";

export default function TaskForm({
  projectId,
  task,
  allUsers,
  onSave,
  onCancel,
}) {
  const [title, setTitle] = useState(task?.title || "");
  const [description, setDescription] = useState(task?.description || "");
  const [status, setStatus] = useState(task?.status || "todo");
  const [assignee, setAssignee] = useState(task?.assignee || ""); // hanya 1 user ID
  const [deadline, setDeadline] = useState(task?.deadline || "");

  const userOptions = useMemo(
    () => (Array.isArray(allUsers) ? allUsers : []),
    [allUsers]
  );

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!title.trim()) {
      alert("Title wajib diisi");
      return;
    }

    onSave({
      id: task?.id,
      project_id: projectId,
      title: title.trim(),
      description: description.trim(),
      status,
      assignee: assignee || null, // kirim satu ID saja
      deadline: deadline || null,
    });
  };

  return (
    <div className="bg-black/60 fixed inset-0 z-40 flex items-center justify-center">
      <div className="bg-gray-800 rounded-2xl p-5 w-full max-w-lg border border-white/10">
        <h3 className="text-lg font-semibold mb-4">
          {task ? "Edit Task" : "Add Task"}
        </h3>

        <form onSubmit={handleSubmit} className="space-y-3">
          {/* Title */}
          <div>
            <label className="text-sm">Title</label>
            <input
              className="w-full bg-gray-900 rounded-lg px-3 py-2 mt-1"
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              placeholder="Nama tugas"
            />
          </div>

          {/* Description */}
          <div>
            <label className="text-sm">Description</label>
            <textarea
              className="w-full bg-gray-900 rounded-lg px-3 py-2 mt-1"
              value={description}
              onChange={(e) => setDescription(e.target.value)}
              placeholder="Deskripsi singkat"
              rows={3}
            />
          </div>

          {/* Status + Assignee */}
          <div className="grid grid-cols-2 gap-3">
            <div>
              <label className="text-sm">Status</label>
              <select
                className="w-full bg-gray-900 rounded-lg px-3 py-2 mt-1"
                value={status}
                onChange={(e) => setStatus(e.target.value)}
              >
                <option value="todo">To Do</option>
                <option value="in_progress">In Progress</option>
                <option value="done">Done</option>
              </select>
            </div>

            <div>
              <label className="text-sm">Assignee</label>
              <select
                value={assignee}
                onChange={(e) => setAssignee(e.target.value)}
                className="w-full p-2 rounded bg-gray-700"
              >
                <option value="">Pilih user</option>
                {userOptions.map((u) => (
                  <option key={u.id} value={u.id}>
                    {u.username}
                  </option>
                ))}
              </select>
            </div>
          </div>

          {/* Deadline */}
          <div>
            <label className="text-sm">Deadline (opsional)</label>
            <input
              type="datetime-local"
              className="w-full bg-gray-900 rounded-lg px-3 py-2 mt-1"
              value={deadline || ""}
              onChange={(e) => setDeadline(e.target.value)}
            />
          </div>

          {/* Actions */}
          <div className="flex justify-end gap-2 pt-2">
            <button
              type="button"
              onClick={onCancel}
              className="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20"
            >
              Batal
            </button>
            <button
              type="submit"
              className="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500"
            >
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
