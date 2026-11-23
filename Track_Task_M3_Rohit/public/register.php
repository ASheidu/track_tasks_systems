<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$values = [
    'fullname' => '',
    'email' => '',
    'username' => ''
];

$errors = [];
$generalError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();

    foreach ($values as $field => $_) {
        $input = $_POST[$field] ?? '';

        $values[$field] = is_string($input)
            ? trim($input)
            : '';
    }

    $password = $_POST['pass'] ?? '';
    $confirmation = $_POST['pass2'] ?? '';

    if (!is_string($password)) {
        $password = '';
    }

    if (!is_string($confirmation)) {
        $confirmation = '';
    }

    // Full Name Validation
    $nameLength = function_exists('mb_strlen')
        ? mb_strlen($values['fullname'], 'UTF-8')
        : strlen($values['fullname']);

    if ($nameLength < 2 || $nameLength > 50) {
        $errors['fullname'] =
            'Full Name must be between 2 and 50 characters.';
    }

    // Email Validation
    if (
        strlen($values['email']) > 254 ||
        !filter_var(
            $values['email'],
            FILTER_VALIDATE_EMAIL
        )
    ) {
        $errors['email'] =
            'Enter a valid email address.';
    }

    // Username Validation
    if (
        !preg_match(
            '/\A[A-Za-z0-9_]{4,20}\z/',
            $values['username']
        )
    ) {
        $errors['username'] =
            'Username must be 4–20 letters, numbers, or underscores.';
    }

    // Password Validation
    if (
        !preg_match(
            '/\A(?=.*[A-Z])(?=.*[0-9])[\s\S]{8,64}\z/',
            $password
        )
    ) {
        $errors['pass'] =
            'Use 8–64 characters with a capital letter and a number.';
    }

    // Confirm Password Validation
    if (
        $confirmation === '' ||
        !hash_equals($password, $confirmation)
    ) {
        $errors['pass2'] =
            'Passwords must match.';
    }

    // Database Registration
    if (!$errors) {
        try {
            $pdo = db();

            // Check Duplicate Email
            $emailCheck = $pdo->prepare(
                'SELECT id
                 FROM users
                 WHERE email = ?
                 LIMIT 1'
            );

            $emailCheck->execute([
                $values['email']
            ]);

            if ($emailCheck->fetch()) {
                $errors['email'] =
                    'This email is already registered.';
            }

            // Check Duplicate Username
            $usernameCheck = $pdo->prepare(
                'SELECT id
                 FROM users
                 WHERE username = ?
                 LIMIT 1'
            );

            $usernameCheck->execute([
                $values['username']
            ]);

            if ($usernameCheck->fetch()) {
                $errors['username'] =
                    'This username is already taken.';
            }

            // Save Account if No Errors
            if (!$errors) {
                $hash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $save = $pdo->prepare(
                    'INSERT INTO users
                     (display_name, email, username, password_hash)
                     VALUES (?, ?, ?, ?)'
                );

                $save->execute([
                    $values['fullname'],
                    $values['email'],
                    $values['username'],
                    $hash
                ]);

                redirect('login.php?registered=1');
            }

        } catch (PDOException $exception) {

            if ($exception->getCode() === '23000') {
                $generalError =
                    'An account with that email or username already exists.';
            } else {
                error_log(
                    'Registration database error: ' .
                    $exception->getCode()
                );

                $generalError =
                    'Registration is temporarily unavailable. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Register | Track Task</title>

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

<main class="account-layout" id="main" tabindex="-1">

    <aside class="account-intro">

        <h1>Join Track Task!</h1>

        <p>
            Create your account and organize
            your tasks in one place.
        </p>

        <h2>Why Track Task?</h2>

        <ul>
            <li>Organize tasks and projects</li>
            <li>Set priorities and due dates</li>
            <li>Monitor your progress</li>
            <li>Manage your daily activities</li>
        </ul>

    </aside>

    <section
        class="account-card"
        aria-labelledby="register-title"
    >

        <h2 id="register-title">
            Registration Form
        </h2>

        <p class="muted">
            Fields marked * are required.
        </p>

        <?php if ($generalError): ?>

            <p class="error" role="alert">
                <?= h($generalError) ?>
            </p>

        <?php endif; ?>

        <form
            id="regForm"
            method="post"
            action="register.php"
            novalidate
        >

            <input
                type="hidden"
                name="csrf_token"
                value="<?= h(csrfToken()) ?>"
            >

            <!-- Basic Information -->
            <fieldset class="account-fieldset">

                <legend>Basic Information</legend>

                <div class="form-grid">

                    <!-- Full Name -->
                    <div class="account-field full">

                        <label for="fullname">
                            Full Name *
                        </label>

                        <input
                            id="fullname"
                            name="fullname"
                            type="text"
                            minlength="2"
                            maxlength="50"
                            required
                            autocomplete="name"
                            value="<?= h($values['fullname']) ?>"
                            aria-describedby="name-help<?= isset($errors['fullname']) ? ' name-error' : '' ?>"
                            <?= isset($errors['fullname']) ? 'aria-invalid="true"' : '' ?>
                        >

                        <small id="name-help">
                            Enter 2–50 characters.
                        </small>

                        <?php if (isset($errors['fullname'])): ?>

                            <p
                                id="name-error"
                                class="error"
                                role="alert"
                            >
                                <?= h($errors['fullname']) ?>
                            </p>

                        <?php endif; ?>

                    </div>

                    <!-- Email -->
                    <div class="account-field">

                        <label for="email">
                            Email Address *
                        </label>

                        <input
                            id="email"
                            name="email"
                            type="email"
                            maxlength="254"
                            required
                            autocomplete="email"
                            value="<?= h($values['email']) ?>"
                            <?= isset($errors['email']) ? 'aria-invalid="true" aria-describedby="email-error"' : '' ?>
                        >

                        <?php if (isset($errors['email'])): ?>

                            <p
                                id="email-error"
                                class="error"
                                role="alert"
                            >
                                <?= h($errors['email']) ?>
                            </p>

                        <?php endif; ?>

                    </div>

                    <!-- Username -->
                    <div class="account-field">

                        <label for="username">
                            Username *
                        </label>

                        <input
                            id="username"
                            name="username"
                            type="text"
                            minlength="4"
                            maxlength="20"
                            pattern="[A-Za-z0-9_]{4,20}"
                            required
                            autocomplete="username"
                            value="<?= h($values['username']) ?>"
                            aria-describedby="username-help<?= isset($errors['username']) ? ' username-error' : '' ?>"
                            <?= isset($errors['username']) ? 'aria-invalid="true"' : '' ?>
                        >

                        <small id="username-help">
                            4–20 letters, numbers,
                            or underscores.
                        </small>

                        <?php if (isset($errors['username'])): ?>

                            <p
                                id="username-error"
                                class="error"
                                role="alert"
                            >
                                <?= h($errors['username']) ?>
                            </p>

                        <?php endif; ?>

                    </div>

                </div>

            </fieldset>

            <!-- Password Information -->
            <fieldset class="account-fieldset">

                <legend>Password Information</legend>

                <div class="form-grid">

                    <!-- Password -->
                    <div class="account-field">

                        <label for="pass">
                            Password *
                        </label>

                        <input
                            id="pass"
                            name="pass"
                            type="password"
                            minlength="8"
                            maxlength="64"
                            required
                            autocomplete="new-password"
                            aria-describedby="pass-help password-strength<?= isset($errors['pass']) ? ' pass-error' : '' ?>"
                            <?= isset($errors['pass']) ? 'aria-invalid="true"' : '' ?>
                        >

                        <small id="pass-help">
                            8–64 characters with a
                            capital letter and a number.
                        </small>

                        <small
                            id="password-strength"
                            role="status"
                            aria-live="polite"
                        ></small>

                        <label class="password-toggle">

                            <input
                                type="checkbox"
                                data-show-password="pass"
                            >

                            Show Password

                        </label>

                        <?php if (isset($errors['pass'])): ?>

                            <p
                                id="pass-error"
                                class="error"
                                role="alert"
                            >
                                <?= h($errors['pass']) ?>
                            </p>

                        <?php endif; ?>

                    </div>

                    <!-- Confirm Password -->
                    <div class="account-field">

                        <label for="pass2">
                            Confirm Password *
                        </label>

                        <input
                            id="pass2"
                            name="pass2"
                            type="password"
                            minlength="8"
                            maxlength="64"
                            required
                            autocomplete="new-password"
                            <?= isset($errors['pass2']) ? 'aria-invalid="true" aria-describedby="pass2-error"' : '' ?>
                        >

                        <label class="password-toggle">

                            <input
                                type="checkbox"
                                data-show-password="pass2"
                            >

                            Show Password

                        </label>

                        <?php if (isset($errors['pass2'])): ?>

                            <p
                                id="pass2-error"
                                class="error"
                                role="alert"
                            >
                                <?= h($errors['pass2']) ?>
                            </p>

                        <?php endif; ?>

                    </div>

                </div>

            </fieldset>

            <!-- Registration Buttons -->
            <div class="account-actions">

                <button type="submit">
                    Create Account
                </button>

                <button
                    type="reset"
                    class="secondary"
                >
                    Reset
                </button>

            </div>

        </form>

        <p>
            Already have an account?
            <a href="login.php">Login Here</a>
        </p>

    </section>

</main>

<footer class="account-footer">
    Track Task | CSCI 426 | Milestone 3
</footer>

</body>
</html>
