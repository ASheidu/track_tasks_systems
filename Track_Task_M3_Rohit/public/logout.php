<?php
// Track Task - Team CTRL - Milestone 3
// Secure Logout

require_once __DIR__ . '/../includes/bootstrap.php';

// Allow logout through POST requests only.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Logout requires a POST request.');
}

// Check CSRF security token.
checkCsrf();

// Destroy the authenticated session.
signOut();

// Redirect to the Login page.
redirect('login.php');
