<?php
require __DIR__ . '/../includes/bootstrap.php';
if (!empty($_SESSION['user_id'])) {
    requireLogin();
    redirect('dashboard.php');
}
redirect('login.php');
