<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/tasks.php';

$userId = requireLogin();
$db = db();
$today = date('Y-m-d');
$pageTitle = 'My Tasks';
$activePage = 'tasks';

$fields = ['title', 'description', 'due_date', 'priority', 'status', 'category', 'tags'];
$task = ['title' => '', 'description' => '', 'due_date' => $today, 'priority' => 'Medium',
    'status' => 'To-Do', 'category' => '', 'tags' => ''];
$mode = input($_GET, 'mode');
$id = filter_var(input($_GET, 'id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$errors = [];
$notice = $_SESSION['task_notice'] ?? '';
unset($_SESSION['task_notice']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $action = input($_POST, 'action');
    $id = filter_var(input($_POST, 'id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;

    if (in_array($action, ['edit', 'delete', 'complete', 'reopen'], true)) {
        findTask($db, $userId, $id);
    }
    if ($action === 'add' || $action === 'edit') {
        $mode = $action;
        foreach ($fields as $field) {
            $task[$field] = input($_POST, $field);
        }
        $tags = parseTaskTags($task['tags']);
        $errors = taskErrors($task, $tags);
        if (!$errors) {
            saveTask($db, $userId, $action === 'edit' ? $id : 0, $task, $tags);
            $_SESSION['task_notice'] = $action === 'edit' ? 'Task updated.' : 'Task added.';
            redirect('tasks.php');
        }
        http_response_code(422);
    } elseif ($action === 'delete') {
        $db->prepare('DELETE FROM tasks WHERE user_id = ? AND id = ?')->execute([$userId, $id]);
        $_SESSION['task_notice'] = 'Task deleted.';
        redirect('tasks.php');
    } elseif ($action === 'complete' || $action === 'reopen') {
        $status = $action === 'complete' ? 'Completed' : 'To-Do';
        $db->prepare('UPDATE tasks SET status = ? WHERE user_id = ? AND id = ?')
            ->execute([$status, $userId, $id]);
        $_SESSION['task_notice'] = $action === 'complete' ? 'Task completed.' : 'Task reopened.';
        redirect('tasks.php');
    } else {
        http_response_code(400);
        exit('Unknown task action.');
    }
} elseif ($mode === 'edit' || $mode === 'delete') {
    $task = findTask($db, $userId, $id);
}

$filters = [];
foreach (['q', 'category', 'priority', 'status', 'due_date'] as $field) {
    $filters[$field] = input($_GET, $field);
}
$filterError = '';
if (mb_strlen($filters['q']) > 100 || mb_strlen($filters['category']) > 50
    || ($filters['priority'] !== '' && !in_array($filters['priority'], PRIORITIES, true))
    || ($filters['status'] !== '' && !in_array($filters['status'], STATUSES, true))
    || ($filters['due_date'] !== '' && !taskDateIsValid($filters['due_date']))) {
    $filterError = 'One or more filters are invalid.';
    http_response_code(400);
}
if ($filterError === '') {
    $tasks = listTasks($db, $userId, $filters);
} else {
    $tasks = [];
}

function options(array $choices, string $current): void
{
    foreach ($choices as $choice) {
        $selected = $choice === $current ? ' selected' : '';
        echo '<option value="' . h($choice) . '"' . $selected . '>' . h($choice) . '</option>';
    }
}

require __DIR__ . '/../includes/workspace.php';
?>
<div class="row split">
    <div><h1>My Tasks</h1><p class="muted">Create, organize, and update your tasks.</p></div>
    <a class="button" href="tasks.php?mode=add#task-form">Add Task</a>
</div>
<?php if ($notice !== ''): ?>
    <p class="notice" role="status"><?= h($notice) ?></p>
<?php endif; ?>

<?php if ($mode === 'add' || $mode === 'edit'): ?>
<section class="panel" id="task-form" aria-labelledby="form-title">
    <h2 id="form-title"><?= $mode === 'edit' ? 'Edit Task' : 'Add Task' ?></h2>
    <?php if ($errors): ?>
        <div class="error" role="alert">
            <strong>Check these fields:</strong>
            <ul><?php foreach ($errors as $field => $message): ?>
                <li><a href="#task-<?= h($field) ?>"><?= h($message) ?></a></li>
            <?php endforeach; ?></ul>
        </div>
    <?php endif; ?>
    <form method="post" action="tasks.php#task-form" class="form-grid">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <input type="hidden" name="action" value="<?= h($mode) ?>">
        <input type="hidden" name="id" value="<?= $id ?>">
        <?php
        $labels = ['title' => 'Task title', 'description' => 'Description', 'due_date' => 'Due date',
            'priority' => 'Priority', 'status' => 'Status', 'category' => 'Category', 'tags' => 'Tags'];
        $limits = ['title' => 100, 'description' => 1000, 'category' => 50, 'tags' => 330];
        foreach ($fields as $field):
            $required = in_array($field, ['title', 'due_date', 'priority', 'status'], true);
            $attrs = ' id="task-' . $field . '" name="' . $field . '"'
                . ($required ? ' required' : '')
                . (isset($limits[$field]) ? ' maxlength="' . $limits[$field] . '"' : '')
                . (isset($errors[$field]) ? ' aria-invalid="true" aria-describedby="error-' . $field . '"' 
                    : ($field === 'tags' ? ' aria-describedby="tag-help"' : ''));
        ?>
            <div class="<?= in_array($field, ['title', 'description', 'tags'], true) ? 'full' : '' ?>">
                <label for="task-<?= $field ?>"><?= h($labels[$field]) ?><?= $required ? ' (required)' : '' ?></label>
                <?php if ($field === 'description'): ?>
                    <textarea rows="3"<?= $attrs ?>><?= h($task[$field]) ?></textarea>
                <?php elseif ($field === 'priority' || $field === 'status'): ?>
                    <select<?= $attrs ?>>
                        <?php options($field === 'priority' ? PRIORITIES : STATUSES, $task[$field]); ?>
                    </select>
                <?php else: ?>
                    <input type="<?= $field === 'due_date' ? 'date' : 'text' ?>" value="<?= h($task[$field]) ?>"<?= $attrs ?>>
                <?php endif; ?>
                <?php if ($field === 'tags'): ?><small id="tag-help">Separate tags with commas. Up to 10 tags.</small><?php endif; ?>
                <?php if (isset($errors[$field])): ?><p id="error-<?= $field ?>"><?= h($errors[$field]) ?></p><?php endif; ?>
            </div>
        <?php endforeach; ?>
        <div class="row full"><button type="submit">Save Task</button><a href="tasks.php">Cancel</a></div>
    </form>
</section>
<?php elseif ($mode === 'delete'): ?>
<section class="panel" aria-labelledby="delete-title">
    <h2 id="delete-title">Delete Task</h2>
    <p>Delete “<?= h($task['title']) ?>”? This cannot be undone.</p>
    <form method="post" action="tasks.php" class="row">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="action" value="delete">
        <button type="submit" class="danger">Delete Task</button>
        <a href="tasks.php">Cancel</a>
    </form>
</section>
<?php endif; ?>

<section class="panel" aria-labelledby="list-title">
    <h2 id="list-title">Task list</h2>
    <?php if ($filterError !== ''): ?><p class="error" role="alert"><?= h($filterError) ?></p><?php endif; ?>
    <form action="tasks.php" method="get" class="form-grid" role="search" aria-label="Search and filter tasks">
        <div class="full"><label for="search">Search by keyword</label><input type="search" id="search" name="q" maxlength="100" value="<?= h($filters['q']) ?>"></div>
        <div><label for="category">Category</label><input id="category" name="category" maxlength="50" value="<?= h($filters['category']) ?>"></div>
        <div><label for="priority">Priority</label><select id="priority" name="priority"><option value="">All priorities</option><?php options(PRIORITIES, $filters['priority']); ?></select></div>
        <div><label for="status">Status</label><select id="status" name="status"><option value="">All statuses</option><?php options(STATUSES, $filters['status']); ?></select></div>
        <div><label for="due_date">Due date</label><input id="due_date" name="due_date" type="date" value="<?= h($filters['due_date']) ?>"></div>
        <div class="row full"><button type="submit">Apply Search and Filters</button><a href="tasks.php">Clear filters</a></div>
    </form>
    <p role="status"><?= count($tasks) ?> task(s) shown.</p>
    <?php if ($tasks): ?>
        <div class="table-wrap" tabindex="0" role="region" aria-label="Results table, scrollable on small screens">
            <table class="task-table">
                <caption>Your tasks</caption>
                <thead><tr><th scope="col">Task</th><th scope="col">Due date</th><th scope="col">Priority</th><th scope="col">Status</th><th scope="col">Actions</th></tr></thead>
                <tbody>
                <?php foreach ($tasks as $row):
                    $rowId = (int) $row['id'];
                    $completed = $row['status'] === 'Completed';
                ?>
                    <tr>
                        <th scope="row"><?= h($row['title']) ?>
                            <?php foreach (['category', 'tags', 'description'] as $field): ?>
                                <?php if (!empty($row[$field])): ?><span class="task-detail"><?= h($row[$field]) ?></span><?php endif; ?>
                            <?php endforeach; ?>
                        </th>
                        <td><?= h($row['due_date']) ?>
                            <?php if (!$completed && $row['due_date'] < $today): ?><span class="overdue">Overdue</span><?php endif; ?>
                        </td>
                        <td><?= h($row['priority']) ?></td>
                        <td><?= h($row['status']) ?></td>
                        <td><div class="row">
                            <a href="tasks.php?mode=edit&amp;id=<?= $rowId ?>#task-form" aria-label="Edit task: <?= h($row['title']) ?>">Edit</a>
                            <a href="tasks.php?mode=delete&amp;id=<?= $rowId ?>#delete-title" aria-label="Delete task: <?= h($row['title']) ?>">Delete</a>
                            <form method="post" action="tasks.php">
                                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                                <input type="hidden" name="id" value="<?= $rowId ?>">
                                <button class="secondary" type="submit" name="action" value="<?= $completed ? 'reopen' : 'complete' ?>"
                                    aria-label="<?= $completed ? 'Reopen' : 'Complete' ?> task: <?= h($row['title']) ?>"><?= $completed ? 'Reopen' : 'Complete' ?></button>
                            </form>
                        </div></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php elseif ($filterError === ''): ?><p>No tasks match. Clear your filters or add a task.</p><?php endif; ?>
</section>
<?php require __DIR__ . '/../includes/workspace-end.php'; ?>
