# Database notes

Track Task uses MySQL with InnoDB tables and utf8mb4 text.
The schema source is sql/schema.sql. Database credentials belong in
config.local.php outside public, or in the host's environment settings.

| Table | Purpose | Keys and constraints |
|---|---|---|
| users | Account name, email, username, password hash | id primary key; email and username unique |
| tasks | Task details and owner | id primary key; user_id references users |
| tags | Reusable tag names belonging to an account | id primary key; user_id references users; unique user_id/name |
| task_tags | Connects tasks with tags | composite primary key user_id/task_id/tag_id; owner-aware foreign keys |

## Relationships

One user can own many tasks and many tags. A task can have many tags,
and a tag can belong to many tasks through task_tags.

Composite foreign keys in task_tags include user_id. This prevents a
task from being linked to a tag belonging to another account.
Deleting a task removes its linking rows. Deleting a user removes
that user's tasks, tags, and related links through cascading rules.

Categories are optional text on tasks; they do not require another table.
Overdue is calculated from an unfinished task's due date, not stored as
a separate status. Dates are stored as YYYY-MM-DD and shown on My Tasks
as MM/DD/YYYY. Status values are To-Do, In Progress, and Completed.

## Task request flow

1. PHP checks the session and gets the signed-in user ID.
2. GET requests load that user's tasks and apply validated search filters.
3. POST requests verify the CSRF token and validate the submitted fields.
4. Prepared queries bind values instead of joining user input into SQL.
5. Successful changes redirect to the task list with a status message.

| Operation | Request | Database work |
|---|---|---|
| Create | POST action=add | Insert task and tag links |
| Read | GET tasks.php | Select tasks restricted by user_id |
| Update | POST action=edit, complete, or reopen | Update an owned task |
| Delete | POST action=delete | Delete an owned task and cascade its links |

Task and tag changes are saved together in a transaction.
Shared helpers are in includes/bootstrap.php and includes/tasks.php.
Dashboard queries must also restrict records by the session user ID.

This note supplements the schema; the final database documentation
still needs a diagram showing these relationships and the required
submission evidence.
