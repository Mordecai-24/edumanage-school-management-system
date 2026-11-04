<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

redirect_if_logged_in();   // also starts the session

// Show a success message after a student has just registered
// Show a success message after a student has just registered
$registered = ($_GET['registered'] ?? '') === '1';
$loggedOut  = ($_GET['loggedout'] ?? '') === '1';


$errors = [];
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = mb_strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Please enter your email and password.';
    } else {
        try {
            // Find the user by email
            $stmt = $pdo->prepare('SELECT id, full_name, password, role FROM users WHERE email = :email');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // New session ID for this login (prevents session fixation)
                session_regenerate_id(true);

                $_SESSION['user_id']   = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role']      = $user['role'];

                header('Location: ' . dashboard_path($user['role']));
                exit;
            } else {
                $errors[] = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errors[] = 'Something went wrong on our side. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in | EduManage - Crestfield Academy</title>

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

            <!-- Background photo (decorative) and navy tint so the text stays readable -->
            <img
                src="assets/images/image4.jpg"
                alt=""
                loading="lazy"
                class="auth__image absolute inset-0 h-full w-full object-cover">
            <div class="auth__overlay absolute inset-0 bg-navy/35"></div>

            <p class="relative text-sm font-medium uppercase tracking-widest text-gold"></p>

            <div class="relative max-w-md">
                <p class="text-3xl font-semibold leading-tight">Inspiring Excellence, Building Futures.</p>
                <p class="mt-4 text-white/70">
                </p>
            </div>

            <p class="relative text-sm text-white/50"> </p>
        </aside>

        <!-- Form panel -->
        <section class="flex w-full items-center justify-center px-6 py-12 lg:w-1/2">
            <div class="w-full max-w-sm">

                <img src="assets/images/logo.png" alt="EduManage School Management System" class="mx-auto h-28 w-auto">

                <h1 class="mt-8 text-2xl font-semibold text-navy">Welcome back</h1>
                <p class="mt-2 text-sm text-muted">Log in to your Crestfield Academy account.</p>
                <?php if ($registered): ?>
                    <div class="auth__message auth__message--success mt-6 rounded border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
                        Your account has been created. Please log in.
                    </div>
                <?php endif; ?>
                <?php if ($loggedOut): ?>
                    <div class="auth__message auth__message--info mt-6 rounded border border-line bg-surface px-4 py-3 text-sm text-ink" role="status">
                        You have been logged out.
                    </div>
                <?php endif; ?>
                <?php if (!empty($errors)): ?>
                    <div class="auth__message mt-6 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                        <?php foreach ($errors as $error): ?>
                            <p><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form class="auth__form mt-8 space-y-5" action="login.php" method="post">

                    <div>
                        <label for="email" class="block text-sm font-medium text-ink">Email address</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            required
                            autocomplete="email"
                            value="<?= htmlspecialchars($email) ?>" class="mt-1 block w-full rounded border border-line px-3 py-2.5 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-ink">Password</label>
                        <div class="auth__password relative mt-1">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                class="block w-full rounded border border-line px-3 py-2.5 pr-16 text-sm focus:border-navy focus:outline-none focus:ring-1 focus:ring-navy">
                            <button
                                type="button"
                                class="auth__toggle absolute inset-y-0 right-0 px-3 text-sm font-medium text-muted hover:text-navy"
                                aria-label="Show password">
                                Show
                            </button>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="auth__submit w-full rounded bg-navy px-4 py-2.5 text-sm font-semibold text-white hover:bg-navy/90 focus:outline-none focus:ring-2 focus:ring-gold focus:ring-offset-2">
                        Log in
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-muted">
                    New student?
                    <a href="register.php" class="font-medium text-navy underline underline-offset-2 hover:text-navy/80">Create an account</a>
                </p>

            </div>
        </section>

    </main>

    <script src="assets/js/script.js"></script>

</body>

</html>