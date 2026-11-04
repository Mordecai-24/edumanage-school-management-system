<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

require_once __DIR__ . '/../config/database.php';

// Count the users in each role (one query, grouped by role)
$counts = ['student' => 0, 'instructor' => 0, 'parent' => 0, 'admin' => 0];

foreach ($pdo->query('SELECT role, COUNT(*) AS total FROM users GROUP BY role') as $row) {
    $counts[$row['role']] = (int) $row['total'];
}

$totalUsers = array_sum($counts);

$overview = [
    ['label' => 'Students',    'value' => $counts['student']],
    ['label' => 'Instructors', 'value' => $counts['instructor']],
    ['label' => 'Parents',     'value' => $counts['parent']],
    ['label' => 'Total users', 'value' => $totalUsers],
];

// The five newest accounts
$recentUsers = $pdo->query(
    'SELECT full_name, email, role, created_at
     FROM users
     ORDER BY created_at DESC, id DESC
     LIMIT 5'
)->fetchAll();

// All accounts (capped at 100 for now)
$allUsers = $pdo->query(
    'SELECT id, full_name, email, role, created_at
     FROM users
     ORDER BY id ASC
     LIMIT 100'
)->fetchAll();

$pageTitle = 'Admin Dashboard';
require __DIR__ . '/../includes/header.php';
?>

<div class="mx-auto max-w-5xl space-y-12">

    <!-- Welcome and system overview -->
    <section id="overview" class="dashboard__section">
        <h2 class="text-xl font-semibold text-navy">Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?></h2>
        <p class="mt-1 text-sm text-muted">An overview of everyone on the EduManage system.</p>

        <dl class="mt-6 grid grid-cols-2 gap-6 border-y border-line py-6 lg:grid-cols-4">
            <?php foreach ($overview as $item): ?>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-muted"><?= htmlspecialchars($item['label']) ?></dt>
                    <dd class="mt-1 text-2xl font-semibold text-navy"><?= (int) $item['value'] ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>
    </section>

    <!-- Recent users -->
    <section id="recent" class="dashboard__section">
        <h2 class="text-lg font-semibold text-navy">Recent users</h2>
        <p class="mt-1 text-sm text-muted">The five most recently created accounts.</p>

        <ul class="mt-4 divide-y divide-line border-y border-line">
            <?php foreach ($recentUsers as $user): ?>
                <li class="flex flex-col gap-1 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <p class="text-sm font-medium"><?= htmlspecialchars($user['full_name']) ?></p>
                        <p class="break-all text-sm text-muted"><?= htmlspecialchars($user['email']) ?></p>
                    </div>
                    <p class="text-sm text-muted">
                        <span class="capitalize"><?= htmlspecialchars($user['role']) ?></span>
                        &middot; <?= htmlspecialchars(date('j M Y', strtotime($user['created_at']))) ?>
                    </p>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <!-- All users -->
    <section id="users" class="dashboard__section">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-navy">Users</h2>
                <p class="mt-1 text-sm text-muted">Every account registered on the system.</p>
            </div>

            <button
                type="button"
                id="open-users-modal"
                class="w-full rounded bg-navy px-4 py-2.5 text-sm font-semibold text-white hover:bg-navy/90 focus:outline-none focus:ring-2 focus:ring-gold focus:ring-offset-2 sm:w-auto">
                Add user
            </button>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[40rem] text-left text-sm">
                <thead class="border-y border-line text-xs uppercase tracking-wide text-muted">
                    <tr>
                        <th class="py-3 pr-4 font-medium">Name</th>
                        <th class="py-3 pr-4 font-medium">Email</th>
                        <th class="py-3 pr-4 font-medium">Role</th>
                        <th class="py-3 pr-4 font-medium">Registered</th>
                        <th class="py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line border-b border-line">
                    <?php foreach ($allUsers as $user): ?>
                        <?php
                        // Admin accounts and your own account are protected
                        $canDelete = $user['role'] !== 'admin' && (int) $user['id'] !== (int) $_SESSION['user_id'];
                        ?>
                        <tr>
                            <td class="py-3 pr-4 font-medium"><?= htmlspecialchars($user['full_name']) ?></td>
                            <td class="py-3 pr-4"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="py-3 pr-4 capitalize"><?= htmlspecialchars($user['role']) ?></td>
                            <td class="py-3 pr-4 text-muted"><?= htmlspecialchars(date('j M Y', strtotime($user['created_at']))) ?></td>
                            <td class="py-3 text-right">
                                <?php if ($canDelete): ?>
                                    <button
                                        type="button"
                                        data-delete-user
                                        data-user-id="<?= (int) $user['id'] ?>"
                                        data-user-name="<?= htmlspecialchars($user['full_name']) ?>"
                                        class="text-sm font-medium text-red-700 hover:underline">
                                        Delete
                                    </button>
                                <?php else: ?>
                                    <span class="text-xs text-muted">Protected</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

</div>

<!-- Users Modal: add a new student, instructor or parent -->
<dialog
    id="users-modal"
    class="users-modal w-full max-w-md rounded border border-line bg-white p-0 text-ink backdrop:bg-black/40"
    aria-labelledby="users-modal-title">
    <div class="p-6">

        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 id="users-modal-title" class="text-lg font-semibold text-navy">Add a user</h2>
                <p class="mt-1 text-sm text-muted">Create an account for a student, instructor or parent.</p>
            </div>
            <button type="button" data-close-modal class="-mr-2 -mt-2 p-2 text-xl leading-none text-muted hover:text-navy" aria-label="Close">&times;</button>
        </div>

        <!-- Success and error messages appear here -->
        <div id="add-user-message" class="users-modal__message mt-4 hidden rounded border px-4 py-3 text-sm" role="alert"></div>

        <form id="add-user-form" class="users-modal__form mt-6 space-y-5" novalidate data-endpoint="<?= BASE_URL ?>/api/add-user.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

            <div>
                <label for="new-full-name" class="block text-sm font-medium text-ink">Full name</label>
                <input
                    type="text"
                    id="new-full-name"
                    name="full_name"
                    required
                    maxlength="100"
                    class="mt-1 block w-full rounded border border-line px-3 py-2.5 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label for="new-email" class="block text-sm font-medium text-ink">Email address</label>
                <input
                    type="email"
                    id="new-email"
                    name="email"
                    required
                    maxlength="255"
                    autocomplete="off"
                    class="mt-1 block w-full rounded border border-line px-3 py-2.5 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
            </div>

            <div>
                <label for="new-password" class="block text-sm font-medium text-ink">Temporary password</label>
                <div class="auth__password relative mt-1">
                    <input
                        type="password"
                        id="new-password"
                        name="password"
                        required
                        minlength="8"
                        autocomplete="new-password"
                        class="block w-full rounded border border-line px-3 py-2.5 pr-16 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    <button
                        type="button"
                        class="auth__toggle absolute inset-y-0 right-0 px-3 text-sm font-medium text-muted hover:text-navy"
                        aria-label="Show password">
                        Show
                    </button>
                </div>
                <p class="mt-1 text-xs text-muted">At least 8 characters. Share it with the user securely.</p>
            </div>

            <div>
                <label for="new-role" class="block text-sm font-medium text-ink">Role</label>
                <select
                    id="new-role"
                    name="role"
                    required
                    class="mt-1 block w-full rounded border border-line bg-white px-3 py-2.5 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">Select a role</option>
                    <option value="student">Student</option>
                    <option value="instructor">Instructor</option>
                    <option value="parent">Parent</option>
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button
                    type="button"
                    data-close-modal
                    class="rounded border border-line px-4 py-2.5 text-sm font-medium text-ink hover:bg-surface">
                    Cancel
                </button>
                <button
                    type="submit"
                    class="rounded bg-navy px-4 py-2.5 text-sm font-semibold text-white hover:bg-navy/90 focus:outline-none focus:ring-2 focus:ring-gold focus:ring-offset-2">
                    Add user
                </button>
            </div>

        </form>
    </div>
</dialog>

<!-- Delete confirmation -->
<dialog
    id="delete-modal"
    class="delete-modal w-full max-w-sm rounded border border-line bg-white p-0 text-ink backdrop:bg-black/40"
    aria-labelledby="delete-modal-title">
    <div class="p-6">

        <h2 id="delete-modal-title" class="text-lg font-semibold text-navy">Delete user</h2>
        <p class="mt-2 text-sm text-muted">
            Are you sure you want to delete <span id="delete-user-name" class="font-medium text-ink"></span>?
            This cannot be undone.
        </p>

        <div id="delete-user-message" class="delete-modal__message mt-4 hidden rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert"></div>

        <form id="delete-user-form" class="delete-modal__form mt-6" data-endpoint="<?= BASE_URL ?>/api/delete-user.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
            <input type="hidden" name="user_id" id="delete-user-id" value="">

            <div class="flex justify-end gap-3">
                <button
                    type="button"
                    data-close-modal
                    class="rounded border border-line px-4 py-2.5 text-sm font-medium text-ink hover:bg-surface">
                    Cancel
                </button>
                <button
                    type="submit"
                    class="rounded bg-red-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-700 focus:ring-offset-2">
                    Delete
                </button>
            </div>
        </form>

    </div>
</dialog>

<?php require __DIR__ . '/../includes/footer.php'; ?>