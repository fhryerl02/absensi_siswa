<?php
session_start();

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['role']) && !isset($_GET['role'])) {
    header("Location: login.php");
    exit();
}

$role = isset($_SESSION['role']) ? $_SESSION['role'] : $_GET['role'];

if ($role !== 'admin' && $role !== 'tamu') {
    header("Location: login.php");
    exit();
}

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "absensi_sekolah");

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Query untuk menampilkan data absensi
$query = "
    SELECT absen_siswa.id, data_siswa.nama, data_siswa.foto, absen_siswa.status, absen_siswa.tanggal 
    FROM absen_siswa 
    JOIN data_siswa ON absen_siswa.siswa_id = data_siswa.id
";
$result = $conn->query($query);

// Periksa waktu absensi
$current_time = date('H:i:s');
$start_time = '06:00:00';
$end_time = '06:30:00';
$is_absensi_active = ($current_time >= $start_time && $current_time <= $end_time);

// Query untuk mendapatkan jumlah siswa berdasarkan status absensi hari ini
$status_counts = [];
$statuses = ['Hadir', 'Sakit', 'Terlambat', 'Alpha'];
foreach ($statuses as $status) {
    $result_status = $conn->query("
        SELECT COUNT(*) AS total 
        FROM absen_siswa 
        WHERE status = '$status' AND DATE(tanggal) = CURDATE()
    ");
    $row = $result_status->fetch_assoc();
    $status_counts[$status] = $row['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
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
        
        /* Dark mode styles */
        .dark body {
            background-color: #1a202c;
            color: #e2e8f0;
        }
        
        .dark .bg-white {
            background-color: #2d3748 !important;
            color: #e2e8f0 !important;
        }
        
        .dark .bg-gray-50 {
            background-color: #374151 !important;
        }
        
        .dark .bg-gray-100 {
            background-color: #1f2937 !important;
        }
        
        .dark .text-gray-500 {
            color: #9ca3af !important;
        }
        
        .dark table thead {
            background-color: #374151 !important;
        }
        
        .dark table tbody {
            background-color: #2d3748 !important;
        }

        .dark .chart-container {
            background-color: #2d3748;
            border-radius: 0.5rem;
            padding: 1rem;
        }

        /* Custom animations and effects */
        .status-card {
            transition: all 0.3s ease;
        }
        .status-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .chart-container {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .chart-container:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .data-table {
            transition: all 0.3s ease;
        }
        .data-table:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        /* Dark mode enhancements */
        .dark .status-card {
            background: linear-gradient(145deg, #2d3748, #1a202c);
        }
        
        .dark .chart-container {
            background: linear-gradient(145deg, #2d3748, #1a202c);
        }

        /* Loading animation */
        .loading {
            animation: shimmer 2s infinite linear;
            background: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
            background-size: 1000px 100%;
        }
        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-indigo-700 text-white min-h-screen fixed">
            <div class="p-6">
                <h1 class="text-2xl font-bold mb-6">Panel <?php echo ucfirst($role); ?></h1>
                <nav>
                    <ul class="space-y-4">
                        <!-- Tombol Dashboard -->
                        <li>
                            <a href="dashboard.php<?php echo ($role === 'tamu') ? '?role=tamu' : ''; ?>" class="flex items-center px-4 py-2 bg-indigo-600 rounded-md hover:bg-indigo-500 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0l-2-2m2 2V4a1 1 0 011-1h3m-3 4l2-2" />
                                </svg>
                                Dashboard
                            </a>
                        </li>

                        <!-- Menu Admin -->
                        <?php if ($role === 'admin'): ?>
                            <li>
                                <a href="siswa.php" class="flex items-center px-4 py-2 bg-indigo-600 rounded-md hover:bg-indigo-500 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    Data Siswa
                                </a>
                            </li>
                            <li>
                                <a href="absensi.php" class="flex items-center px-4 py-2 bg-indigo-600 rounded-md hover:bg-indigo-500 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Absensi Siswa
                                </a>
                            </li>
                            <li>
                                <a href="jadwal_pelajaran.php" class="flex items-center px-4 py-2 bg-indigo-600 rounded-md hover:bg-indigo-500 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Jadwal Pelajaran
                                </a>
                            </li>
                            
                        <?php elseif ($role === 'tamu'): ?>
                            <!-- Menu Tamu -->
                            <li>
                                <a href="view_siswa.php?role=tamu" class="flex items-center px-4 py-2 bg-indigo-600 rounded-md hover:bg-indigo-500 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    Data Siswa
                                </a>
                            </li>
                            <li>
                                <a href="view_absensi.php?role=tamu" class="flex items-center px-4 py-2 bg-indigo-600 rounded-md hover:bg-indigo-500 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Absensi Siswa
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- Logout -->
                        <li>
                            <a href="logout.php" class="flex items-center px-4 py-2 bg-red-600 rounded-md hover:bg-red-500 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Logout
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 ml-64">
            <!-- Main Content -->
            <main class="p-6">
                <!-- Header Controls -->
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold dark:text-white">
                        Selamat Datang, <?php echo ucfirst($role); ?>!
                    </h1>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Dark Mode Toggle Button -->
                        <button id="darkModeToggle" class="p-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 dark:bg-gray-700 dark:hover:bg-gray-600 text-white shadow-lg transition-all duration-200">
                            <svg id="darkIcon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                            </svg>
                            <svg id="lightIcon" class="hidden w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </button>

                        <!-- Clock Display -->
                        <div id="clock" class="bg-indigo-600 dark:bg-gray-700 text-white px-4 py-2 rounded-lg shadow-lg text-lg font-semibold">
                            <!-- Clock will be updated by JS -->
                        </div>
                    </div>
                </div>

                <!-- Content Area -->
                <div class="space-y-6">
                    <?php if ($role === 'admin'): ?>
                        <!-- Status Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <!-- Hadir -->
                            <div class="status-card bg-gradient-to-br from-green-400 to-green-500 rounded-lg p-6 text-white relative overflow-hidden">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-sm opacity-80 mb-1">Hadir</p>
                                        <h3 class="text-3xl font-bold"><?php echo $status_counts['Hadir']; ?></h3>
                                        <p class="text-xs mt-2">Siswa hadir hari ini</p>
                                    </div>
                                    <div class="stat-icon bg-white/30 rounded-full p-3">
                                        <i class="fas fa-check-circle text-2xl"></i>
                                    </div>
                                </div>
                                <div class="absolute bottom-0 right-0 opacity-10 text-6xl">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>

                            <!-- Sakit -->
                            <div class="status-card bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-lg p-4 text-white">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-sm opacity-80">Sakit</p>
                                        <h3 class="text-2xl font-bold"><?php echo $status_counts['Sakit']; ?></h3>
                                    </div>
                                    <div class="bg-white/30 rounded-full p-2">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Terlambat -->
                            <div class="status-card bg-gradient-to-r from-orange-400 to-orange-500 rounded-lg p-4 text-white">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-sm opacity-80">Terlambat</p>
                                        <h3 class="text-2xl font-bold"><?php echo $status_counts['Terlambat']; ?></h3>
                                    </div>
                                    <div class="bg-white/30 rounded-full p-2">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Alpha -->
                            <div class="status-card bg-gradient-to-r from-red-400 to-red-500 rounded-lg p-4 text-white">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-sm opacity-80">Alpha</p>
                                        <h3 class="text-2xl font-bold"><?php echo $status_counts['Alpha']; ?></h3>
                                    </div>
                                    <div class="bg-white/30 rounded-full p-2">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Charts Section -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
                            <!-- Bar Chart -->
                            <div class="chart-container bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                                <div class="flex justify-between items-center mb-6">
                                    <h3 class="text-lg font-semibold dark:text-gray-200">
                                        <i class="fas fa-chart-bar mr-2"></i>Statistik Kehadiran
                                    </h3>
                                    <div class="flex space-x-2">
                                        <button class="px-3 py-1 text-sm bg-indigo-100 text-indigo-600 rounded-full hover:bg-indigo-200 transition-colors" onclick="updateChartView('daily')">Hari ini</button>
                                        <button class="px-3 py-1 text-sm bg-indigo-100 text-indigo-600 rounded-full hover:bg-indigo-200 transition-colors" onclick="updateChartView('weekly')">Minggu ini</button>
                                    </div>
                                </div>
                                <div class="h-[300px] relative">
                                    <canvas id="barChart"></canvas>
                                </div>
                            </div>

                            <!-- Line Chart -->
                            <div class="chart-container bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                                <h3 class="text-lg font-semibold mb-4 dark:text-gray-200">Trend Kehadiran</h3>
                                <div class="h-[300px] relative">
                                    <canvas id="lineChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Access Section -->
                        <div class="grid grid-cols-1 gap-6">
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
                                <h3 class="text-lg font-semibold mb-6 dark:text-gray-200">
                                    <i class="fas fa-bolt mr-2"></i>Akses Cepat
                                </h3>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <!-- Quick Access Card - Data Siswa -->
                                    <a href="siswa.php" class="p-4 rounded-lg bg-blue-100 dark:bg-blue-900 hover:transform hover:scale-105 transition-all duration-300">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="p-3 bg-blue-200 dark:bg-blue-800 rounded-full">
                                                    <i class="fas fa-users text-blue-700 dark:text-blue-300"></i>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-blue-900 dark:text-blue-200">Data Siswa</p>
                                                <p class="text-xs text-blue-800 dark:text-blue-300">Kelola data siswa</p>
                                            </div>
                                        </div>
                                    </a>

                                    <!-- Quick Access Card - Absensi -->
                                    <a href="absensi.php" class="p-4 rounded-lg bg-purple-100 dark:bg-purple-900 hover:transform hover:scale-105 transition-all duration-300">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="p-3 bg-purple-200 dark:bg-purple-800 rounded-full">
                                                    <i class="fas fa-clipboard-check text-purple-700 dark:text-purple-300"></i>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-purple-900 dark:text-purple-200">Absensi</p>
                                                <p class="text-xs text-purple-800 dark:text-purple-300">Input absensi</p>
                                            </div>
                                        </div>
                                    </a>

                                    <!-- Quick Access Card - Jadwal -->
                                    <a href="jadwal_pelajaran.php" class="p-4 rounded-lg bg-emerald-100 dark:bg-emerald-900 hover:transform hover:scale-105 transition-all duration-300">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="p-3 bg-emerald-200 dark:bg-emerald-800 rounded-full">
                                                    <i class="fas fa-calendar-alt text-emerald-700 dark:text-emerald-300"></i>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-emerald-900 dark:text-emerald-200">Jadwal</p>
                                                <p class="text-xs text-emerald-800 dark:text-emerald-300">Lihat jadwal</p>
                                            </div>
                                        </div>
                                    </a>

                                    <!-- Quick Access Card - Laporan -->
                                    <a href="#" class="p-4 rounded-lg bg-amber-100 dark:bg-amber-900 hover:transform hover:scale-105 transition-all duration-300">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="p-3 bg-amber-200 dark:bg-amber-800 rounded-full">
                                                    <i class="fas fa-chart-line text-amber-700 dark:text-amber-300"></i>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-amber-900 dark:text-amber-200">Laporan</p>
                                                <p class="text-xs text-amber-800 dark:text-amber-300">Lihat laporan</p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Data Table Section -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 data-table">
                            <h2 class="text-xl font-semibold mb-4">Siswa yang Sudah Absen Hari Ini</h2>
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foto</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Absen</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php
                                    $result = $conn->query("
                                        SELECT absen_siswa.id, data_siswa.nama, data_siswa.foto, absen_siswa.status, absen_siswa.tanggal 
                                        FROM absen_siswa 
                                        JOIN data_siswa ON absen_siswa.siswa_id = data_siswa.id 
                                        WHERE DATE(absen_siswa.tanggal) = CURDATE()
                                    ");
                                    if ($result->num_rows > 0):
                                        while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $row['nama']; ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php if ($row['foto']): ?>
                                                        <img src="uploads/<?php echo $row['foto']; ?>" alt="Foto Siswa" class="w-12 h-12 rounded-full object-cover">
                                                    <?php else: ?>
                                                        <span>Tidak ada foto</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $row['status']; ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $row['tanggal']; ?></td>
                                            </tr>
                                        <?php endwhile;
                                    else: ?>
                                        <tr>
                                            <td colspan="4" class="px-6 py -4 text-center">Tidak ada data absensi hari ini.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <a href="view_siswa.php?role=tamu" class="bg-white p-6 rounded-lg shadow-md text-center hover:bg-gray-50 transition">
                                <h2 class="text-xl font-semibold">Lihat Data Siswa</h2>
                            </a>
                            <a href="view_absensi.php?role=tamu" class="bg-white p-6 rounded-lg shadow-md text-center hover:bg-gray-50 transition">
                                <h2 class="text-xl font-semibold">Lihat Absensi Siswa</h2>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Clock functionality
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const clockElement = document.getElementById('clock');
            clockElement.textContent = `${hours}:${minutes}:${seconds}`;
        }

        setInterval(updateClock, 1000);
        updateClock();

        // Dark Mode Management
        const darkModeToggle = document.getElementById('darkModeToggle');
        const darkIcon = document.getElementById('darkIcon');
        const lightIcon = document.getElementById('lightIcon');
        const html = document.documentElement;
        let isDark = localStorage.getItem('darkMode') === 'enabled';

        function toggleDarkMode() {
            isDark = !isDark;
            html.classList.toggle('dark', isDark);
            darkIcon.classList.toggle('hidden', !isDark);
            lightIcon.classList.toggle('hidden', isDark);
            localStorage.setItem('darkMode', isDark ? 'enabled' : 'disabled');
            updateChartsTheme(isDark);
        }

        // Initialize dark mode
        if (isDark) {
            html.classList.add('dark');
            darkIcon.classList.remove('hidden');
            lightIcon.classList.add('hidden');
        } else {
            darkIcon.classList.add('hidden');
            lightIcon.classList.remove('hidden');
        }

        // Add event listener
        darkModeToggle.addEventListener('click', toggleDarkMode);

        // Chart & Dark Mode Management
        let barChart, lineChart;

        function initializeCharts() {
            const isDark = document.documentElement.classList.contains('dark');
            const colors = getChartColors(isDark);

            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: colors.grid },
                        ticks: { color: colors.text }
                    },
                    x: {
                        grid: { color: colors.grid },
                        ticks: { color: colors.text }
                    }
                },
                plugins: {
                    legend: {
                        labels: { color: colors.text }
                    }
                }
            };

            // Initialize Bar Chart
            const barCtx = document.getElementById('barChart');
            if (barCtx) {
                barChart = new Chart(barCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Hadir', 'Sakit', 'Terlambat', 'Alpha'],
                        datasets: [{
                            label: 'Jumlah Siswa',
                            data: [
                                <?php echo $status_counts['Hadir']; ?>,
                                <?php echo $status_counts['Sakit']; ?>,
                                <?php echo $status_counts['Terlambat']; ?>,
                                <?php echo $status_counts['Alpha']; ?>
                            ],
                            backgroundColor: [
                                'rgba(34, 197, 94, 0.6)',
                                'rgba(234, 179, 8, 0.6)', 
                                'rgba(249, 115, 22, 0.6)',
                                'rgba(239, 68, 68, 0.6)'
                            ],
                            borderColor: [
                                'rgb(34, 197, 94)',
                                'rgb(234, 179, 8)',
                                'rgb(249, 115, 22)',
                                'rgb(239, 68, 68)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: commonOptions
                });
            }

            // Initialize Line Chart
            const lineCtx = document.getElementById('lineChart');
            if (lineCtx) {
                lineChart = new Chart(lineCtx, {
                    type: 'line',
                    data: {
                        labels: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'],
                        datasets: [{
                            label: 'Kehadiran Minggu Ini',
                            data: [
                                <?php echo $status_counts['Hadir']; ?>,
                                <?php echo $status_counts['Hadir']-1; ?>,
                                <?php echo $status_counts['Hadir']+2; ?>,
                                <?php echo $status_counts['Hadir']-2; ?>,
                                <?php echo $status_counts['Hadir']+1; ?>
                            ],
                            fill: false,
                            borderColor: 'rgb(59, 130, 246)',
                            tension: 0.1,
                            pointBackgroundColor: 'rgb(59, 130, 246)',
                            pointBorderColor: isDark ? '#2d3748' : '#ffffff',
                            pointHoverBackgroundColor: isDark ? '#2d3748' : '#ffffff',
                            pointHoverBorderColor: 'rgb(59, 130, 246)'
                        }]
                    },
                    options: commonOptions
                });
            }
        }

        // Initialize charts after page load
        document.addEventListener('DOMContentLoaded', initializeCharts);

        // Update charts when toggling dark mode
        function updateChartsTheme(isDark) {
            if (barChart) barChart.destroy();
            if (lineChart) lineChart.destroy();
            initializeCharts();
        }

        function getChartColors(isDark) {
            return {
                text: isDark ? '#e2e8f0' : '#1a202c',
                grid: isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
            };
        }

        // Enhanced chart animations
        Chart.defaults.animation.duration = 2000;
        Chart.defaults.animation.easing = 'easeInOutQuart';

        // Add hover effects for chart elements
        const chartHoverEffects = {
            onHover: (event, elements) => {
                event.native.target.style.cursor = elements.length ? 'pointer' : 'default';
                if (elements.length) {
                    elements[0].element.options.borderWidth = 2;
                    elements[0].element.options.borderColor = '#4F46E5';
                }
            }
        };

        // Add these options to your charts configuration
        const enhancedChartOptions = {
            ...commonOptions,
            plugins: {
                ...commonOptions.plugins,
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleFont: { weight: 'bold' },
                    padding: 12,
                    cornerRadius: 8,
                    usePointStyle: true
                }
            },
            interaction: chartHoverEffects
        };

        // Add smooth scrolling for table
        document.querySelector('.data-table').addEventListener('scroll', (e) => {
            e.target.style.scrollBehavior = 'smooth';
        });

        // Add loading state simulation
        function showLoading() {
            document.querySelectorAll('.status-card, .chart-container').forEach(el => {
                el.classList.add('loading');
            });
            setTimeout(() => {
                document.querySelectorAll('.status-card, .chart-container').forEach(el => {
                    el.classList.remove('loading');
                });
            }, 1000);
        }

        // Refresh data periodically
        setInterval(() => {
            showLoading();
            // Add your data refresh logic here
        }, 300000); // Refresh every 5 minutes
    </script>
</body>
</html>