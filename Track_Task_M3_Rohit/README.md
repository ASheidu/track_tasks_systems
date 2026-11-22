# Track Task — Milestone 3 / Rohit's part

This package provides the shared MySQL schema, connection/session helpers,
workspace theme and layout, and the PHP My Tasks page. It is an integration
package for Team CTRL. Amrit's login, registration and logout routes and
Sheidu's dashboard route must be added before the normal user flow can run.

## Local setup

1. Install PHP 8 with PDO MySQL and mbstring enabled, and start MySQL.
2. Create an empty database called track_task. Select it and import
   sql/schema.sql (for example, from your database manager's Import screen).
   The schema does not drop existing tables or create any sample users.
3. Copy config.example.php to config.local.php. Enter your own database
   settings. Use an app database account with SELECT, INSERT, UPDATE and
   DELETE permissions on track_task. Use a separate account for schema setup.
4. From this folder run: `php -S localhost:8000 -t public`
5. After integrating Amrit's and Sheidu's routes, open
   http://localhost:8000/. The PHP server replaces VS Code Live Server.
   Keep the server's document root at public/ so configuration, schema, and
   internal helpers cannot be downloaded as public files.

The server may also read DB_HOST, DB_PORT, DB_NAME, DB_USER and DB_PASSWORD
from its environment. Set APP_HTTPS=1 for an HTTPS deployment behind a proxy
when PHP does not receive HTTPS directly. Never commit config.local.php.

## What Rohit's page does

- GET /tasks.php lists the signed-in user's tasks. Search checks title,
  description, category and tag names. Category, priority, status and date
  filters work alone or together. GET mode=add opens the task form;
  mode=edit&id=... opens editing; mode=delete&id=... shows confirmation.
- POST /tasks.php handles add, edit, delete, complete and reopen. Updates
  and deletions are always scoped to the signed-in user's ID. A successful
  POST redirects to the list and shows a status message.
- The server checks task fields and tag limits, and saves a task and its tag
  links together. All SQL values are bound to placeholders.
- The shared theme uses the existing Track Task palette. Task text is 16px
  at browser defaults and the Overdue badge uses a bold visible label.
  The layout provides a skip link, labels, focus outlines and a keyboard
  scrollable table. Pair with actual keyboard and screen-reader testing.
- CSV import/export was optional for this phase and is not in this build.
  Milestone 2 browser tasks are not automatically migrated.

## Integration checklist

1. Register two test accounts through the combined app.
2. With account A, add two tasks that share a tag, and add a second tag to
   one task. Verify the tasks still appear after signing out and back in.
3. Search for a word in a title and a tag. Test priority and date filters
   alone, then combine search and filters. Try a search with no matches.
4. Edit a title and tag, complete and reopen a task, and confirm deletion
   from its confirmation screen. Verify the dashboard after each change.
5. Sign in as account B and confirm A's tasks and dashboard counts are not
   visible. Confirm task edit and delete controls never modify another
   account's records. Check that expired sessions redirect to Login.
6. Check that invalid dates, missing titles and overlong entries are rejected
   on the server. Test the final routes and labels on desktop and phone.
7. Test the deployed app with real test accounts before updating the
   submission's cover page and links.

See TEAM_HANDOFF.md for the routes, shared PHP interface and file ownership.
