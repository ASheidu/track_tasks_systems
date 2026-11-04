# Dashboard Documentation

Author: Sheidu Al-Ameen
Course: CSCI 426
Project: Task Management System

## Files

- dashboard.html: Page structure, navigation, task form,
  summaries, task list, reminders, and progress section.
- dashboard.css: Styling, responsive layout, and focus indicators.
- dashboard.js: Task data, validation, filtering, completion,
  browser storage, and progress calculations.
- js/task-store.js: Shared task storage, validation, and summary calculations.

## Running the dashboard

From the project folder, run:

python3 -m http.server 8000

Open:

http://localhost:8000/dashboard.html

## Frontend behavior

The dashboard uses sample tasks and localStorage.
It does not implement a backend or real authentication.

## Checks performed

- [x] Task creation
- [x] Invalid input handling
- [x] Completion and progress updates
- [x] Search and combined filters
- [x] Persistence after refresh
- [x] Keyboard navigation
- [x] Narrow-screen layout
- [x] Navigation to team pages

## Integration notes

Task storage key: trackTask.tasks.v1

My Tasks links to tasks.html.
Calendar links to the planned-features section.
The dashboard and My Tasks use js/task-store.js.
## Known issues

Record any remaining issues here.
