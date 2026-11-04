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

// 4. The user ID must be a whole number
$userId = filter_var($_POST['user_id'] ?? '', FILTER_VALIDATE_INT);

if ($userId === false || $userId < 1) {
    json_response(['success' => false, 'message' => 'Invalid user.'], 422);
}

// 5. Safety rule: an admin cannot delete their own account
if ($userId === (int) $_SESSION['user_id']) {
    json_response(['success' => false, 'message' => 'You cannot delete your own account.'], 403);
}

try {
    // 6. Find the user
    $stmt = $pdo->prepare('SELECT full_name, role FROM users WHERE id = :id');
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch();

    if (!$user) {
        json_response(['success' => false, 'message' => 'That user no longer exists.'], 404);
    }

    // 7. Safety rule: administrator accounts can never be deleted
    if ($user['role'] === 'admin') {
        json_response(['success' => false, 'message' => 'Administrator accounts cannot be deleted.'], 403);
    }

    // 8. Delete (the extra role check is a second safety net)
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id AND role <> :admin_role');
    $stmt->execute(['id' => $userId, 'admin_role' => 'admin']);

    json_response(['success' => true, 'message' => $user['full_name'] . ' was deleted.']);

} catch (PDOException $e) {
    error_log($e->getMessage());
    json_response(['success' => false, 'message' => 'Something went wrong on our side. Please try again later.'], 500);
}