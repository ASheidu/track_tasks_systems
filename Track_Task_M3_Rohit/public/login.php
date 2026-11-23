<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();

    $emailInput = $_POST['loginEmail'] ?? '';
    $password = $_POST['loginPassword'] ?? '';

    $email = is_string($emailInput)
        ? trim($emailInput)
        : '';

    if (!is_string($password)) {
        $password = '';
    }

    if (
        strlen($email) <= 254 &&
        filter_var($email, FILTER_VALIDATE_EMAIL) &&
        $password !== ''
    ) {
        try {
            $query = db()->prepare(
                'SELECT id, display_name, password_hash
                 FROM users
                 WHERE email = ?
                 LIMIT 1'
            );

            $query->execute([$email]);
            $user = $query->fetch();

            if (
                $user &&
                password_verify(
                    $password,
                    $user['password_hash']
                )
            ) {
                session_regenerate_id(true);

                $_SESSION = [
                    'user_id' => (int)$user['id'],
                    'display_name' => $user['display_name'],
                    'last_activity' => time()
                ];

                csrfToken();

                redirect('dashboard.php');
            }

        } catch (PDOException $exception) {
            error_log(
                'Login database error: ' .
                $exception->getCode()
            );

            $error =
                'Sign-in is temporarily unavailable. Please try again.';
        }
    }

    if ($error === '') {
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login | Track Task</title>

    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/account.css">

    <script src="js/account.js" defer></script>
</head>

<body class="account-page">

<a class="skip-link" href="#main">
    Skip to main content
</a>

<header class="account-header">
    <div class="account-header-inner">
        <a class="account-brand" href="login.php">
            Track Task
        </a>

        <p>Get things done, one task at a time.</p>
    </div>
</header>

<main id="main" class="account-layout">

    <aside class="account-intro">
        <h1>Welcome Back!</h1>

        <p>Sign in to manage your saved tasks.</p>

        <h2>Manage Your Tasks</h2>

        <ul>
            <li>Organize your daily tasks</li>
            <li>Set priorities and deadlines</li>
            <li>Track your progress</li>
        </ul>
    </aside>

    <section
        class="account-card"
        aria-labelledby="login-title"
    >

        <h2 id="login-title">Login</h2>

        <?php if (
            isset($_GET['registered']) &&
            $_GET['registered'] === '1'
        ): ?>

            <p class="notice" role="status">
                Your account was created.
                Please sign in.
            </p>

        <?php endif; ?>

        <?php if (
            isset($_GET['expired']) &&
            $_GET['expired'] === '1'
        ): ?>

            <p class="error" role="alert">
                Your session expired.
                Please sign in again.
            </p>

        <?php endif; ?>

        <?php if ($error): ?>

            <p class="error" role="alert">
                <?= h($error) ?>
            </p>

        <?php endif; ?>

        <form
            id="loginForm"
            method="POST"
            action="login.php"
            novalidate
        >

            <input
                type="hidden"
                name="csrf_token"
                value="<?= h(csrfToken()) ?>"
            >

            <div class="account-field">
                <label for="loginEmail">
                    Email Address *
                </label>

                <input
                    type="email"
                    id="loginEmail"
                    name="loginEmail"
                    maxlength="254"
                    autocomplete="email"
                    value="<?= h($email) ?>"
                    required
                >
            </div>

            <div class="account-field">
                <label for="loginPassword">
                    Password *
                </label>

                <input
                    type="password"
                    id="loginPassword"
                    name="loginPassword"
                    autocomplete="current-password"
                    required
                >

                <label class="password-toggle">
                    <input
                        type="checkbox"
                        data-show-password="loginPassword"
                    >
                    Show Password
                </label>
            </div>

            <div class="account-actions">
                <button type="submit">
                    Sign In
                </button>
            </div>

        </form>

        <p>
            Don't have an account?
            <a href="register.php">Register Here</a>
        </p>

    </section>
</main>

<footer class="account-footer">
    Track Task | CSCI 426 | Milestone 3
</footer>

</body>
</html>
