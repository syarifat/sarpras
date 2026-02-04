<?php

/**
 * Login Page
 * Sarpras Management System
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . getRedirectUrl());
    exit;
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi.';
    } elseif (attemptLogin($username, $password)) {
        header('Location: ' . getRedirectUrl());
        exit;
    } else {
        $error = 'Username atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sarpras Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="min-h-screen bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-full shadow-lg mb-4">
                <i class="fas fa-school text-blue-600 text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Sarpras<span class="text-yellow-300">App</span></h1>
            <p class="text-blue-200">Sistem Manajemen Sarana Prasarana</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Masuk ke Akun</h2>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <span><?= e($error) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-1"></i> Username
                    </label>
                    <input type="text" id="username" name="username"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="Masukkan username"
                        value="<?= e($_POST['username'] ?? '') ?>"
                        required autofocus>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-1"></i> Password
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="Masukkan password"
                            required>
                        <button type="button" onclick="togglePassword()"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i class="fas fa-eye" id="toggle-icon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit"
                    class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform hover:scale-[1.02] transition-all shadow-lg">
                    <i class="fas fa-sign-in-alt mr-2"></i> Masuk
                </button>
            </form>

            <!-- Demo Accounts Info -->
            <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                <p class="text-sm font-semibold text-gray-700 mb-3">
                    <i class="fas fa-info-circle mr-1 text-blue-500"></i> Akun Demo:
                </p>
                <div class="space-y-2 text-sm text-gray-600">
                    <div class="flex justify-between">
                        <span><i class="fas fa-user-shield text-red-500 w-5"></i> Admin:</span>
                        <code class="bg-gray-200 px-2 rounded">admin / password123</code>
                    </div>
                    <div class="flex justify-between">
                        <span><i class="fas fa-user-tie text-blue-500 w-5"></i> Petugas:</span>
                        <code class="bg-gray-200 px-2 rounded">petugas1 / password123</code>
                    </div>
                    <div class="flex justify-between">
                        <span><i class="fas fa-user text-green-500 w-5"></i> User:</span>
                        <code class="bg-gray-200 px-2 rounded">guru1 / password123</code>
                    </div>
                </div>
            </div>
        </div>

        <p class="text-center text-blue-200 mt-6 text-sm">
            &copy; <?= date('Y') ?> SMK - Sarana Prasarana Management
        </p>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggle-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>