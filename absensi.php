<?php
session_start();

// Set zona waktu
date_default_timezone_set('Asia/Jakarta'); // Ganti dengan zona waktu yang sesuai

// Periksa apakah pengguna adalah admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "absensi_sekolah");

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Tambah absensi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_absensi'])) {
    $current_time = date('H:i:s');
    $start_time = '06:00:00';
    $late_time = '07:00:00';
    $end_time = '23:30:00';

    if ($current_time >= $start_time && $current_time <= $end_time) {
        $siswa_id = $_POST['siswa_id'];
        $status = $_POST['status'];
        
        // Jika waktu sudah lewat dari jam 7, status hadir otomatis jadi terlambat
        if ($current_time > $late_time && $status == 'Hadir') {
            $status = 'Terlambat';
        }

        $stmt = $conn->prepare("INSERT INTO absen_siswa (siswa_id, status) VALUES (?, ?)");
        $stmt->bind_param("is", $siswa_id, $status);
        $stmt->execute();
        $stmt->close();
    } else {
        header("Location: absensi.php?error=1");
        exit();
    }

    header("Location: absensi.php");
    exit();
}

// Hapus absensi
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM absen_siswa WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Reset auto-increment jika tidak ada data lagi
    $result = $conn->query("SELECT COUNT(*) AS total FROM absen_siswa");
    $row = $result->fetch_assoc();
    if ($row['total'] == 0) {
        $conn->query("ALTER TABLE absen_siswa AUTO_INCREMENT = 1");
    }

    // Redirect untuk menghindari duplikasi data saat refresh
    header("Location: absensi.php");
    exit();
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
$late_time = '07:00:00';
$end_time = '23:30:00';
$is_absensi_active = ($current_time >= $start_time && $current_time <= $end_time);
$is_on_time = ($current_time >= $start_time && $current_time <= $late_time);
?>

<!DOCTYPE html>
<html lang="en">
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
        
        .dark .form-select {
            background-color: #2d3748;
            color: #e2e8f0;
            border-color: #4a5568;
        }
        
        .dark .form-select option {
            background-color: #2d3748;
            color: #e2e8f0;
        }
        
        .dark table td {
            color: #e2e8f0 !important;
        }
        
        .dark .shadow-md {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <aside class="w-64 bg-indigo-700 text-white min-h-screen fixed">
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Panel Admin</h1>
            <nav>
                <ul class="space-y-4">
                    <li>
                        <a href="dashboard.php" class="flex items-center px-4 py-2 bg-indigo-600 rounded-md hover:bg-indigo-500 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0l-2-2m2 2V4a1 1 0 011-1h3m-3 4l2-2" />
                            </svg>
                            Dashboard
                        </a>
                    </li>
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

    <!-- Main Content -->
    <main class="flex-1 ml-64 p-6 overflow-y-auto">
        <!-- Header Controls -->
        <div class="flex justify-between items-center p-6 bg-white dark:bg-gray-800 shadow-md mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Absensi Siswa</h1>
            
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
            </div>
        </div>

        <!-- Form tambah absensi -->
        <?php if ($is_absensi_active): ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-800 dark:text-white">Tambah Absensi</h2>
                <?php if (!$is_on_time): ?>
                    <div class="bg-yellow-100 dark:bg-yellow-800 text-yellow-700 dark:text-yellow-100 p-4 rounded-lg mb-4">
                        <p>Waktu sudah lewat jam 07:00. Status "Hadir" akan otomatis tercatat sebagai "Terlambat".</p>
                    </div>
                <?php endif; ?>
                <form method="POST" action="" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <select name="siswa_id" class="form-select w-full p-2 rounded-lg border border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-indigo-500" required>
                            <option value="">Pilih Siswa</option>
                            <?php
                            $siswa_result = $conn->query("SELECT * FROM data_siswa");
                            while ($row = $siswa_result->fetch_assoc()):
                                echo "<option value='" . $row['id'] . "'>" . $row['nama'] . "</option>";
                            endwhile;
                            ?>
                        </select>
                        <select name="status" class="form-select w-full p-2 rounded-lg border border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-indigo-500" required>
                            <option value="">Pilih Status</option>
                            <option value="Hadir">Hadir</option>
                            <option value="Sakit">Sakit</option>
                            <option value="Terlambat">Terlambat</option>
                            <option value="Alpha">Alpha</option>
                        </select>
                    </div>
                    <button type="submit" name="add_absensi" class="w-full md:w-auto px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors duration-200">
                        Tambah Absensi
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="bg-red-100 text-red-700 p-4 rounded-lg shadow-md mb-6">
                <p>Waktu absensi sudah lewat. Absensi hanya tersedia antara 06:00 - 09:30.</p>
            </div>
        <?php endif; ?>

        <!-- Tabel absensi -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-800 dark:text-white">Data Absensi</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">No.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nama Siswa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Foto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php
                        if ($result->num_rows > 0):
                            $no = 1; // Nomor urut
                            while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-800 dark:text-gray-300"><?php echo $no++; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-800 dark:text-gray-300"><?php echo $row['nama']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($row['foto']): ?>
                                            <img src="uploads/<?php echo $row['foto']; ?>" alt="Foto Siswa" class="w-12 h-12 rounded-full object-cover">
                                        <?php else: ?>
                                            <span class="text-gray-800 dark:text-gray-300">Tidak ada foto</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-800 dark:text-gray-300"><?php echo $row['status']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-800 dark:text-gray-300"><?php echo $row['tanggal']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap space-x-2">
                                        <a href="?delete=<?php echo $row['id']; ?>" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                            Hapus
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-800 dark:text-gray-300">Tidak ada data absensi.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

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