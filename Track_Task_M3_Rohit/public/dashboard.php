<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$userId = requireLogin();
$today = date('Y-m-d');

$query = db()->prepare("
    SELECT COUNT(*) AS total,
           COUNT(CASE WHEN status = 'In Progress' THEN 1 END) AS in_progress,
           COUNT(CASE WHEN status = 'Completed' THEN 1 END) AS completed,
           COUNT(CASE WHEN status <> 'Completed' AND due_date < ? THEN 1 END) AS overdue
    FROM tasks
    WHERE user_id = ?
");

$query->execute([$today, $userId]);
$counts = $query->fetch();

$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require __DIR__ . '/../includes/workspace.php';
?>

<div class="row split">
    <h1>Dashboard</h1>
    <a class="button" href="tasks.php?mode=add#task-form">Add Task</a>
</div>

<?php require __DIR__ . '/../includes/workspace-end.php'; ?>