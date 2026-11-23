<?php
// Set $pageTitle and $activePage before including this shared layout.
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($pageTitle) ?> | Track Task</title>
<link rel="stylesheet" href="css/theme.css">
<?php if (!empty($pageCss)): ?><link rel="stylesheet" href="<?= h($pageCss) ?>"><?php endif; ?>
</head>
<body class="workspace">
<a class="skip-link" href="#main">Skip to main content</a>
<aside class="sidebar">
    <a class="brand" href="dashboard.php">Track Task</a>
    <nav aria-label="Main navigation">
        <a href="dashboard.php"<?= $activePage === 'dashboard' ? ' aria-current="page"' : '' ?>>Dashboard</a>
        <a href="tasks.php"<?= $activePage === 'tasks' ? ' aria-current="page"' : '' ?>>My Tasks</a>
    </nav>
    <form action="logout.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <button type="submit" class="secondary">Log Out</button>
    </form>
</aside>
<div class="workspace-content">
    <header class="topbar">
        <span>Track Task / <?= h($pageTitle) ?></span>
        <strong><?= h($_SESSION['display_name'] ?? 'Track Task user') ?></strong>
    </header>
    <main id="main" tabindex="-1" class="content">
