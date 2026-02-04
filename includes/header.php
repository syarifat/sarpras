<?php

/**
 * Header Template
 * Sarpras Management System
 */

if (!defined('PAGE_TITLE')) {
    define('PAGE_TITLE', 'Sarpras Management');
}

$currentUser = getCurrentUser();
$currentRole = $currentUser['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= PAGE_TITLE ?> - Sarpras Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .sidebar-link.active {
            background-color: rgba(59, 130, 246, 0.1);
            border-left: 3px solid #3b82f6;
            color: #2563eb;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen">
    <?php if (isLoggedIn()): ?>
        <!-- Navbar -->
        <nav class="bg-white shadow-lg fixed w-full z-50">
            <div class="max-w-full mx-auto px-4">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <button id="sidebar-toggle" class="p-2 rounded-md text-gray-600 hover:bg-gray-100 lg:hidden">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <a href="/sarpras_lagi/" class="flex items-center ml-2 lg:ml-0">
                            <i class="fas fa-school text-primary-600 text-2xl mr-2"></i>
                            <span class="font-bold text-xl text-gray-800">Sarpras<span class="text-primary-600">App</span></span>
                        </a>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="hidden sm:flex items-center space-x-2">
                            <span class="text-sm text-gray-600">
                                <i class="fas fa-user-circle mr-1"></i>
                                <?= e($currentUser['nama_lengkap']) ?>
                            </span>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-primary-100 text-primary-800">
                                <?= ucfirst($currentRole) ?>
                            </span>
                        </div>
                        <a href="/sarpras_lagi/logout.php" class="flex items-center px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition">
                            <i class="fas fa-sign-out-alt mr-1"></i>
                            <span class="hidden sm:inline">Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <div class="flex pt-16">
            <!-- Sidebar -->
            <aside id="sidebar" class="fixed left-0 top-16 h-[calc(100vh-4rem)] w-64 bg-white shadow-lg transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-40 overflow-y-auto">
                <nav class="p-4">
                    <?php if ($currentRole === 'admin'): ?>
                        <!-- Admin Menu -->
                        <div class="mb-6">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Dashboard</p>
                            <a href="/sarpras_lagi/admin/dashboard.php" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition">
                                <i class="fas fa-tachometer-alt w-5 mr-3"></i>
                                Dashboard
                            </a>
                        </div>
                        <div class="mb-6">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Master Data</p>
                            <a href="/sarpras_lagi/admin/users/" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition">
                                <i class="fas fa-users w-5 mr-3"></i>
                                Pengguna
                            </a>
                            <a href="/sarpras_lagi/admin/kategori/" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition">
                                <i class="fas fa-tags w-5 mr-3"></i>
                                Kategori
                            </a>
                            <a href="/sarpras_lagi/admin/sarpras/" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition">
                                <i class="fas fa-boxes w-5 mr-3"></i>
                                Sarpras
                            </a>
                        </div>
                        <div class="mb-6">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Transaksi</p>
                            <a href="/sarpras_lagi/admin/peminjaman/" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition">
                                <i class="fas fa-hand-holding w-5 mr-3"></i>
                                Peminjaman
                            </a>
                            <a href="/sarpras_lagi/admin/pengembalian/" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition">
                                <i class="fas fa-undo w-5 mr-3"></i>
                                Pengembalian
                            </a>
                            <a href="/sarpras_lagi/admin/pengaduan/" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition">
                                <i class="fas fa-exclamation-triangle w-5 mr-3"></i>
                                Pengaduan
                            </a>
                        </div>
                        <div class="mb-6">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Laporan</p>
                            <a href="/sarpras_lagi/admin/laporan/" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition">
                                <i class="fas fa-chart-bar w-5 mr-3"></i>
                                Laporan & Statistik
                            </a>
                            <a href="/sarpras_lagi/admin/laporan/activity_log.php" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition">
                                <i class="fas fa-history w-5 mr-3"></i>
                                Activity Log
                            </a>
                        </div>

                    <?php elseif ($currentRole === 'petugas'): ?>
                        <!-- Petugas Menu -->
                        <div class="mb-6">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Dashboard</p>
                            <a href="/sarpras_lagi/petugas/dashboard.php" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition">
                                <i class="fas fa-tachometer-alt w-5 mr-3"></i>
                                Dashboard
                            </a>
                        </div>
                        <div class="mb-6">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Transaksi</p>
                            <a href="/sarpras_lagi/admin/peminjaman/" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition">
                                <i class="fas fa-hand-holding w-5 mr-3"></i>
                                Peminjaman
                            </a>
                            <a href="/sarpras_lagi/admin/pengembalian/" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition">
                                <i class="fas fa-undo w-5 mr-3"></i>
                                Pengembalian
                            </a>
                            <a href="/sarpras_lagi/admin/pengaduan/" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition">
                                <i class="fas fa-exclamation-triangle w-5 mr-3"></i>
                                Pengaduan
                            </a>
                        </div>

                    <?php else: ?>
                        <!-- User Menu -->
                        <div class="mb-6">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Dashboard</p>
                            <a href="/sarpras_lagi/user/dashboard.php" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition">
                                <i class="fas fa-tachometer-alt w-5 mr-3"></i>
                                Dashboard
                            </a>
                        </div>
                        <div class="mb-6">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Layanan</p>
                            <a href="/sarpras_lagi/user/peminjaman/" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition">
                                <i class="fas fa-hand-holding w-5 mr-3"></i>
                                Peminjaman Saya
                            </a>
                            <a href="/sarpras_lagi/user/pengaduan/" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition">
                                <i class="fas fa-bullhorn w-5 mr-3"></i>
                                Pengaduan Saya
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- Profile Section (All Roles) -->
                    <div class="mb-6">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Profil</p>
                        <a href="/sarpras_lagi/profile.php" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition">
                            <i class="fas fa-user-cog w-5 mr-3"></i>
                            Profil Saya
                        </a>
                    </div>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 lg:ml-64 p-6">
                <?= displayFlash() ?>
            <?php endif; ?>