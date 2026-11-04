<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

// 1. Only a logged-in admin may use this endpoint
require_role_api('admin');

// 2. Only POST requests are accepted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Invalid request method.'], 405);
}

// 3. The request must carry the secret token from the admin's own page
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    json_response(['success' => false, 'message' => 'Your session has expired. Refresh the page and try again.'], 403);
}

// 4. Read the submitted values
$fullName = trim($_POST['full_name'] ?? '');
$email    = mb_strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
$role     = $_POST['role'] ?? '';

// 5. Validate on the server (the browser's checks can be bypassed)
$errors = [];

if ($fullName === '') {
    $errors[] = 'Please enter the full name.';
} elseif (mb_strlen($fullName) > 100) {
    $errors[] = 'The full name must be 100 characters or fewer.';
}

if ($email === '') {
    $errors[] = 'Please enter an email address.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
} elseif (mb_strlen($email) > 255) {
    $errors[] = 'The email address is too long.';
}

if (strlen($password) < 8) {
    $errors[] = 'The password must be at least 8 characters long.';
} elseif (strlen($password) > 72) {
    $errors[] = 'The password must be 72 characters or fewer.';
}

// Only these roles may be created here. "admin" is deliberately not on the list.
$allowedRoles = ['student', 'instructor', 'parent'];

if (!in_array($role, $allowedRoles, true)) {
    $errors[] = 'Please choose a role: student, instructor or parent.';
}

if (!empty($errors)) {
    json_response(['success' => false, 'message' => 'Please fix the following.', 'errors' => $errors], 422);
}

// 6. Check for a duplicate email, then save
try {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);

    if ($stmt->fetch()) {
        json_response(['success' => false, 'message' => 'An account with this email already exists.', 'errors' => ['An account with this email already exists.']], 409);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO users (full_name, email, password, role)
         VALUES (:full_name, :email, :password, :role)'
    );
    $stmt->execute([
        'full_name' => $fullName,
        'email'     => $email,
        'password'  => password_hash($password, PASSWORD_DEFAULT),
        'role'      => $role,
    ]);

    json_response([
        'success' => true,
        'message' => ucfirst($role) . ' account created for ' . $fullName . '.',
    ], 201);

} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        // The database's unique rule caught a duplicate email
        json_response(['success' => false, 'message' => 'An account with this email already exists.', 'errors' => ['An account with this email already exists.']], 409);
    }

    error_log($e->getMessage());
    json_response(['success' => false, 'message' => 'Something went wrong on our side. Please try again later.'], 500);
}