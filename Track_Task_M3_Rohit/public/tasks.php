<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/tasks.php';

$userId = requireLogin();
$db = db();
$today = date('Y-m-d');
$pageTitle = 'My Tasks';
$activePage = 'tasks';
$pageCss = 'css/tasks.css';

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

// Saved categories belong only to the signed-in account.
$categoryQuery = $db->prepare('SELECT DISTINCT category FROM tasks
    WHERE user_id = ? AND category <> ? ORDER BY category');
$categoryQuery->execute([$userId, '']);
$categories = $categoryQuery->fetchAll(PDO::FETCH_COLUMN);
$hasAdvancedFilters = $filters['priority'] !== '' || $filters['due_date'] !== '';
$firstError = $errors ? array_key_first($errors) : '';

function options(array $choices, string $current): void
{
    foreach ($choices as $choice) {
        $selected = $choice === $current ? ' selected' : '';
        echo '<option value="' . h($choice) . '"' . $selected . '>' . h($choice) . '</option>';
    }
}

require __DIR__ . '/../includes/workspace.php';
?>
<div class="row split task-page-heading">
    <div><p class="eyebrow">YOUR WORKSPACE</p><h1>My Tasks</h1><p class="muted">Create, organize, and update your tasks.</p></div>
    <a class="button" href="tasks.php?mode=add#task-form">Add Task</a>
</div>
<?php if ($notice !== ''): ?>
    <p class="notice" role="status"><?= h($notice) ?></p>
<?php endif; ?>

<?php if ($mode === 'add' || $mode === 'edit'): ?>
<dialog open class="task-editor" id="task-form" aria-labelledby="form-title">
    <h2 id="form-title"><?= $mode === 'edit' ? 'Edit Task' : 'Add Task' ?></h2>
    <?php if ($errors): ?>
        <div class="error" role="alert">
            <strong>Check these fields:</strong>
            <ul><?php foreach ($errors as $field => $message): ?>
                <li><a href="#task-<?= h($field) ?>"><?= h($message) ?></a></li>
            <?php endforeach; ?></ul>
        </div>
    <?php endif; ?>
    <p class="muted">Fields marked “required” must be filled in.</p>
    <datalist id="saved-categories">
        <?php foreach ($categories as $category): ?><option value="<?= h($category) ?>"></option><?php endforeach; ?>
    </datalist>
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
                . (($firstError === $field || ($firstError === '' && $field === 'title')) ? ' autofocus' : '')
                . ($field === 'category' ? ' list="saved-categories"' : '')
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
</dialog>
<?php elseif ($mode === 'delete'): ?>
<dialog open class="task-editor" id="delete-dialog" aria-labelledby="delete-title">
    <h2 id="delete-title">Delete Task</h2>
    <p>Delete “<?= h($task['title']) ?>”? This cannot be undone.</p>
    <form method="post" action="tasks.php" class="row">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="action" value="delete">
        <button type="submit" class="danger">Delete Task</button>
        <a href="tasks.php">Cancel</a>
    </form>
</dialog>
<?php endif; ?>

<section class="task-filters" aria-label="Search and filter tasks">
    <?php if ($filterError !== ''): ?><p class="error" role="alert"><?= h($filterError) ?></p><?php endif; ?>
    <form action="tasks.php" method="get" role="search" aria-label="Search and filter tasks">
        <div class="filter-toolbar">
            <div class="keyword"><label for="search">Search tasks</label><input type="search" id="search" name="q" maxlength="100" placeholder="Search titles, descriptions, or tags" value="<?= h($filters['q']) ?>"></div>
            <div><label for="category">Category</label><select id="category" name="category">
                <option value="">All categories</option>
                <?php if ($filters['category'] !== '' && !in_array($filters['category'], $categories, true)): ?>
                    <option selected value="<?= h($filters['category']) ?>"><?= h($filters['category']) ?></option>
                <?php endif; ?>
                <?php options($categories, $filters['category']); ?>
            </select></div>
            <div><label for="status">Status</label><select id="status" name="status"><option value="">All statuses</option><?php options(STATUSES, $filters['status']); ?></select></div>
            <button type="submit">Apply</button>
            <a class="clear-link" href="tasks.php">Clear</a>
        </div>
        <details class="advanced-filters"<?= $hasAdvancedFilters ? ' open' : '' ?>>
            <summary>More filters<?= $hasAdvancedFilters ? ' (active)' : '' ?></summary>
            <div class="advanced-grid">
                <div><label for="priority">Priority</label><select id="priority" name="priority"><option value="">All priorities</option><?php options(PRIORITIES, $filters['priority']); ?></select></div>
                <div><label for="due_date">Due date</label><input id="due_date" name="due_date" type="date" value="<?= h($filters['due_date']) ?>"></div>
            </div>
        </details>
    </form>
</section>

<section class="panel task-list-panel" aria-labelledby="list-title">
    <div class="row split list-heading">
        <div><h2 id="list-title">Your tasks <span class="count-pill"><?= count($tasks) ?></span></h2>
        <p class="muted">Your work, one task at a time.</p></div>
        <p class="results-count" role="status"><?= count($tasks) ?> task(s) match your current filters.</p>
    </div>
    <?php if ($tasks): ?>
        <div class="table-wrap" tabindex="0" role="region" aria-label="Results table, scrollable on small screens">
            <table class="task-table">
                <caption class="visually-hidden">Your tasks with dates, priorities, statuses, and actions</caption>
                <thead><tr><th scope="col">Task</th><th scope="col">Due date</th><th scope="col">Priority</th><th scope="col">Status</th><th scope="col">Actions</th></tr></thead>
                <tbody>
                <?php foreach ($tasks as $row):
                    $rowId = (int) $row['id'];
                    $completed = $row['status'] === 'Completed';
                ?>
                    <tr class="<?= $completed ? 'is-completed' : '' ?>">
                        <th scope="row"><span class="task-name"><?= h($row['title']) ?></span>
                            <?php foreach (['category', 'tags', 'description'] as $field): ?>
                                <?php if (!empty($row[$field])): ?><span class="task-detail"><?= h($row[$field]) ?></span><?php endif; ?>
                            <?php endforeach; ?>
                        </th>
                        <td><?= h(date('m/d/Y', strtotime($row['due_date']))) ?>
                            <?php if (!$completed && $row['due_date'] < $today): ?><span class="overdue">Overdue</span><?php endif; ?>
                        </td>
                        <td><span class="task-badge priority-<?= strtolower($row['priority']) ?>"><?= h($row['priority']) ?></span></td>
                        <td><span class="task-badge status-<?= strtolower(str_replace(' ', '-', $row['status'])) ?>"><?= $completed ? '✓ ' : '' ?><?= h($row['status']) ?></span></td>
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
    <?php elseif ($filterError === ''): ?><div class="task-empty"><h3>No tasks to show</h3><p>Clear your filters or add your first task.</p><a class="button" href="tasks.php?mode=add#task-form">Add Task</a></div><?php endif; ?>
</section>
<script src="js/tasks-ui.js" defer></script>
<?php require __DIR__ . '/../includes/workspace-end.php'; ?>
