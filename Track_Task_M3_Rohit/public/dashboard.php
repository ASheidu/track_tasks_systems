<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$userId = requireLogin();

$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require __DIR__ . '/../includes/workspace.php';
?>

<div class="row split">
    <h1>Dashboard</h1>
    <a class="button" href="tasks.php?mode=add#task-form">Add Task</a>
</div>

<?php require __DIR__ . '/../includes/workspace-end.php'; ?>