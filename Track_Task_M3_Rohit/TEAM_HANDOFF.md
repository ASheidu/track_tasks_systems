# Team CTRL — Milestone 3 integration agreement

Use this package as the shared starting point on a Milestone 3 integration
branch. Everyone uses the same schema and helpers. Keep the submitted
Milestone 2 ZIP as the reference for the design. Each teammate should work
on their own branch, make 8–10 meaningful commits spread across the work,
and open a pull request against the agreed integration branch.

## Files and ownership

| Owner | Files and responsibility |
|---|---|
| Rohit | sql/schema.sql, config.example.php, includes/bootstrap.php, includes/tasks.php, public/index.php, public/tasks.php, public/css/theme.css, includes/workspace.php and includes/workspace-end.php. Coordinate deployment and integration. |
| Amrit | public/login.php, public/register.php, public/logout.php and any account-specific CSS or UI JavaScript. |
| Sheidu | public/dashboard.php and any dashboard-specific CSS or UI JavaScript. |

## Shared database and PHP helpers

- Import sql/schema.sql once per development database. users has id,
  display_name, email, username and password_hash. Email and username
  are unique. tasks has id, user_id, title, description, due_date,
  priority, status and category. Tags belong to an account and task_tags
  connects them to tasks with same-owner foreign keys.
- At the top of your PHP routes use:
  `require_once __DIR__ . '/../includes/bootstrap.php';`
  `db()` returns a configured PDO connection. Use prepared queries with
  positional (?) parameters. Do not make a second connection file.
- Protected routes call `$userId = requireLogin();` before output. Use
  that ID for every account-scoped SQL query. Escape HTML with `h($value)`.
- On successful login, regenerate the session ID, clear old session data,
  and set `$_SESSION['user_id']` (integer), `$_SESSION['display_name']`
  (from users.display_name), and `$_SESSION['last_activity'] = time()`.
  Then call `redirect('dashboard.php')`. The common login check enforces
  a 30-minute idle timeout.
- POST forms send a hidden `csrf_token` from `csrfToken()`. Call
  `checkCsrf()` before processing a POST. For logout accept POST only,
  check the token, call `signOut()` and `redirect('login.php')`.
- Render HTML from PHP. GET routes read data, POST routes change it.
  Display date calculations use America/Chicago and `date('Y-m-d')`.
  An overdue task is due before today and is not Completed. Due today
  is not overdue. Priorities: Low, Medium, High. Statuses: To-Do,
  In Progress, Completed. Do not make Overdue a saved status.

## Amrit: account pages

Replace demo login with real account creation and password verification.
Use PHP validation, password_hash()/password_verify(), uniqueness checks,
and a generic invalid-login response. Initialize the agreed session keys
and implement POST logout. Report an expired session when the login page
receives expired=1. Keep Show Password and accessible form labels.
Load css/theme.css on both pages. Use the shared form-grid class for fields
that fit side by side and full for fields that should span both columns.
The shared layout automatically switches to one column at 760px or below.
Register and Login can have their own account-page layout. Coordinate
before adding fields to users or preserving demo-only settings.

## Sheidu: analytics

Require login, then count only tasks belonging to $userId. Keep the
In Progress, Completed and Overdue cards and the completion bar.
Completion percentage is completed / total x 100, rounded to a whole
number; use 0 if the account has no tasks. Overdue can overlap with
In Progress. Use the PHP date rule above so Dashboard and My Tasks agree.

Set `$pageTitle = 'Dashboard'; $activePage = 'dashboard';` and include
includes/workspace.php before page content and includes/workspace-end.php
after it. Link Add Task to `tasks.php?mode=add#task-form`. Keep Overdue
visually prominent. Use shared theme classes and put any extra dashboard
styles in a scoped stylesheet. Set $pageCss to css/dashboard.css before including the shared layout if needed.
Avoid duplicating the sidebar.

## Evidence and submission

Each teammate sends Rohit the PR link, changed files and routes, test
results, screenshots, and their own reflection. Document the three official
backend security techniques once the combined site is running: password
hashing (Amrit), prepared statements (all), and session expiration (shared).
Do not claim unrun tests. Deploy the full app on a backend-capable host
after agreeing on the PHP/MySQL setup; retain the Milestone 2 static site
as the previous milestone.
