<?php
// Layout: top of every dashboard page.
// The page must call require_role() and set $pageTitle BEFORE including this file.

require_once __DIR__ . '/icons.php';

$fullName = $_SESSION['full_name'] ?? 'User';
$role     = $_SESSION['role'] ?? '';
$initial  = mb_strtoupper(mb_substr($fullName, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?> | EduManage</title>

    <!-- Inter font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind (CDN) and our brand colours -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="<?= BASE_URL ?>/assets/js/tailwind-config.js"></script>
</head>

<body class="bg-surface font-sans text-ink antialiased">

    <div class="dashboard flex min-h-screen flex-col">

        <!-- Top bar: logo, page title and the signed-in user -->
        <header class="dashboard__header sticky top-0 z-40 flex h-20 shrink-0 items-center justify-between border-b border-line bg-white px-4 sm:px-6">
            <div class="flex items-center gap-3 sm:gap-4">
                <button
                    type="button"
                    id="sidebar-toggle"
                    class="dashboard__menu-button -ml-2 p-2 text-navy lg:hidden"
                    aria-label="Open menu"
                    aria-controls="sidebar"
                    aria-expanded="false">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="EduManage" class="h-14 w-auto">

                <span class="hidden h-8 w-px bg-line sm:block" aria-hidden="true"></span>

                <h1 class="sr-only text-xl font-semibold text-navy sm:not-sr-only"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
            </div>

            <div class="dashboard__user flex items-center gap-3">
                <div class="hidden text-right sm:block">
                    <p class="text-sm font-semibold"><?= htmlspecialchars($fullName) ?></p>
                    <p class="text-xs capitalize text-muted"><?= htmlspecialchars($role) ?></p>
                </div>
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-navy text-sm font-semibold text-white" aria-hidden="true">
                    <?= htmlspecialchars($initial) ?>
                </span>
            </div>
        </header>

        <div class="dashboard__body flex flex-1">

            <?php require __DIR__ . '/sidebar.php'; ?>

            <div class="dashboard__main flex min-w-0 flex-1 flex-col">

                <main class="dashboard__content flex-1 px-4 py-8 sm:px-8">