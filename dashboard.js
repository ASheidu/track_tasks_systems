"use strict";

const STORAGE_KEY = "trackTasks.tasks.v1";

const $ = (id) => document.getElementById(id);

const priorities = ["low", "medium", "high"];
const statuses = ["in-progress", "completed"];

const names = {
  "in-progress": "In progress",
  completed: "Completed",
  overdue: "Overdue"
};

function localDate(offset = 0) {
  const date = new Date();

  date.setDate(date.getDate() + offset);

  const pad = (n) => String(n).padStart(2, "0");

  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
}

function validDate(value) {
  if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) {
    return false;
  }

  const date = new Date(`${value}T12:00:00`);
  const [year, month, day] = value.split("-").map(Number);

  return (
    !Number.isNaN(date.getTime()) &&
    date.getFullYear() === year &&
    date.getMonth() + 1 === month &&
    date.getDate() === day
  );
}

function demoTasks() {
  return [
    {
      id: "demo-1",
      title: "Design dashboard",
      description: "Build the dashboard layout.",
      dueDate: localDate(1),
      priority: "high",
      status: "in-progress"
    },
    {
      id: "demo-2",
      title: "Update user stories",
      description: "Review the requirements.",
      dueDate: localDate(-1),
      priority: "high",
      status: "in-progress"
    },
    {
      id: "demo-3",
      title: "Review team feedback",
      description: "Discuss page consistency.",
      dueDate: localDate(2),
      priority: "medium",
      status: "in-progress"
    },
    {
      id: "demo-4",
      title: "Prepare site map",
      description: "Connect the main pages.",
      dueDate: localDate(),
      priority: "low",
      status: "completed"
    }
  ];
}

function validTask(task) {
  return (
    task &&
    typeof task.id === "string" &&
    typeof task.title === "string" &&
    task.title.trim().length >= 3 &&
    task.title.length <= 80 &&
    typeof task.description === "string" &&
    task.description.length <= 300 &&
    typeof task.dueDate === "string" &&
    validDate(task.dueDate) &&
    priorities.includes(task.priority) &&
    statuses.includes(task.status)
  );
}

function announce(message) {
  $("announcement").textContent = message;
}

function loadTasks() {
  try {
    const saved = localStorage.getItem(STORAGE_KEY);

    if (saved === null) {
      return demoTasks();
    }

    const data = JSON.parse(saved);

    if (
      !Array.isArray(data) ||
      !data.every(validTask) ||
      new Set(data.map((task) => task.id)).size !== data.length
    ) {
      throw new Error("Invalid task data");
    }

    return data;
  } catch {
    announce("Saved tasks could not be loaded. Showing sample tasks.");
    return demoTasks();
  }
}

let tasks = loadTasks();

function saveTasks() {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(tasks));
    return true;
  } catch {
    announce(
      "Changes work on this page, but browser storage is unavailable."
    );

    return false;
  }
}

function displayStatus(task) {
  if (task.status === "completed") {
    return "completed";
  }

  return task.dueDate < localDate() ? "overdue" : "in-progress";
}

function render() {
  const completed = tasks.filter(
    (task) => task.status === "completed"
  ).length;

  const overdue = tasks.filter(
    (task) => displayStatus(task) === "overdue"
  ).length;

  const percent = tasks.length
    ? Math.round(completed / tasks.length * 100)
    : 0;

  $("in-progress-count").textContent =
    tasks.length - completed - overdue;

  $("completed-count").textContent = completed;
  $("overdue-count").textContent = overdue;

  $("progress-percent").textContent = `${percent}%`;
  $("progress-bar").value = percent;
  $("progress-bar").textContent = `${percent}%`;

  $("progress-description").textContent =
    `${completed} of ${tasks.length} tasks completed`;

  $("reminder-text").textContent = overdue
    ? `${overdue} overdue task(s) need your attention.`
    : "You have no overdue tasks.";

  const query = $("search").value.trim().toLowerCase();

  const filtered = tasks.filter((task) =>
    `${task.title} ${task.description}`
      .toLowerCase()
      .includes(query) &&

    (
      $("status-filter").value === "all" ||
      displayStatus(task) === $("status-filter").value
    ) &&

    (
      $("priority-filter").value === "all" ||
      task.priority === $("priority-filter").value
    )
  );

  $("task-list").replaceChildren();

  $("empty-message").hidden = filtered.length > 0;

  $("results-count").textContent =
    `Showing ${filtered.length} of ${tasks.length} tasks`;

  filtered.forEach((task) => {
    const row = document.createElement("li");

    row.className =
      `task-row ${task.status === "completed" ? "task-completed" : ""}`;

    const label = document.createElement("label");
    label.className = "task-check";

    const checkbox = document.createElement("input");
    checkbox.type = "checkbox";
    checkbox.checked = task.status === "completed";

    checkbox.setAttribute(
      "aria-label",
      `Completed: ${task.title}`
    );

    checkbox.addEventListener("change", () => {
      task.status = checkbox.checked
        ? "completed"
        : "in-progress";

      const saved = saveTasks();

      render();

      if (saved) {
        announce("Task status updated.");
      }

      $("search").focus();
    });

    label.append(checkbox);

    const details = document.createElement("div");
    details.className = "task-details";

    const title = document.createElement("p");
    title.className = "task-title";
    title.textContent = task.title;

    const meta = document.createElement("p");
    meta.className = "task-meta";

    const date = new Date(
      `${task.dueDate}T12:00:00`
    ).toLocaleDateString();

    meta.textContent =
      `Due ${date} · ${task.priority} priority`;

    details.append(title, meta);

    const badge = document.createElement("span");
    badge.className = `badge ${displayStatus(task)}`;
    badge.textContent = names[displayStatus(task)];

    row.append(label, details, badge);

    $("task-list").append(row);
  });
}

function closeForm() {
  $("task-form").reset();
  $("form-error").textContent = "";

  $("task-form")
    .querySelectorAll("[aria-invalid]")
    .forEach((field) => {
      field.removeAttribute("aria-invalid");
    });

  $("task-panel").hidden = true;

  $("show-form").setAttribute("aria-expanded", "false");
  $("show-form").focus();
}

function showError(id, message) {
  $("form-error").textContent = message;

  $(id).setAttribute("aria-invalid", "true");
  $(id).focus();
}

$("show-form").addEventListener("click", () => {
  const opening = $("task-panel").hidden;

  $("task-panel").hidden = !opening;

  $("show-form").setAttribute(
    "aria-expanded",
    String(opening)
  );

  $("task-date").min = localDate();

  if (opening) {
    $("task-title").focus();
  }
});

$("cancel-form").addEventListener("click", closeForm);

$("search").addEventListener("input", render);
$("status-filter").addEventListener("change", render);
$("priority-filter").addEventListener("change", render);

$("task-form").addEventListener("submit", (event) => {
  event.preventDefault();

  $("task-form")
    .querySelectorAll("[aria-invalid]")
    .forEach((field) => {
      field.removeAttribute("aria-invalid");
    });

  $("form-error").textContent = "";

  const title = $("task-title").value.trim();
  const description = $("task-description").value.trim();
  const dueDate = $("task-date").value;
  const priority = $("task-priority").value;

  if (title.length < 3 || title.length > 80) {
    return showError(
      "task-title",
      "Enter a task title with 3–80 characters."
    );
  }

  if (description.length > 300) {
    return showError(
      "task-description",
      "Keep the description within 300 characters."
    );
  }

  if (!validDate(dueDate) || dueDate < localDate()) {
    return showError(
      "task-date",
      "Choose today or a future date."
    );
  }

  if (!priorities.includes(priority)) {
    return showError(
      "task-priority",
      "Choose Low, Medium, or High priority."
    );
  }

  tasks.unshift({
    id: `task-${Date.now()}-${Math.random().toString(36).slice(2)}`,
    title,
    description,
    dueDate,
    priority,
    status: "in-progress"
  });

  const saved = saveTasks();

  $("search").value = "";
  $("status-filter").value = "all";
  $("priority-filter").value = "all";

  closeForm();
  render();

  if (saved) {
    announce("Task created and saved in this browser.");
  }
});

window.addEventListener("storage", (event) => {
  if (event.key === STORAGE_KEY || event.key === null) {
    tasks = loadTasks();
    render();
  }
});

$("task-date").min = localDate();

saveTasks();
render();