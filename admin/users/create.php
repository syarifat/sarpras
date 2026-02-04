<?php

/**
 * Admin - Create User
 * Sarpras Management System
 */

define('PAGE_TITLE', 'Tambah Pengguna');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['admin']);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $nama_lengkap = sanitize($_POST['nama_lengkap'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $role = sanitize($_POST['role'] ?? 'user');

        // Validation
        if (empty($username) || empty($password) || empty($nama_lengkap)) {
            $error = 'Username, password, dan nama lengkap harus diisi.';
        } elseif (strlen($password) < 6) {
            $error = 'Password minimal 6 karakter.';
        } elseif (!in_array($role, ['admin', 'petugas', 'user'])) {
            $error = 'Role tidak valid.';
        } else {
            // Check if username exists
            $existing = fetch("SELECT id FROM users WHERE username = ?", [$username]);
            if ($existing) {
                $error = 'Username sudah digunakan.';
            } else {
                // Insert user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                query(
                    "INSERT INTO users (username, password, nama_lengkap, email, phone, role) VALUES (?, ?, ?, ?, ?, ?)",
                    [$username, $hashed_password, $nama_lengkap, $email, $phone, $role]
                );

                logActivity('CREATE_USER', "Created user: $username");
                setFlash('success', 'Pengguna berhasil ditambahkan.');
                header('Location: index.php');
                exit;
            }
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-2xl mx-auto">
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Tambah Pengguna</h1>
            <p class="text-gray-600">Buat akun pengguna baru</p>
        </div>
        <a href="index.php" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i><?= e($error) ?>
        </div>
    <?php endif; ?>

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="POST" action="">
            <?= csrfField() ?>

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Username *</label>
                    <input type="text" name="username" value="<?= e($_POST['username'] ?? '') ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required pattern="[a-zA-Z0-9_]+" title="Hanya huruf, angka, dan underscore">
                    <p class="text-xs text-gray-500 mt-1">Hanya huruf, angka, dan underscore</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                    <input type="password" name="password"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required minlength="6">
                    <p class="text-xs text-gray-500 mt-1">Minimal 6 karakter</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
                    <input type="text" name="nama_lengkap" value="<?= e($_POST['nama_lengkap'] ?? '') ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">No. Telepon</label>
                    <input type="text" name="phone" value="<?= e($_POST['phone'] ?? '') ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
                    <select name="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="user" <?= ($_POST['role'] ?? '') === 'user' ? 'selected' : '' ?>>User (Pengguna Umum)</option>
                        <option value="petugas" <?= ($_POST['role'] ?? '') === 'petugas' ? 'selected' : '' ?>>Petugas/Operator</option>
                        <option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
            </div>

            <div class="mt-8 flex gap-4">
                <button type="submit" class="flex-1 py-2 px-4 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
                <a href="index.php" class="flex-1 py-2 px-4 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>