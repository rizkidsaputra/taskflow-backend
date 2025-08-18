// === src/components/ProjectForm.jsx ===
import React, { useState } from "react";

export default function ProjectForm({ project, onSave, onCancel }) {
  const [title, setTitle] = useState(project?.title || "");
  const [description, setDescription] = useState(project?.description || "");
  const [isPrivate, setIsPrivate] = useState(Number(project?.is_private) === 1);

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!title.trim()) return alert("Title wajib diisi");
    onSave({
      id: project?.id,
      title: title.trim(),
      description: description.trim(),
      is_private: isPrivate ? 1 : 0,
    });
  };

  return (
    <div className="bg-black/60 fixed inset-0 z-40 flex items-center justify-center">
      <div className="bg-gray-800 rounded-2xl p-5 w-full max-w-lg border border-white/10">
        <h3 className="text-lg font-semibold mb-4">
          {project ? "Edit Project" : "Add Project"}
        </h3>

        <form onSubmit={handleSubmit} className="space-y-3">
          <div>
            <label className="text-sm">Title</label>
            <input
              className="w-full bg-gray-900 rounded-lg px-3 py-2 mt-1"
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              placeholder="Nama project"
            />
          </div>

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

          <label className="inline-flex items-center gap-2">
            <input
              type="checkbox"
              checked={isPrivate}
              onChange={(e) => setIsPrivate(e.target.checked)}
            />
            <span>Private</span>
          </label>

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
              className="px-3 py-1.5 rounded-lg bg-green-600 hover:bg-green-500"
            >
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}