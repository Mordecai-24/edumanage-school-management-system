<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('instructor');

require_once __DIR__ . '/../config/database.php';

// Load this instructor's own record, using the ID stored in the session
$stmt = $pdo->prepare('SELECT full_name, email, role, created_at FROM users WHERE id = :id');
$stmt->execute(['id' => $_SESSION['user_id']]);
$instructor = $stmt->fetch();

// If the account no longer exists, end the session
if (!$instructor) {
    header('Location: ' . BASE_URL . '/logout.php');
    exit;
}

$firstName   = explode(' ', trim($instructor['full_name']))[0];
$memberSince = date('j F Y', strtotime($instructor['created_at']));

// SAMPLE DATA: replace with real records if academic tables are added later
$overview = [
    ['label' => 'Assigned classes', 'value' => '4'],
    ['label' => 'Students',         'value' => '134'],
    ['label' => 'Subjects',         'value' => '1'],
    ['label' => 'Next class',       'value' => 'Mon, 8:00 AM'],
];

$classes = [
    ['class' => 'Grade 7 East',  'subject' => 'Mathematics', 'students' => 34, 'schedule' => 'Mon and Wed, 8:00 AM'],
    ['class' => 'Grade 8 West',  'subject' => 'Mathematics', 'students' => 31, 'schedule' => 'Tue and Thu, 8:00 AM'],
    ['class' => 'Grade 9 North', 'subject' => 'Mathematics', 'students' => 36, 'schedule' => 'Mon and Fri, 11:30 AM'],
    ['class' => 'Grade 9 South', 'subject' => 'Mathematics', 'students' => 33, 'schedule' => 'Wed and Fri, 2:00 PM'],
];

$announcements = [
    ['date' => '3 November 2026',  'title' => 'Term 3 examinations',    'text' => 'Submit your examination papers to the academic office by 12 November.'],
    ['date' => '30 October 2026',  'title' => 'Staff meeting',          'text' => 'A staff meeting will be held on Friday at 4:00 PM in the staff room.'],
    ['date' => '21 October 2026',  'title' => 'Library hours extended', 'text' => 'The library now stays open until 5:00 PM on weekdays.'],
];

$pageTitle = 'Instructor Dashboard';
require __DIR__ . '/../includes/header.php';
?>

<div class="mx-auto max-w-5xl space-y-12">

    <!-- Welcome and overview -->
    <section id="overview" class="dashboard__section">
        <h2 class="text-xl font-semibold text-navy">Welcome back, <?= htmlspecialchars($firstName) ?></h2>
        <p class="mt-1 text-sm text-muted">Here is a summary of your teaching account.</p>

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
                <dd class="text-sm font-medium"><?= htmlspecialchars($instructor['full_name']) ?></dd>
            </div>
            <div class="flex flex-col gap-1 border-b border-line py-3 sm:flex-row sm:gap-0">
                <dt class="text-sm text-muted sm:w-48">Email address</dt>
                <dd class="break-all text-sm font-medium"><?= htmlspecialchars($instructor['email']) ?></dd>
            </div>
            <div class="flex flex-col gap-1 border-b border-line py-3 sm:flex-row sm:gap-0">
                <dt class="text-sm text-muted sm:w-48">Role</dt>
                <dd class="text-sm font-medium capitalize"><?= htmlspecialchars($instructor['role']) ?></dd>
            </div>
            <div class="flex flex-col gap-1 border-b border-line py-3 sm:flex-row sm:gap-0">
                <dt class="text-sm text-muted sm:w-48">Account created</dt>
                <dd class="text-sm font-medium"><?= htmlspecialchars($memberSince) ?></dd>
            </div>
        </dl>
    </section>

    <!-- Classes (sample data) -->
    <section id="classes" class="dashboard__section">
        <h2 class="text-lg font-semibold text-navy">My classes</h2>
        <p class="mt-1 text-xs text-muted">Sample data for demonstration.</p>

        <?php if (empty($classes)): ?>
            <p class="mt-4 text-sm text-muted">You have not been assigned any classes yet.</p>
        <?php else: ?>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[36rem] text-left text-sm">
                    <thead class="border-y border-line text-xs uppercase tracking-wide text-muted">
                        <tr>
                            <th class="py-3 pr-4 font-medium">Class</th>
                            <th class="py-3 pr-4 font-medium">Subject</th>
                            <th class="py-3 pr-4 font-medium">Students</th>
                            <th class="py-3 font-medium">Schedule</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line border-b border-line">
                        <?php foreach ($classes as $class): ?>
                            <tr>
                                <td class="py-3 pr-4 font-medium"><?= htmlspecialchars($class['class']) ?></td>
                                <td class="py-3 pr-4"><?= htmlspecialchars($class['subject']) ?></td>
                                <td class="py-3 pr-4"><?= (int) $class['students'] ?></td>
                                <td class="py-3 text-muted"><?= htmlspecialchars($class['schedule']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
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