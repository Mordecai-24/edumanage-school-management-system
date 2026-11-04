<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

redirect_if_logged_in();

// Variables the page needs, with empty starting values
$errors   = [];
$fullName = '';
$email    = '';

// Only run this when the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Read the submitted values. "?? ''" means: use an empty string if it's missing.
    $fullName = trim($_POST['full_name'] ?? '');
    $email    = mb_strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    // Full name
    if ($fullName === '') {
        $errors[] = 'Please enter your full name.';
    } elseif (mb_strlen($fullName) > 100) {
        $errors[] = 'Your full name must be 100 characters or fewer.';
    }

    // Email
    if ($email === '') {
        $errors[] = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif (mb_strlen($email) > 255) {
        $errors[] = 'Your email address is too long.';
    }

    // Password (we do not trim passwords: spaces can be part of them)
    if (strlen($password) < 8) {
        $errors[] = 'Your password must be at least 8 characters long.';
    } elseif (strlen($password) > 72) {
        $errors[] = 'Your password must be 72 characters or fewer.';
    }
    // Only touch the database if the input passed every check
    if (empty($errors)) {
        try {
            // Is this email already registered?
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
            $stmt->execute(['email' => $email]);

            if ($stmt->fetch()) {
                $errors[] = 'An account with this email already exists. Try logging in instead.';
            } else {
                // Scramble the password, then save the new student
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare(
                    'INSERT INTO users (full_name, email, password, role)
                     VALUES (:full_name, :email, :password, :role)'
                );
                $stmt->execute([
                    'full_name' => $fullName,
                    'email'     => $email,
                    'password'  => $passwordHash,
                    'role'      => 'student',   // always a student, whatever the form sends
                ]);

                // Success: send the user to the login page
                header('Location: login.php?registered=1');
                exit;
            }
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                // MySQL's unique rule caught a duplicate email
                $errors[] = 'An account with this email already exists. Try logging in instead.';
            } else {
                error_log($e->getMessage());
                $errors[] = 'Something went wrong on our side. Please try again later.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create account | EduManage - Crestfield Academy</title>

    <!-- Inter font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind (CDN) and our brand colours. The config file must load AFTER Tailwind. -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="assets/js/tailwind-config.js"></script>
</head>

<body class="bg-white font-sans text-ink antialiased">

    <main class="auth flex min-h-screen">

        <!-- Brand panel: hidden on small screens, shown from 1024px up -->
        <aside class="auth__brand relative hidden flex-col justify-between overflow-hidden bg-navy p-12 text-white lg:flex lg:w-1/2">

            <!-- Background photo (decorative) -->
            <img
                src="assets/images/image1.jpg"
                alt=""
                loading="lazy"
                class="auth__image absolute inset-0 h-full w-full object-cover">
            <div class="auth__overlay absolute inset-0 bg-navy/35"></div>


            <p class="relative text-sm font-medium uppercase tracking-widest text-gold"></p>

            <div class="relative max-w-md">
                <p class="text-3xl font-semibold leading-tight">Inspiring Excellence, Building Futures.</p>

            </div>


            <p class="relative text-sm text-white/50"> </p>
        </aside>

        <!-- Form panel -->
        <section class="flex w-full items-center justify-center px-6 py-12 lg:w-1/2">
            <div class="w-full max-w-sm">

                <img src="assets/images/logo.png" alt="EduManage School Management System" class="mx-auto h-24 w-auto">

                <h1 class="mt-8 text-2xl font-semibold text-navy">Create your account</h1>
                <p class="mt-2 text-sm text-muted">Student registration for Crestfield Academy.</p>

                <?php if (!empty($errors)): ?>
                    <div class="auth__message mt-6 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                        <ul class="list-disc space-y-1 pl-4">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form class="auth__form mt-8 space-y-5" action="register.php" method="post">

                    <div>
                        <label for="full_name" class="block text-sm font-medium text-ink">Full name</label>
                        <input
                            type="text"
                            id="full_name"
                            name="full_name"
                            required
                            maxlength="100"
                            value="<?= htmlspecialchars($fullName) ?>"
                            autocomplete="name"
                            class="mt-1 block w-full rounded border border-line px-3 py-2.5 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-ink">Email address</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            required
                            maxlength="255"
                            value="<?= htmlspecialchars($email) ?>"
                            autocomplete="email"
                            class="mt-1 block w-full rounded border border-line px-3 py-2.5 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-ink">Password</label>
                        <div class="auth__password relative mt-1">
                            <input
                                type="password"
                                id="password"
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
                        <p class="mt-1 text-xs text-muted">Use at least 8 characters.</p>
                    </div>

                    <button
                        type="submit"
                        class="auth__submit w-full rounded bg-navy px-4 py-2.5 text-sm font-semibold text-white hover:bg-navy/90 focus:outline-none focus:ring-2 focus:ring-gold focus:ring-offset-2">
                        Create account
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-muted">
                    Already have an account?
                    <a href="login.php" class="font-medium text-navy underline underline-offset-2 hover:text-navy/80">Log in</a>
                </p>

                <p class="mt-4 text-center text-xs text-muted">
                    This form is for students only. Instructor and parent accounts are created by the school administrator.
                </p>

            </div>
        </section>

    </main>

    <script src="assets/js/script.js"></script>
</body>

</html>