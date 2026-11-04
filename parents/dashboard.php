<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('parent');

require_once __DIR__ . '/../config/database.php';

// Load this parent's own record, using the ID stored in the session
$stmt = $pdo->prepare('SELECT full_name, email, role, created_at FROM users WHERE id = :id');
$stmt->execute(['id' => $_SESSION['user_id']]);
$parent = $stmt->fetch();

// If the account no longer exists, end the session
if (!$parent) {
    header('Location: ' . BASE_URL . '/logout.php');
    exit;
}

$firstName   = explode(' ', trim($parent['full_name']))[0];
$memberSince = date('j F Y', strtotime($parent['created_at']));

// SAMPLE DATA: linking parents to real students needs extra database tables
$overview = [
    ['label' => 'Children',    'value' => '1'],
    ['label' => 'Attendance',  'value' => '96%'],
    ['label' => 'Next exam',   'value' => '23 Nov'],
    ['label' => 'Term ends',   'value' => '4 Dec'],
];

$child = [
    'Name'          => 'Amani Njoroge',
    'Class'         => 'Grade 8 West',
    'Admission no.' => 'CA-2026-014',
    'Class teacher' => 'Ms. Wanjiru',
    'Attendance'    => '96% this term',
];

$announcements = [
    ['date' => '3 November 2026',  'title' => 'Term 3 examinations',  'text' => 'End-of-term examinations begin on 23 November. Please ensure your child is well rested and on time.'],
    ['date' => '29 October 2026',  'title' => 'Sports day',           'text' => 'Parents are welcome to attend sports day on the school grounds.'],
    ['date' => '18 October 2026',  'title' => 'Parents meeting',      'text' => 'The end-of-term parents meeting will be held on 28 November at 10:00 AM.'],
];

$pageTitle = 'Parent Dashboard';
require __DIR__ . '/../includes/header.php';
?>

<div class="mx-auto max-w-5xl space-y-12">

    <!-- Welcome and overview -->
    <section id="overview" class="dashboard__section">
        <h2 class="text-xl font-semibold text-navy">Welcome back, <?= htmlspecialchars($firstName) ?></h2>
        <p class="mt-1 text-sm text-muted">Here is a summary of your child's school year.</p>

        <dl class="mt-6 grid grid-cols-2 gap-6 border-y border-line py-6 lg:grid-cols-4">
            <?php foreach ($overview as $item): ?>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-muted"><?= htmlspecialchars($item['label']) ?></dt>
                    <dd class="mt-1 text-2xl font-semibold text-navy"><?= htmlspecialchars($item['value']) ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>
        <p class="mt-2 text-xs text-muted">Sample data for demonstration.</p>
    </section>

    <!-- Profile (real data from the database) -->
    <section id="profile" class="dashboard__section">
        <h2 class="text-lg font-semibold text-navy">My profile</h2>

        <dl class="mt-4 border-t border-line">
            <div class="flex flex-col gap-1 border-b border-line py-3 sm:flex-row sm:gap-0">
                <dt class="text-sm text-muted sm:w-48">Full name</dt>
                <dd class="text-sm font-medium"><?= htmlspecialchars($parent['full_name']) ?></dd>
            </div>
            <div class="flex flex-col gap-1 border-b border-line py-3 sm:flex-row sm:gap-0">
                <dt class="text-sm text-muted sm:w-48">Email address</dt>
                <dd class="break-all text-sm font-medium"><?= htmlspecialchars($parent['email']) ?></dd>
            </div>
            <div class="flex flex-col gap-1 border-b border-line py-3 sm:flex-row sm:gap-0">
                <dt class="text-sm text-muted sm:w-48">Role</dt>
                <dd class="text-sm font-medium capitalize"><?= htmlspecialchars($parent['role']) ?></dd>
            </div>
            <div class="flex flex-col gap-1 border-b border-line py-3 sm:flex-row sm:gap-0">
                <dt class="text-sm text-muted sm:w-48">Account created</dt>
                <dd class="text-sm font-medium"><?= htmlspecialchars($memberSince) ?></dd>
            </div>
        </dl>
    </section>

    <!-- Child information (sample data) -->
    <section id="child" class="dashboard__section">
        <h2 class="text-lg font-semibold text-navy">My child</h2>
        <p class="mt-1 text-xs text-muted">Sample information for demonstration. It is not linked to a real student account.</p>

        <dl class="mt-4 border-t border-line">
            <?php foreach ($child as $label => $value): ?>
                <div class="flex flex-col gap-1 border-b border-line py-3 sm:flex-row sm:gap-0">
                    <dt class="text-sm text-muted sm:w-48"><?= htmlspecialchars($label) ?></dt>
                    <dd class="text-sm font-medium"><?= htmlspecialchars($value) ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>
    </section>

    <!-- Announcements (sample data) -->
    <section id="announcements" class="dashboard__section">
        <h2 class="text-lg font-semibold text-navy">Announcements</h2>
        <p class="mt-1 text-xs text-muted">Sample data for demonstration.</p>

        <?php if (empty($announcements)): ?>
            <p class="mt-4 text-sm text-muted">There are no announcements right now.</p>
        <?php else: ?>
            <ul class="mt-4 divide-y divide-line border-y border-line">
                <?php foreach ($announcements as $note): ?>
                    <li class="py-4">
                        <p class="text-xs text-muted"><?= htmlspecialchars($note['date']) ?></p>
                        <p class="mt-1 text-sm font-semibold"><?= htmlspecialchars($note['title']) ?></p>
                        <p class="mt-1 text-sm text-muted"><?= htmlspecialchars($note['text']) ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>