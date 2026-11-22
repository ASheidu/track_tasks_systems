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
$total = (int) $counts['total'];
$inProgress = (int) $counts['in_progress'];
$completed = (int) $counts['completed'];
$overdue = (int) $counts['overdue'];

$percent = 0;
if ($total > 0) {
    $percent = (int) round(($completed / $total) * 100);
}

$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require __DIR__ . '/../includes/workspace.php';
?>

<div class="row split">
    <h1>Dashboard</h1>
    <a class="button" href="tasks.php?mode=add#task-form">Add Task</a>
</div>
<?php if ($total === 0): ?>
    <p class="panel">
        <strong>No tasks yet.</strong> Choose Add Task to save your first task.
    </p>
<?php endif; ?>
<section aria-labelledby="overview-title">
    <h2 id="overview-title">Task overview</h2>

    <dl class="dashboard-stats">
        <div class="panel<?= $overdue > 0 ? ' dashboard-alert' : '' ?>">
            <dt>Overdue</dt>
            <dd><?= $overdue ?></dd>
        </div>

        <div class="panel">
            <dt>In Progress</dt>
            <dd><?= $inProgress ?></dd>
        </div>

        <div class="panel">
            <dt>Completed</dt>
            <dd><?= $completed ?></dd>
        </div>
    </dl>
</section>
<section class="panel dashboard-progress" aria-labelledby="completion-title">
    <div class="row split">
        <h2 id="completion-title">Overall completion</h2>
        <strong><?= $percent ?>%</strong>
    </div>

    <progress aria-labelledby="completion-title" max="100" value="<?= $percent ?>">
        <?= $percent ?>%
    </progress>

    <p class="muted">
        <?= $completed ?> of <?= $total ?> <?= $total === 1 ? 'task' : 'tasks' ?> completed.
    </p>
</section>
<?php require __DIR__ . '/../includes/workspace-end.php'; ?>