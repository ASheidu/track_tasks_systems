<?php
const PRIORITIES = ['Low', 'Medium', 'High'];
const STATUSES = ['To-Do', 'In Progress', 'Completed'];

function input(array $source, string $key, string $default = ''): string
{
    $value = $source[$key] ?? $default;
    return is_string($value) ? trim($value) : '';
}

function taskDateIsValid(string $date): bool
{
    if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $date)) {
        return false;
    }
    [$year, $month, $day] = array_map('intval', explode('-', $date));
    return $year >= 1000 && checkdate($month, $day, $year);
}

function taskErrors(array $task, array $tags): array
{
    $errors = [];
    if ($task['title'] === '' || mb_strlen($task['title']) > 100) {
        $errors['title'] = 'Enter a title of 1–100 characters.';
    }
    if (mb_strlen($task['description']) > 1000) {
        $errors['description'] = 'Use no more than 1,000 characters.';
    }
    if (!taskDateIsValid($task['due_date'])) {
        $errors['due_date'] = 'Enter a valid due date.';
    }
    if (!in_array($task['priority'], PRIORITIES, true)) {
        $errors['priority'] = 'Choose a listed priority.';
    }
    if (!in_array($task['status'], STATUSES, true)) {
        $errors['status'] = 'Choose a listed status.';
    }
    if (mb_strlen($task['category']) > 50) {
        $errors['category'] = 'Use no more than 50 characters.';
    }
    if (count($tags) > 10) {
        $errors['tags'] = 'Use up to 10 tags.';
    }
    foreach ($tags as $tag) {
        if (mb_strlen($tag) > 30) {
            $errors['tags'] = 'Use no more than 30 characters per tag.';
        }
    }
    return $errors;
}

function parseTaskTags(string $text): array
{
    $tags = [];
    foreach (explode(',', $text) as $tag) {
        $tag = trim($tag);
        if ($tag !== '') {
            $tags[mb_strtolower($tag)] = $tag;
        }
    }
    return array_values($tags);
}

function findTask(PDO $db, int $userId, int $taskId): array
{
    $query = $db->prepare('SELECT * FROM tasks WHERE user_id = ? AND id = ?');
    $query->execute([$userId, $taskId]);
    $task = $query->fetch();
    if (!$task) {
        http_response_code(404);
        exit('Task not found. Return to My Tasks and try again.');
    }
    $query = $db->prepare('SELECT t.name FROM task_tags tt JOIN tags t
        ON tt.tag_id = t.id AND tt.user_id = t.user_id
        WHERE tt.user_id = ? AND tt.task_id = ? ORDER BY t.name');
    $query->execute([$userId, $taskId]);
    $task['tags'] = implode(', ', $query->fetchAll(PDO::FETCH_COLUMN));
    return $task;
}

function saveTask(PDO $db, int $userId, int $taskId, array $task, array $tags): void
{
    $values = [$task['title'], $task['description'], $task['due_date'],
        $task['priority'], $task['status'], $task['category']];
    // Save the task and all of its tag links together.
    $db->beginTransaction();
    try {
        if ($taskId) {
            $lock = $db->prepare('SELECT id FROM tasks WHERE user_id = ? AND id = ? FOR UPDATE');
            $lock->execute([$userId, $taskId]);
            if (!$lock->fetchColumn()) {
                $db->rollBack();
                http_response_code(404);
                exit('Task not found.');
            }
            $query = $db->prepare('UPDATE tasks SET title = ?, description = ?, due_date = ?,
                priority = ?, status = ?, category = ? WHERE user_id = ? AND id = ?');
            $query->execute(array_merge($values, [$userId, $taskId]));
            $db->prepare('DELETE FROM task_tags WHERE user_id = ? AND task_id = ?')
                ->execute([$userId, $taskId]);
        } else {
            $query = $db->prepare('INSERT INTO tasks
                (title, description, due_date, priority, status, category, user_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)');
            $query->execute(array_merge($values, [$userId]));
            $taskId = (int) $db->lastInsertId();
        }
        $upsert = $db->prepare('INSERT INTO tags (user_id, name) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)');
        $link = $db->prepare('INSERT INTO task_tags (user_id, task_id, tag_id)
            VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE tag_id = tag_id');
        foreach ($tags as $tag) {
            $upsert->execute([$userId, $tag]);
            $link->execute([$userId, $taskId, (int) $db->lastInsertId()]);
        }
        $db->commit();
    } catch (Throwable $error) {
        $db->rollBack();
        throw $error;
    }
}

function listTasks(PDO $db, int $userId, array $filters): array
{
    $sql = 'SELECT t.*, (SELECT GROUP_CONCAT(g.name ORDER BY g.name SEPARATOR \', \')
        FROM task_tags tt JOIN tags g ON g.id = tt.tag_id AND g.user_id = tt.user_id
        WHERE tt.user_id = t.user_id AND tt.task_id = t.id) AS tags
        FROM tasks t WHERE t.user_id = ?';
    $values = [$userId];

    if ($filters['q'] !== '') {
        // LOCATE treats the keyword as text rather than a SQL wildcard.
        $sql .= ' AND (LOCATE(?, t.title) > 0 OR LOCATE(?, t.description) > 0
            OR LOCATE(?, t.category) > 0 OR EXISTS (
                SELECT 1 FROM task_tags tt JOIN tags g
                ON g.id = tt.tag_id AND g.user_id = tt.user_id
                WHERE tt.user_id = t.user_id AND tt.task_id = t.id AND LOCATE(?, g.name) > 0))';
        for ($i = 0; $i < 4; $i++) {
            $values[] = $filters['q'];
        }
    }

    // The column names come from this fixed list, never from user input.
    foreach (['category', 'priority', 'status', 'due_date'] as $field) {
        if ($filters[$field] !== '') {
            $sql .= " AND t.$field = ?";
            $values[] = $filters[$field];
        }
    }
    $sql .= ' ORDER BY t.due_date, t.id DESC';
    $query = $db->prepare($sql);
    $query->execute($values);
    return $query->fetchAll();
}
