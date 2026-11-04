<?php
// Layout: the navigation sidebar. It is loaded by header.php,
// so $role is already defined there.
//
// Each role gets its own menu. Every item opens its own view on the dashboard:
// 'view' must match the data-view value of a section on that page.

$menus = [
    'student' => [
        ['label' => 'Dashboard',     'view' => 'overview',      'icon' => 'dashboard'],
        ['label' => 'My Profile',    'view' => 'profile',       'icon' => 'user'],
        ['label' => 'Academics',     'view' => 'academics',     'icon' => 'book'],
        ['label' => 'Announcements', 'view' => 'announcements', 'icon' => 'bell'],
    ],
    'instructor' => [
        ['label' => 'Dashboard',     'view' => 'overview',      'icon' => 'dashboard'],
        ['label' => 'My Profile',    'view' => 'profile',       'icon' => 'user'],
        ['label' => 'My Classes',    'view' => 'classes',       'icon' => 'book'],
        ['label' => 'Announcements', 'view' => 'announcements', 'icon' => 'bell'],
    ],
    'parent' => [
        ['label' => 'Dashboard',     'view' => 'overview',      'icon' => 'dashboard'],
        ['label' => 'My Profile',    'view' => 'profile',       'icon' => 'user'],
        ['label' => 'My Child',      'view' => 'child',         'icon' => 'academic-cap'],
        ['label' => 'Announcements', 'view' => 'announcements', 'icon' => 'bell'],
    ],
    'admin' => [
        ['label' => 'Dashboard',     'view' => 'overview',      'icon' => 'dashboard'],
        ['label' => 'Students',      'view' => 'students',      'icon' => 'academic-cap'],
        ['label' => 'Instructors',   'view' => 'instructors',   'icon' => 'user'],
        ['label' => 'Parents',       'view' => 'parents',       'icon' => 'users'],
        ['label' => 'Users',         'view' => 'users',         'icon' => 'user-group'],
    ],
];

$menu = $menus[$role] ?? [];
?>
<!-- Dark layer behind the drawer on small screens -->
<div class="dashboard__overlay fixed inset-x-0 bottom-0 top-20 z-20 hidden bg-black/40 lg:hidden" id="sidebar-overlay"></div>

<aside
    class="dashboard__sidebar fixed bottom-0 left-0 top-20 z-30 flex w-64 -translate-x-full flex-col bg-navy text-white transition-transform duration-200 lg:sticky lg:bottom-auto lg:top-20 lg:h-[calc(100vh-5rem)] lg:shrink-0 lg:translate-x-0"
    id="sidebar">

    <nav class="dashboard__nav flex-1 overflow-y-auto py-6" aria-label="Main navigation">
        <ul class="space-y-1">
            <?php foreach ($menu as $index => $item): ?>
                <li>
                    <a
                        href="#<?= htmlspecialchars($item['view']) ?>"
                        data-view-link="<?= htmlspecialchars($item['view']) ?>"
                        class="dashboard__link flex items-center gap-3 border-l-4 px-5 py-3 text-sm font-medium <?= $index === 0
                                                                                                                    ? 'border-gold bg-white/10 text-white'
                                                                                                                    : 'border-transparent text-white/70 hover:bg-white/5 hover:text-white' ?>">
                        <?= icon($item['icon']) ?>
                        <?= htmlspecialchars($item['label']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <div class="border-t border-white/10 py-3">
        <a
            href="<?= BASE_URL ?>/logout.php"
            class="dashboard__logout flex items-center gap-3 border-l-4 border-transparent px-5 py-3 text-sm font-medium text-white/70 hover:bg-white/5 hover:text-white">
            <?= icon('logout') ?>
            Log out
        </a>
    </div>

</aside>