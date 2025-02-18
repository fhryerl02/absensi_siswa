<?php
session_start();

// Periksa apakah pengguna adalah siswa
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
    header("Location: login.php");
    exit();
}

// Set zona waktu
date_default_timezone_set('Asia/Jakarta');

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "absensi_sekolah");

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil data siswa yang sedang login
$siswa_id = $_SESSION['siswa_id'];

// Tambah absensi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_absensi'])) {
    $current_time = date('H:i:s');
    $start_time = '06:00:00';
    $end_time = '23:30:00';
    $late_time = '07:00:00';

    if ($current_time >= $start_time && $current_time <= $end_time) {
        $status = $_POST['status'];
        
        // Jika waktu sudah lewat dari jam 7, status otomatis jadi terlambat
        if ($current_time > $late_time && $status == 'Hadir') {
            $status = 'Terlambat';
        }
        
        $stmt = $conn->prepare("INSERT INTO absen_siswa (siswa_id, status) VALUES (?, ?)");
        $stmt->bind_param("is", $siswa_id, $status);
        $stmt->execute();
        $stmt->close();
    } else {
        header("Location: siswa_absensi.php?error=1");
        exit();
    }

    header("Location: siswa_absensi.php");
    exit();
}

// Query untuk menampilkan data absensi siswa
$query = "
    SELECT absen_siswa.id, data_siswa.nama, data_siswa.foto, absen_siswa.status, absen_siswa.tanggal 
    FROM absen_siswa 
    JOIN data_siswa ON absen_siswa.siswa_id = data_siswa.id
    WHERE siswa_id = ?
    ORDER BY tanggal DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $siswa_id);
$stmt->execute();
$result = $stmt->get_result();

// Periksa waktu absensi
$current_time = date('H:i:s');
$start_time = '06:00:00';
$late_time = '07:00:00';
$end_time = '23:30:00';
$is_absensi_active = ($current_time >= $start_time && $current_time <= $end_time);
$is_on_time = ($current_time >= $start_time && $current_time <= $late_time);

// Periksa apakah sudah absen hari ini
$today = date('Y-m-d');
$check_query = "SELECT id FROM absen_siswa WHERE siswa_id = ? AND DATE(tanggal) = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("is", $siswa_id, $today);
$check_stmt->execute();
$already_attended = $check_stmt->get_result()->num_rows > 0;
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Siswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
        
        .dark table td {
            color: #e2e8f0 !important;
        }
        
        .dark .shadow-md {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900">
    <div class="container mx-auto p-6">
        <!-- Header Controls -->
        <div class="flex justify-between items-center p-6 bg-white dark:bg-gray-800 shadow-md mb-6 rounded-lg">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Absensi Siswa - <?php echo $_SESSION['siswa_nama']; ?></h1>
            
            <div class="flex items-center space-x-4">
                <!-- Dark Mode Toggle -->
                <button id="darkModeToggle" class="p-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 dark:bg-gray-700 dark:hover:bg-gray-600 text-white shadow-lg transition-all duration-200">
                    <svg id="darkIcon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <svg id="lightIcon" class="hidden w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </button>

                <!-- Logout Button -->
                <a href="logout.php" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </a>
            </div>
        </div>

        <!-- Form Absensi -->
        <?php if ($is_absensi_active && !$already_attended): ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-800 dark:text-white">Absen Hari Ini</h2>
                <?php if (!$is_on_time): ?>
                    <div class="bg-yellow-100 dark:bg-yellow-800 text-yellow-700 dark:text-yellow-100 p-4 rounded-lg mb-4">
                        <p>Anda melewati jam 07:00. Absensi akan tercatat sebagai "Terlambat".</p>
                    </div>
                <?php endif; ?>
                <form method="POST" action="" class="space-y-4">
                    <div class="grid grid-cols-1 gap-4">
                        <select name="status" class="w-full p-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500" required>
                            <option value="">Pilih Status</option>
                            <?php if ($is_on_time): ?>
                                <option value="Hadir">Hadir</option>
                            <?php else: ?>
                                <option value="Terlambat">Terlambat</option>
                            <?php endif; ?>
                            <option value="Sakit">Sakit</option>
                            <option value="Izin">Izin</option>
                        </select>
                    </div>
                    <button type="submit" name="add_absensi" class="w-full md:w-auto px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors duration-200">
                        Submit Absensi
                    </button>
                </form>
            </div>
        <?php elseif ($already_attended): ?>
            <div class="bg-green-100 dark:bg-green-800 text-green-700 dark:text-green-100 p-4 rounded-lg shadow-md mb-6">
                <p>Anda sudah melakukan absensi hari ini.</p>
            </div>
        <?php else: ?>
            <div class="bg-red-100 dark:bg-red-800 text-red-700 dark:text-red-100 p-4 rounded-lg shadow-md mb-6">
                <p>Waktu absensi sudah lewat atau belum dimulai. Absensi hanya tersedia antara 06:00 - 23:30.</p>
            </div>
        <?php endif; ?>

        <!-- Tabel Riwayat Absensi -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-800 dark:text-white">Riwayat Absensi</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php if ($result->num_rows > 0):
                            $no = 1;
                            while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-800 dark:text-gray-300"><?php echo $no++; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-800 dark:text-gray-300"><?php echo $row['tanggal']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-800 dark:text-gray-300"><?php echo $row['status']; ?></td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-gray-800 dark:text-gray-300">Belum ada riwayat absensi.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Dark Mode Script -->
    <script>
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
    </script>
</body>
</html>
