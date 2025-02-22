<?php
session_start();
require_once 'notification.php';

// Periksa apakah pengguna adalah admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Pelajaran</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Remove dark mode configuration
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        dark: {
                            bg: '#1a202c',
                            card: '#2d3748',
                            text: '#a0aec0'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        * {
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .schedule-card {
            transition: all 0.3s ease;
        }
        .schedule-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <main class="ml-64 p-6">
        <div class="container mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Jadwal Pelajaran</h1>

            <!-- Schedule Grid -->
            <div class="grid gap-6">
                <!-- Senin -->
                <div class="bg-white rounded-lg shadow-md p-6 schedule-card">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold mr-4">
                            Sen
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800">Senin</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <div class="text-sm text-blue-600 mb-1">07:00 - 08:30</div>
                            <div class="font-semibold">Matematika</div>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <div class="text-sm text-green-600 mb-1">08:30 - 10:00</div>
                            <div class="font-semibold">Bahasa Indonesia</div>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <div class="text-sm text-purple-600 mb-1">10:15 - 11:45</div>
                            <div class="font-semibold">IPA</div>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <div class="text-sm text-yellow-600 mb-1">12:30 - 14:00</div>
                            <div class="font-semibold">IPS</div>
                        </div>
                    </div>
                </div>

                <!-- Selasa -->
                <div class="bg-white rounded-lg shadow-md p-6 schedule-card">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-full bg-green-500 flex items-center justify-center text-white font-bold mr-4">
                            Sel
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800">Selasa</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-red-50 p-4 rounded-lg">
                            <div class="text-sm text-red-600 mb-1">07:00 - 08:30</div>
                            <div class="font-semibold">Bahasa Inggris</div>
                        </div>
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <div class="text-sm text-blue-600 mb-1">08:30 - 10:00</div>
                            <div class="font-semibold">Matematika</div>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <div class="text-sm text-green-600 mb-1">10:15 - 11:45</div>
                            <div class="font-semibold">Bahasa Indonesia</div>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <div class="text-sm text-purple-600 mb-1">12:30 - 14:00</div>
                            <div class="font-semibold">IPA</div>
                        </div>
                    </div>
                </div>

                <!-- Rabu -->
                <div class="bg-white rounded-lg shadow-md p-6 schedule-card">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-full bg-purple-500 flex items-center justify-center text-white font-bold mr-4">
                            Rab
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800">Rabu</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <div class="text-sm text-yellow-600 mb-1">07:00 - 08:30</div>
                            <div class="font-semibold">IPS</div>
                        </div>
                        <div class="bg-red-50 p-4 rounded-lg">
                            <div class="text-sm text-red-600 mb-1">08:30 - 10:00</div>
                            <div class="font-semibold">Bahasa Inggris</div>
                        </div>
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <div class="text-sm text-blue-600 mb-1">10:15 - 11:45</div>
                            <div class="font-semibold">Matematika</div>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <div class="text-sm text-green-600 mb-1">12:30 - 14:00</div>
                            <div class="font-semibold">Bahasa Indonesia</div>
                        </div>
                    </div>
                </div>

                <!-- Kamis -->
                <div class="bg-white rounded-lg shadow-md p-6 schedule-card">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-full bg-red-500 flex items-center justify-center text-white font-bold mr-4">
                            Kam
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800">Kamis</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <div class="text-sm text-purple-600 mb-1">07:00 - 08:30</div>
                            <div class="font-semibold">IPA</div>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <div class="text-sm text-yellow-600 mb-1">08:30 - 10:00</div>
                            <div class="font-semibold">IPS</div>
                        </div>
                        <div class="bg-red-50 p-4 rounded-lg">
                            <div class="text-sm text-red-600 mb-1">10:15 - 11:45</div>
                            <div class="font-semibold">Bahasa Inggris</div>
                        </div>
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <div class="text-sm text-blue-600 mb-1">12:30 - 14:00</div>
                            <div class="font-semibold">Matematika</div>
                        </div>
                    </div>
                </div>

                <!-- Jumat -->
                <div class="bg-white rounded-lg shadow-md p-6 schedule-card">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-full bg-yellow-500 flex items-center justify-center text-white font-bold mr-4">
                            Jum
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800">Jumat</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-green-50 p-4 rounded-lg">
                            <div class="text-sm text-green-600 mb-1">07:00 - 08:30</div>
                            <div class="font-semibold">Bahasa Indonesia</div>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <div class="text-sm text-purple-600 mb-1">08:30 - 10:00</div>
                            <div class="font-semibold">IPA</div>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <div class="text-sm text-yellow-600 mb-1">10:15 - 11:45</div>
                            <div class="font-semibold">IPS</div>
                        </div>
                        <!-- If a fourth slot is not needed, you may omit it -->
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!-- No dark mode scripts included -->
</body>
</html>
