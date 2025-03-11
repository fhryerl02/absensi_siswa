<?php
session_start();
require_once 'notification.php';

// Set zona waktu
date_default_timezone_set('Asia/Jakarta');

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

// Check and create mata_pelajaran column if it doesn't exist
$result = $conn->query("SHOW COLUMNS FROM absen_siswa LIKE 'mata_pelajaran'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE absen_siswa ADD COLUMN mata_pelajaran VARCHAR(100) NOT NULL DEFAULT '' AFTER status";
    $conn->query($sql);
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
        $mata_pelajaran = $_POST['mata_pelajaran']; // New field
        
        // Jika waktu sudah lewat dari jam 7, status hadir otomatis jadi terlambat
        if ($current_time > $late_time && $status == 'Hadir') {
            $status = 'Terlambat';
        }

        // Updated query to include mata_pelajaran
        $stmt = $conn->prepare("INSERT INTO absen_siswa (siswa_id, status, mata_pelajaran) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $siswa_id, $status, $mata_pelajaran);
        if ($stmt->execute()) {
            $_SESSION['notification'] = "Absensi berhasil ditambahkan!";
            $_SESSION['notification_type'] = "success";
        } else {
            $_SESSION['notification'] = "Gagal menambahkan absensi!";
            $_SESSION['notification_type'] = "error";
        }
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
    if ($stmt->execute()) {
        $_SESSION['notification'] = "Absensi berhasil dihapus!";
        $_SESSION['notification_type'] = "success";
    } else {
        $_SESSION['notification'] = "Gagal menghapus absensi!";
        $_SESSION['notification_type'] = "error";
    }
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
$hasMataPelajaran = false;
$check = $conn->query("SHOW COLUMNS FROM absen_siswa LIKE 'mata_pelajaran'");
if ($check && $check->num_rows > 0) {
    $hasMataPelajaran = true;
}

if ($hasMataPelajaran) {
    $query = "
        SELECT absen_siswa.id, data_siswa.nama, data_siswa.foto, absen_siswa.status, 
        IFNULL(absen_siswa.mata_pelajaran, '-') as mata_pelajaran, 
        absen_siswa.tanggal 
        FROM absen_siswa 
        JOIN data_siswa ON absen_siswa.siswa_id = data_siswa.id
    ";
} else {
    $query = "
        SELECT absen_siswa.id, data_siswa.nama, data_siswa.foto, absen_siswa.status,
        '-' as mata_pelajaran,
        absen_siswa.tanggal 
        FROM absen_siswa 
        JOIN data_siswa ON absen_siswa.siswa_id = data_siswa.id
    ";
}
$result = $conn->query($query);

if (!$result) {
    // If query fails, try fallback query without mata_pelajaran
    $query = "
        SELECT absen_siswa.id, data_siswa.nama, data_siswa.foto, absen_siswa.status, 
        absen_siswa.tanggal 
        FROM absen_siswa 
        JOIN data_siswa ON absen_siswa.siswa_id = data_siswa.id
    ";
    $result = $conn->query($query);
}

// Periksa waktu absensi
$current_time = date('H:i:s');
$start_time = '06:00:00';
$late_time = '07:00:00';
$end_time = '23:30:00';
$is_absensi_active = ($current_time >= $start_time && $current_time <= $end_time);
$is_on_time = ($current_time >= $start_time && $current_time <= $late_time);

if (isset($_SESSION['notification'])) {
    show_notification($_SESSION['notification'], $_SESSION['notification_type']);
    unset($_SESSION['notification']);
    unset($_SESSION['notification_type']);
}
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
        /* Base Transitions */
        * {
            transition: all 0.3s ease;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #c7d2fe;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #818cf8;
        }

        /* Card Animations */
        .card-hover {
            transform: translateY(0);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        /* Status Badges */
        .status-badge {
            @apply px-3 py-1 rounded-full text-sm font-medium;
            animation: fadeIn 0.5s ease-out;
        }

        .status-hadir { @apply bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200; }
        .status-sakit { @apply bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200; }
        .status-terlambat { @apply bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200; }
        .status-alpha { @apply bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200; }

        /* Enhanced Form Elements */
        .form-select {
            background-image: url("data:image/svg+xml,...");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }

        .form-select:focus {
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
            border-color: #6366f1;
        }

        /* Table Enhancements */
        .table-row {
            position: relative;
            overflow: hidden;
        }

        .table-row::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 2px;
            background: linear-gradient(to right, transparent, #6366f1, transparent);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .table-row:hover::after {
            transform: scaleX(1);
        }

        /* Action Buttons */
        .btn-action {
            @apply inline-flex items-center px-3 py-2 border border-transparent 
                   text-sm font-medium rounded-md transition-all duration-200
                   focus:outline-none focus:ring-2 focus:ring-offset-2;
        }

        .btn-delete {
            @apply text-red-700 bg-red-100 hover:bg-red-200 
                   focus:ring-red-500 hover:shadow-md;
        }

        /* Loading Animation */
        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }

        .loading {
            animation: shimmer 2s infinite linear;
            background: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
            background-size: 1000px 100%;
        }

        /* Modal Animation */
        .modal-enter {
            animation: modalEnter 0.3s ease-out;
        }

        @keyframes modalEnter {
            from { 
                opacity: 0;
                transform: scale(0.95);
            }
            to { 
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Dark Mode Enhancements */
        .dark body {
            background-color: #111827;
        }

        .dark .bg-white {
            background-color: #1F2937;
        }

        .dark .text-gray-800 {
            color: #F3F4F6;
        }

        .dark .border-gray-200 {
            border-color: #374151;
        }

        .dark .form-select {
            background-color: #374151;
            color: #F3F4F6;
            border-color: #4B5563;
        }

        .dark .form-select option {
            background-color: #374151;
            color: #F3F4F6;
        }

        /* Enhanced Card Styling */
        .card {
            @apply bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden transition-all duration-300;
        }

        .card:hover {
            @apply transform -translate-y-1 shadow-xl;
        }

        /* Enhanced Table Styling */
        .table-container {
            @apply overflow-hidden bg-white dark:bg-gray-800 rounded-xl shadow-lg;
        }

        .table-header {
            @apply bg-gray-50 dark:bg-gray-700 px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider;
        }

        .table-cell {
            @apply px-6 py-4 whitespace-nowrap text-gray-800 dark:text-gray-300;
        }

        /* Status Badge Enhancements */
        .status-badge {
            @apply px-3 py-1 rounded-full text-sm font-medium inline-flex items-center space-x-1;
        }

        .status-hadir {
            @apply bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200;
        }

        .status-sakit {
            @apply bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200;
        }

        .status-terlambat {
            @apply bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200;
        }

        .status-alpha {
            @apply bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200;
        }

        /* Form Input Enhancements */
        .form-input {
            @apply w-full rounded-lg border-gray-300 dark:border-gray-600 
                   dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 
                   focus:ring-2 focus:ring-indigo-500 dark:focus:border-indigo-600;
        }

        /* Button Enhancements */
        .btn-primary {
            @apply px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 
                   dark:bg-indigo-500 dark:hover:bg-indigo-600 transition-all duration-200
                   focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500;
        }

        .btn-danger {
            @apply px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 
                   dark:bg-red-500 dark:hover:bg-red-600 transition-all duration-200
                   focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500;
        }

        /* Search Input Enhancement */
        .search-input {
            @apply pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                   focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                   dark:bg-gray-700 dark:text-gray-300;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    
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
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-6 card-hover">
                <h2 class="text-xl font-semibold mb-4 text-gray-800 dark:text-white flex items-center">
                    <i class="fas fa-user-check mr-2 text-indigo-500"></i>
                    Tambah Absensi
                </h2>
                <?php if (!$is_on_time): ?>
                    <div class="bg-yellow-100 dark:bg-yellow-800 text-yellow-700 dark:text-yellow-100 p-4 rounded-lg mb-4">
                        <p>Waktu sudah lewat jam 07:00. Status "Hadir" akan otomatis tercatat sebagai "Terlambat".</p>
                    </div>
                <?php endif; ?>
                <form method="POST" action="" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Enhanced Select Fields -->
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Pilih Siswa
                            </label>
                            <div class="relative">
                                <select name="siswa_id" required 
                                        class="form-select w-full p-2.5 rounded-lg border border-gray-300 
                                               dark:border-gray-600 bg-white dark:bg-gray-700">
                                    <option value="">Pilih Siswa</option>
                                    <?php
                                    $siswa_result = $conn->query("SELECT * FROM data_siswa");
                                    while ($row = $siswa_result->fetch_assoc()):
                                        echo "<option value='" . $row['id'] . "' class='py-2'>" . $row['nama'] . "</option>";
                                    endwhile;
                                    ?>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Pilih Mata Pelajaran
                            </label>
                            <div class="relative">
                                <select name="mata_pelajaran" required 
                                        class="form-select w-full p-2.5 rounded-lg border border-gray-300 
                                               dark:border-gray-600 bg-white dark:bg-gray-700">
                                    <option value="">Pilih Mata Pelajaran</option>
                                    <option value="Matematika">Matematika</option>
                                    <option value="Bahasa Indonesia">Bahasa Indonesia</option>
                                    <option value="IPA">IPA</option>
                                    <option value="IPS">IPS</option>
                                    <option value="Bahasa Inggris">Bahasa Inggris</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Pilih Status
                            </label>
                            <div class="relative">
                                <select name="status" required 
                                        class="form-select w-full p-2.5 rounded-lg border border-gray-300 
                                               dark:border-gray-600 bg-white dark:bg-gray-700">
                                    <option value="">Pilih Status</option>
                                    <option value="Hadir">Hadir</option>
                                    <option value="Sakit">Sakit</option>
                                    <option value="Terlambat">Terlambat</option>
                                    <option value="Alpha">Alpha</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="add_absensi" 
                            class="btn-action bg-indigo-600 hover:bg-indigo-700 text-white
                                   transform hover:scale-105 hover:shadow-lg">
                        <i class="fas fa-plus-circle mr-2"></i>
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
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden card-hover">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center">
                    <i class="fas fa-list mr-2 text-indigo-500"></i>
                    Data Absensi
                </h2>
            </div>

            <!-- Add search and filter controls -->
            <div class="p-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                <div class="flex flex-wrap gap-4 items-center justify-between">
                    <div class="relative">
                        <input type="text" id="searchInput" 
                               class="pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                                      focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                                      dark:bg-gray-800 dark:text-gray-300"
                               placeholder="Cari...">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
            </div>

            <!-- Enhanced table styling -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">No.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nama Siswa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Foto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Mata Pelajaran</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php
                        if ($result->num_rows > 0):
                            $no = 1; // Nomor urut
                            while ($row = $result->fetch_assoc()): ?>
                                <tr class="table-row">
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
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-800 dark:text-gray-300"><?php echo $row['mata_pelajaran']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-800 dark:text-gray-300"><?php echo $row['tanggal']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap space-x-2">
                                        <!-- Modified delete link with modal trigger -->
                                        <a href="?delete=<?php echo $row['id']; ?>" onclick="event.preventDefault(); showDeleteModal(this.href);" class="btn-action btn-delete">
                                            Hapus
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-800 dark:text-gray-300">Tidak ada data absensi.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Confirmation -->
    <div id="deleteModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg max-w-sm w-full p-6 modal-enter">
            <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-100">Konfirmasi Hapus</h3>
            <p class="mb-6 text-gray-600 dark:text-gray-300">Apakah Anda yakin ingin menghapus data ini?</p>
            <div class="flex justify-end space-x-4">
                <button id="cancelBtn" class="px-4 py-2 rounded-md bg-gray-300 hover:bg-gray-400 text-gray-800">Batal</button>
                <button id="confirmBtn" class="px-4 py-2 rounded-md bg-red-600 hover:bg-red-700 text-white">Hapus</button>
            </div>
        </div>
    </div>

    <!-- Delete Modal Script -->
    <script>
        let deleteUrl = '';
        function showDeleteModal(url) {
            deleteUrl = url;
            document.getElementById('deleteModal').classList.remove('hidden');
        }
        document.getElementById('cancelBtn').addEventListener('click', function(){
            document.getElementById('deleteModal').classList.add('hidden');
        });
        document.getElementById('confirmBtn').addEventListener('click', function(){
            window.location.href = deleteUrl;
        });
    </script>
    
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