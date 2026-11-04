<?php
require_once __DIR__ . '/includes/auth.php';

start_secure_session();

// 1. Empty the session data
$_SESSION = [];

// 2. Delete the session cookie from the browser
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires'  => time() - 42000,
        'path'     => $params['path'],
        'domain'   => $params['domain'],
        'secure'   => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => $params['samesite'] ?? 'Lax',
    ]);
}

// 3. Destroy the session on the server
session_destroy();

// Back to the login page, with a message
header('Location: ' . BASE_URL . '/login.php?loggedout=1');
exit;