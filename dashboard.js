"use strict";

const STORAGE_KEY = TrackTask.KEY;

const $ = (id) => document.getElementById(id);

const priorities = ["Low", "Medium", "High"];

const names = {
  "to-do": "To-Do",
  "in-progress": "In Progress",
  completed: "Completed",
  overdue: "Overdue"
};

function announce(message) {
  $("announcement").textContent = message;
}

let storageReady = true;

function loadTasks() {
  try {
    const savedTasks = TrackTask.getTasks();
    storageReady = true;
    return savedTasks;
  } catch (error) {
    storageReady = false;
    announce(error.message);
    return [];
  }
}

let tasks = loadTasks();

function saveTasks() {
  if (!storageReady) {
    announce("Saved tasks could not be loaded. Changes were not saved.");
    return false;
  }

  try {
    TrackTask.saveTasks(tasks);
    return true;
  } catch (error) {
    announce(error.message);
    tasks = loadTasks();
    return false;
  }
}

function displayStatus(task) {
  if (task.status === "Completed") {
    return "completed";
  }

  if (task.dueDate < TrackTask.today()) {
    return "overdue";
  }

  if (task.status === "To-Do") {
    return "to-do";
  }

  return "in-progress";
}

function render() {
const summary = TrackTask.getSummary(tasks);

const completed = summary.completed;
const overdue = summary.overdue;
const percent = summary.progress;

$("in-progress-count").textContent = summary.inProgress;
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
      `task-row ${task.status === "Completed" ? "task-completed" : ""}`;

    const label = document.createElement("label");
    label.className = "task-check";

    const checkbox = document.createElement("input");
    checkbox.type = "checkbox";
    checkbox.checked = task.status === "Completed";

    checkbox.setAttribute(
      "aria-label",
      `Completed: ${task.title}`
    );

    checkbox.addEventListener("change", () => {
      task.status = checkbox.checked
        ? "Completed"
        : "To-Do";

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
  
  if (!storageReady) {
  announce("Tasks cannot be saved until browser storage is available.");
  return;
}

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

  if (title.length < 1 || title.length > 100) {
  return showError(
    "task-title",
    "Enter a task title with 1–100 characters."
  );
}

if (description.length > 1000) {
  return showError(
    "task-description",
    "Keep the description within 1,000 characters."
  );
}

  if (!TrackTask.validDate(dueDate)) {
  return showError(
    "task-date",
    "Choose a valid due date."
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
  status: "To-Do",
  category: "",
  tags: []
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

render();
