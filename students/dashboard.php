<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('student');

require_once __DIR__ . '/../config/database.php';

// Load this student's own record, using the ID stored in the session
$stmt = $pdo->prepare('SELECT full_name, email, role, created_at FROM users WHERE id = :id');
$stmt->execute(['id' => $_SESSION['user_id']]);
$student = $stmt->fetch();

// If the account no longer exists, end the session
if (!$student) {
    header('Location: ' . BASE_URL . '/logout.php');
    exit;
}

$firstName   = explode(' ', trim($student['full_name']))[0];
$memberSince = date('j F Y', strtotime($student['created_at']));

// SAMPLE DATA: replace with real records if academic tables are added later
$overview = [
    ['label' => 'Subjects',     'value' => '5'],
    ['label' => 'Attendance',   'value' => '96%'],
    ['label' => 'Current term', 'value' => 'Term 3'],
    ['label' => 'Next exam',    'value' => '23 Nov'],
];

$subjects = [
    ['name' => 'Mathematics',     'instructor' => 'Mr. Otieno',  'schedule' => 'Mon and Wed, 8:00 AM'],
    ['name' => 'English',         'instructor' => 'Ms. Wanjiru', 'schedule' => 'Tue and Thu, 9:00 AM'],
    ['name' => 'Kiswahili',       'instructor' => 'Mr. Mwangi',  'schedule' => 'Mon and Fri, 10:30 AM'],
    ['name' => 'Integrated Science', 'instructor' => 'Ms. Achieng', 'schedule' => 'Wed and Fri, 11:30 AM'],
    ['name' => 'Social Studies',  'instructor' => 'Mr. Kamau',   'schedule' => 'Tue, 2:00 PM'],
];

$announcements = [
    ['date' => '3 November 2026',  'title' => 'Term 3 examinations',    'text' => 'End-of-term examinations begin on 23 November. Timetables will be shared in class.'],
    ['date' => '29 October 2026',  'title' => 'Sports day',             'text' => 'Sports day will be held on the school grounds. Wear your sports uniform.'],
    ['date' => '20 October 2026',  'title' => 'Library hours extended', 'text' => 'The library now stays open until 5:00 PM on weekdays.'],
];

$pageTitle = 'Student Dashboard';
require __DIR__ . '/../includes/header.php';
?>

<div class="mx-auto max-w-5xl space-y-12">

    <!-- Welcome and overview -->
    <section id="overview" class="dashboard__section">
        <h2 class="text-xl font-semibold text-navy">Welcome back, <?= htmlspecialchars($firstName) ?></h2>
        <p class="mt-1 text-sm text-muted">Here is a summary of your school account.</p>

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
                <dd class="text-sm font-medium"><?= htmlspecialchars($student['full_name']) ?></dd>
            </div>
            <div class="flex flex-col gap-1 border-b border-line py-3 sm:flex-row sm:gap-0">
                <dt class="text-sm text-muted sm:w-48">Email address</dt>
                <dd class="break-all text-sm font-medium"><?= htmlspecialchars($student['email']) ?></dd>
            </div>
            <div class="flex flex-col gap-1 border-b border-line py-3 sm:flex-row sm:gap-0">
                <dt class="text-sm text-muted sm:w-48">Role</dt>
                <dd class="text-sm font-medium capitalize"><?= htmlspecialchars($student['role']) ?></dd>
            </div>
            <div class="flex flex-col gap-1 border-b border-line py-3 sm:flex-row sm:gap-0">
                <dt class="text-sm text-muted sm:w-48">Member since</dt>
                <dd class="text-sm font-medium"><?= htmlspecialchars($memberSince) ?></dd>
            </div>
        </dl>
    </section>

    <!-- Academics (sample data) -->
    <section id="academics" class="dashboard__section">
        <h2 class="text-lg font-semibold text-navy">Academics</h2>
        <p class="mt-1 text-xs text-muted">Sample data for demonstration.</p>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[32rem] text-left text-sm">
                <thead class="border-y border-line text-xs uppercase tracking-wide text-muted">
                    <tr>
                        <th class="py-3 pr-4 font-medium">Subject</th>
                        <th class="py-3 pr-4 font-medium">Instructor</th>
                        <th class="py-3 font-medium">Schedule</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line border-b border-line">
                    <?php foreach ($subjects as $subject): ?>
                        <tr>
                            <td class="py-3 pr-4 font-medium"><?= htmlspecialchars($subject['name']) ?></td>
                            <td class="py-3 pr-4"><?= htmlspecialchars($subject['instructor']) ?></td>
                            <td class="py-3 text-muted"><?= htmlspecialchars($subject['schedule']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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