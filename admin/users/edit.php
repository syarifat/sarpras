<?php

/**
 * Admin - Edit User
 * Sarpras Management System
 */

define('PAGE_TITLE', 'Edit Pengguna');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['admin']);

$id = intval($_GET['id'] ?? 0);
$user = fetch("SELECT * FROM users WHERE id = ?", [$id]);

if (!$user) {
    setFlash('error', 'Pengguna tidak ditemukan.');
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $nama_lengkap = sanitize($_POST['nama_lengkap'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $role = sanitize($_POST['role'] ?? 'user');
        $new_password = $_POST['new_password'] ?? '';

        // Validation
        if (empty($nama_lengkap)) {
            $error = 'Nama lengkap harus diisi.';
        } elseif (!in_array($role, ['admin', 'petugas', 'user'])) {
            $error = 'Role tidak valid.';
        } else {
            // Update user
            if (!empty($new_password)) {
                if (strlen($new_password) < 6) {
                    $error = 'Password baru minimal 6 karakter.';
                } else {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    query(
                        "UPDATE users SET nama_lengkap = ?, email = ?, phone = ?, role = ?, password = ? WHERE id = ?",
                        [$nama_lengkap, $email, $phone, $role, $hashed_password, $id]
                    );
                }
            } else {
                query(
                    "UPDATE users SET nama_lengkap = ?, email = ?, phone = ?, role = ? WHERE id = ?",
                    [$nama_lengkap, $email, $phone, $role, $id]
                );
            }

            if (!$error) {
                logActivity('UPDATE_USER', "Updated user: {$user['username']}");
                setFlash('success', 'Pengguna berhasil diperbarui.');
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
            <h1 class="text-2xl font-bold text-gray-800">Edit Pengguna</h1>
            <p class="text-gray-600">Ubah data pengguna: <?= e($user['username']) ?></p>
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
                    <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                    <input type="text" value="<?= e($user['username']) ?>"
                        class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg" disabled>
                    <p class="text-xs text-gray-500 mt-1">Username tidak dapat diubah</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                    <input type="password" name="new_password"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        minlength="6">
                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah password</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
                    <input type="text" name="nama_lengkap" value="<?= e($_POST['nama_lengkap'] ?? $user['nama_lengkap']) ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" value="<?= e($_POST['email'] ?? $user['email']) ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">No. Telepon</label>
                    <input type="text" name="phone" value="<?= e($_POST['phone'] ?? $user['phone']) ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
                    <select name="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <?php $selectedRole = $_POST['role'] ?? $user['role']; ?>
                        <option value="user" <?= $selectedRole === 'user' ? 'selected' : '' ?>>User (Pengguna Umum)</option>
                        <option value="petugas" <?= $selectedRole === 'petugas' ? 'selected' : '' ?>>Petugas/Operator</option>
                        <option value="admin" <?= $selectedRole === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                    <?php if ($id == getCurrentUser()['id']): ?>
                        <p class="text-xs text-yellow-600 mt-1"><i class="fas fa-exclamation-triangle"></i> Hati-hati mengubah role Anda sendiri</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-8 flex gap-4">
                <button type="submit" class="flex-1 py-2 px-4 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-save mr-2"></i>Simpan Perubahan
                </button>
                <a href="index.php" class="flex-1 py-2 px-4 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>