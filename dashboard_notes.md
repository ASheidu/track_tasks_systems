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

Task storage key: trackTasks.tasks.v1

Confirm that tasks.html and calendar.html match
the team's implemented filenames.

## Known issues

Record any remaining issues here.