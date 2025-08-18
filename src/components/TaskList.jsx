import React from "react";
import TaskItem from "./TaskItem";

export default function TaskList({ tasks, onEdit, onDelete, user, projectOwner }) {
  if (!tasks || !tasks.length) {
    return <p className="text-gray-400">No tasks</p>;
  }

  return (
    <div className="flex flex-col gap-3">
      {tasks.map((task) => {
        const canEditOrDelete =
          user && (user.username === projectOwner || user.id === task.assignee_id);

        return (
          <TaskItem
            key={task.id}
            task={task}
            onEdit={canEditOrDelete ? () => onEdit(task) : undefined}
            onDelete={canEditOrDelete ? () => onDelete(task.id) : undefined}
          />
        );
      })}
    </div>
  );
}