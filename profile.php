<?php

/**
 * Profile Page
 * Sarpras Management System
 */

define('PAGE_TITLE', 'Profil Saya');

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$currentUser = getCurrentUser();
$error = '';
$success = '';

// Get full user data
$user = fetch("SELECT * FROM users WHERE id = ?", [$currentUser['id']]);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'update_profile') {
            $nama_lengkap = sanitize($_POST['nama_lengkap'] ?? '');
            $email = sanitize($_POST['email'] ?? '');
            $phone = sanitize($_POST['phone'] ?? '');

            if (empty($nama_lengkap)) {
                $error = 'Nama lengkap harus diisi.';
            } else {
                query(
                    "UPDATE users SET nama_lengkap = ?, email = ?, phone = ? WHERE id = ?",
                    [$nama_lengkap, $email, $phone, $currentUser['id']]
                );

                $_SESSION['nama_lengkap'] = $nama_lengkap;
                $user['nama_lengkap'] = $nama_lengkap;
                $user['email'] = $email;
                $user['phone'] = $phone;

                logActivity('UPDATE_PROFILE', 'Updated profile');
                $success = 'Profil berhasil diperbarui.';
            }
        } elseif ($action === 'change_password') {
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                $error = 'Semua field password harus diisi.';
            } elseif (!password_verify($current_password, $user['password'])) {
                $error = 'Password saat ini salah.';
            } elseif (strlen($new_password) < 6) {
                $error = 'Password baru minimal 6 karakter.';
            } elseif ($new_password !== $confirm_password) {
                $error = 'Konfirmasi password tidak cocok.';
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                query("UPDATE users SET password = ? WHERE id = ?", [$hashed_password, $currentUser['id']]);

                logActivity('CHANGE_PASSWORD', 'Changed password');
                $success = 'Password berhasil diubah.';
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Profil Saya</h1>
            <p class="text-gray-600">Kelola informasi akun Anda</p>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i><?= e($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-check-circle mr-2"></i><?= e($success) ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Profile Info Card -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-user-circle text-blue-500 mr-2"></i>Informasi Profil
            </h2>

            <form method="POST" action="">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="update_profile">

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input type="text" value="<?= e($user['username']) ?>"
                            class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg" disabled>
                        <p class="text-xs text-gray-500 mt-1">Username tidak dapat diubah.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap *</label>
                        <input type="text" name="nama_lengkap" value="<?= e($user['nama_lengkap']) ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="<?= e($user['email']) ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                        <input type="text" name="phone" value="<?= e($user['phone']) ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                            <?= ucfirst($user['role']) ?>
                        </span>
                    </div>
                </div>

                <button type="submit" class="mt-6 w-full py-2 px-4 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-save mr-2"></i>Simpan Perubahan
                </button>
            </form>
        </div>

        <!-- Change Password Card -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-key text-yellow-500 mr-2"></i>Ubah Password
            </h2>

            <form method="POST" action="">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="change_password">

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password Saat Ini *</label>
                        <input type="password" name="current_password"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru *</label>
                        <input type="password" name="new_password"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required minlength="6">
                        <p class="text-xs text-gray-500 mt-1">Minimal 6 karakter.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru *</label>
                        <input type="password" name="confirm_password"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required>
                    </div>
                </div>

                <button type="submit" class="mt-6 w-full py-2 px-4 bg-yellow-500 text-white font-medium rounded-lg hover:bg-yellow-600 transition">
                    <i class="fas fa-lock mr-2"></i>Ubah Password
                </button>
            </form>
        </div>
    </div>

    <!-- Account Info -->
    <div class="bg-white rounded-xl shadow-sm p-6 mt-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-info-circle text-gray-500 mr-2"></i>Informasi Akun
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-500">Tanggal Dibuat:</span>
                <span class="ml-2 text-gray-800"><?= formatDateTime($user['created_at']) ?></span>
            </div>
            <div>
                <span class="text-gray-500">Terakhir Diperbarui:</span>
                <span class="ml-2 text-gray-800"><?= formatDateTime($user['updated_at']) ?></span>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>