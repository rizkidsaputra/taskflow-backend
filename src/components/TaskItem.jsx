import React from "react";

function formatDate(iso) {
  if (!iso) return "-";
  const d = new Date(iso);
  const dd = String(d.getDate()).padStart(2, "0");
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  const yy = d.getFullYear();
  return `${dd}/${mm}/${yy}`;
}

export default function TaskItem({ task, onEdit, onDelete }) {
  const statusLabel =
    task.status === "done"
      ? "Done"
      : task.status === "in_progress"
      ? "On Progress"
      : task.status || "-";

  const tagColor =
    task.status === "done"
      ? "bg-green-600"
      : task.status === "in_progress"
      ? "bg-yellow-500"
      : "bg-gray-600";

  return (
    <div className="bg-gray-900 p-4 rounded-xl shadow-sm hover:shadow-md transition">
      <div className="flex justify-between gap-4">
        <div className="min-w-0">
          {/* Judul & deskripsi */}
          <h4 className="text-lg font-semibold truncate">{task.title}</h4>
          <p className="text-sm text-gray-300 mt-1 line-clamp-3">
            {task.description || "-"}
          </p>
          {/* Status, deadline, created_at */}
          <div className="flex flex-wrap items-center gap-3 text-sm text-gray-400 mt-3">
            <span
              className={`${tagColor} text-white px-2 py-0.5 rounded-full text-xs font-semibold leading-none`}
            >
              {statusLabel}
            </span>
            <span>Deadline: {formatDate(task.deadline)}</span>
            <span>Created: {formatDate(task.created_at)}</span>
          </div>

          <p className="text-sm text-gray-300 flex items-center gap-1 mt-3">
            <span className="text-purple-400">👤 Assignee:</span>
            <span>{task.assignee_name || "-"}</span>
          </p>
        </div>

        {/* Tombol aksi */}
        <div className="flex flex-col items-end gap-2">
          {onEdit && (
            <button
              onClick={() => onEdit(task)}
              title="Edit Task"
              className="p-1 rounded hover:bg-white/5"
            >
              ✏️
            </button>
          )}
          {onDelete && (
            <button
              onClick={() => onDelete(task.id)}
              title="Delete Task"
              className="p-1 rounded hover:bg-red-600/20 text-red-400"
            >
              🗑️
            </button>
          )}
        </div>
      </div>
    </div>
  );
}
