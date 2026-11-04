<?php
// includes/auth.php: shared session and role checks for protected pages

// The folder this project lives in. Change it here if the project ever moves.
const BASE_URL = '/school-management-system';

// Start the session the same way on every page
function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'httponly' => true,   // JavaScript cannot read the session cookie
            'samesite' => 'Lax',  // the cookie is not sent with most cross-site requests
        ]);
        session_start();
    }
}

// Where each role's dashboard lives
function dashboard_path(string $role): string
{
    $dashboards = [
        'admin'      => '/admin/dashboard.php',
        'instructor' => '/instructors/dashboard.php',
        'parent'     => '/parents/dashboard.php',
        'student'    => '/students/dashboard.php',
    ];

    return BASE_URL . ($dashboards[$role] ?? '/login.php');
}

// Call this at the top of a protected page. Only the given role may stay.
function require_role(string $role): void
{
    start_secure_session();

    // Tell the browser never to keep a copy of protected pages
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');

    // Not logged in: send them to the login page
    if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }

    // Logged in, but the wrong role: send them to their own dashboard
    if ($_SESSION['role'] !== $role) {
        header('Location: ' . dashboard_path($_SESSION['role']));
        exit;
    }
}

// Call this on the login and register pages: logged-in users don't need them
function redirect_if_logged_in(): void
{
    start_secure_session();

    if (!empty($_SESSION['user_id']) && !empty($_SESSION['role'])) {
        header('Location: ' . dashboard_path($_SESSION['role']));
        exit;
    }
}

// ---------- Helpers for API endpoints (they reply with JSON, not redirects) ----------

// Send a JSON reply and stop
function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data);
    exit;
}

// Like require_role(), but for endpoints: replies with an error instead of redirecting
function require_role_api(string $role): void
{
    start_secure_session();

    if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
        json_response(['success' => false, 'message' => 'Please log in again.'], 401);
    }

    if ($_SESSION['role'] !== $role) {
        json_response(['success' => false, 'message' => 'You do not have permission to do this.'], 403);
    }
}

// CSRF protection: a secret token kept in the session and sent back with each admin request
function csrf_token(): string
{
    start_secure_session();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token): bool
{
    start_secure_session();

    return !empty($_SESSION['csrf_token'])
        && is_string($token)
        && hash_equals($_SESSION['csrf_token'], $token);
}