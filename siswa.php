<?php
session_start();

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

// Handle pencarian
$search = "";
$query = "SELECT * FROM data_siswa"; // Query default
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $query = "SELECT * FROM data_siswa WHERE nama LIKE '%$search%' OR kelas LIKE '%$search%' OR jurusan LIKE '%$search%'";
}

// Handle filter kelas dan jurusan
$kelas_filter = "";
$jurusan_filter = "";
if (isset($_GET['kelas'])) {
    $kelas_filter = $_GET['kelas'];
    $query .= " AND kelas = '$kelas_filter'";
}
if (isset($_GET['jurusan'])) {
    $jurusan_filter = $_GET['jurusan'];
    $query .= " AND jurusan = '$jurusan_filter'";
}

// Tambah data siswa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_siswa'])) {
    $nama = $_POST['nama'];
    $kelas = $_POST['kelas'];
    $jurusan = $_POST['jurusan'];
    $foto = null;

    // Proses upload foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $target_dir = "uploads/"; // Folder penyimpanan foto
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Buat folder jika belum ada
        }
        $file_name = uniqid() . "_" . basename($_FILES['foto']['name']); // Nama file unik
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validasi tipe file
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
                $foto = $file_name; // Simpan nama file di database
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO data_siswa (nama, kelas, jurusan, foto) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nama, $kelas, $jurusan, $foto);
    $stmt->execute();
    $stmt->close();

    // Redirect untuk menghindari duplikasi data saat refresh
    header("Location: siswa.php");
    exit();
}

// Edit data siswa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_siswa'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $kelas = $_POST['kelas'];
    $jurusan = $_POST['jurusan'];
    $foto = null;

    // Ambil data lama untuk mendapatkan nama foto sebelumnya
    $stmt = $conn->prepare("SELECT foto FROM data_siswa WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $old_foto = $row['foto'];
    $stmt->close();

    // Proses upload foto baru
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $target_dir = "uploads/"; // Folder penyimpanan foto
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Buat folder jika belum ada
        }
        $file_name = uniqid() . "_" . basename($_FILES['foto']['name']); // Nama file unik
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validasi tipe file
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
                $foto = $file_name; // Simpan nama file di database

                // Hapus foto lama jika ada
                if ($old_foto && file_exists($target_dir . $old_foto)) {
                    unlink($target_dir . $old_foto);
                }
            }
        }
    } else {
        $foto = $old_foto; // Gunakan foto lama jika tidak ada foto baru
    }

    $stmt = $conn->prepare("UPDATE data_siswa SET nama = ?, kelas = ?, jurusan = ?, foto = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $nama, $kelas, $jurusan, $foto, $id);
    $stmt->execute();
    $stmt->close();

    // Redirect untuk menghindari duplikasi data saat refresh
    header("Location: siswa.php");
    exit();
}

// Hapus data siswa
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Ambil nama foto sebelum menghapus data
    $stmt = $conn->prepare("SELECT foto FROM data_siswa WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $foto = $row['foto'];
    $stmt->close();

    // Hapus data siswa
    $stmt = $conn->prepare("DELETE FROM data_siswa WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Hapus foto dari folder jika ada
    if ($foto && file_exists("uploads/" . $foto)) {
        unlink("uploads/" . $foto);
    }

    // Reset auto-increment dan urutkan ulang ID
    reorderIds($conn);

    // Redirect untuk menghindari duplikasi data saat refresh
    header("Location: siswa.php");
    exit();
}

// Fungsi untuk mengurutkan ulang ID
function reorderIds($conn) {
    // Ambil semua data siswa
    $result = $conn->query("SELECT * FROM data_siswa ORDER BY id ASC");
    if ($result->num_rows > 0) {
        $new_id = 1;
        while ($row = $result->fetch_assoc()) {
            $old_id = $row['id'];
            $nama = $row['nama'];
            $kelas = $row['kelas'];
            $jurusan = $row['jurusan'];
            $foto = $row['foto'];

            // Update ID menjadi urutan baru
            $stmt = $conn->prepare("UPDATE data_siswa SET id = ? WHERE id = ?");
            $stmt->bind_param("ii", $new_id, $old_id);
            $stmt->execute();
            $stmt->close();

            $new_id++;
        }
    }

    // Reset auto-increment
    $conn->query("ALTER TABLE data_siswa AUTO_INCREMENT = 1");
}

// Eksekusi query untuk menampilkan data siswa
$result = $conn->query($query);

// Ambil daftar kelas dan jurusan untuk slide bar
$kelas_result = $conn->query("SELECT DISTINCT kelas FROM data_siswa");
$jurusan_result = $conn->query("SELECT DISTINCT jurusan FROM data_siswa");
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa</title>
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

        .dark .form-select {
            background-color: #2d3748;
            color: #e2e8f0;
            border-color: #4a5568;
        }
        
        .dark .form-select option {
            background-color: #2d3748;
            color: #e2e8f0;
        }

        .dark input[type="text"],
        .dark input[type="file"],
        .dark .form-input {
            background-color: #2d3748;
            color: #e2e8f0;
            border-color: #4a5568;
        }

        .dark input[type="text"]::placeholder {
            color: #9ca3af;
        }

        .dark .text-gray-700,
        .dark .text-gray-800,
        .dark .text-gray-900 {
            color: #e2e8f0;
        }

        .dark .bg-indigo-50 {
            background-color: #374151;
        }

        .dark .bg-indigo-100 {
            background-color: #374151;
        }

        .dark .text-indigo-700 {
            color: #93c5fd;
        }

        .dark .bg-red-100 {
            background-color: #4b2631;
        }

        .dark .text-red-700 {
            color: #fca5a5;
        }

        .dark .modal-content {
            background-color: #2d3748;
            color: #e2e8f0;
        }

        .dark .bg-gray-50 {
            background-color: #374151;
        }

        .dark .text-gray-500 {
            color: #9ca3af;
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
                    <!-- Tombol Dashboard -->
                    <li>
                        <a href="dashboard.php" class="flex items-center px-4 py-2 bg-indigo-600 rounded-md hover:bg-indigo-500 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0l-2-2m2 2V4a1 1 0 011-1h3m-3 4l2-2" />
                            </svg>
                            Dashboard
                        </a>
                    </li>

                    <!-- Menu Data Siswa -->
                    <li>
                        <a href="siswa.php" class="flex items-center px-4 py-2 bg-indigo-600 rounded-md hover:bg-indigo-500 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Data Siswa
                        </a>
                    </li>

                    <!-- Menu Absensi Siswa -->
                    <li>
                        <a href="absensi.php" class="flex items-center px-4 py-2 bg-indigo-600 rounded-md hover:bg-indigo-500 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Absensi Siswa
                        </a>
                    </li>

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

    <!-- Main Content -->
    <main class="flex-1 ml-64 p-6">
        <!-- Header Controls -->
        <div class="flex justify-between items-center p-6 bg-white dark:bg-gray-800 shadow-md mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Data Siswa</h1>
            
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

        <!-- Search Bar -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <form method="GET" action="" class="flex items-center">
                <input type="text" name="search" placeholder="Cari siswa..." value="<?php echo htmlspecialchars($search); ?>" 
                    class="px-4 py-2 border border-gray-300 rounded-l-md w-full focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button type="submit" 
                    class="bg-indigo-600 text-white px-4 py-2 rounded-r-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cari
                </button>
            </div>
        </form>

        <!-- Form tambah/edit siswa -->
        <?php
        $edit_mode = false;
        $id = $nama = $kelas = $jurusan = $foto = "";

        if (isset($_GET['edit'])) {
            $edit_mode = true;
            $id = $_GET['edit'];
            $stmt = $conn->prepare("SELECT * FROM data_siswa WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result_edit = $stmt->get_result();
            $row = $result_edit->fetch_assoc();
            $nama = $row['nama'];
            $kelas = $row['kelas'];
            $jurusan = $row['jurusan'];
            $foto = $row['foto'];
            $stmt->close();
        }
        ?>
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <form method="POST" action="" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mb-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-200"><?php echo $edit_mode ? "Edit Siswa" : "Tambah Siswa"; ?></h2>
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input type="text" name="nama" placeholder="Nama" value="<?php echo $nama; ?>" 
                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-gray-200" required>
                    <input type="text" name="kelas" placeholder="Kelas" value="<?php echo $kelas; ?>" 
                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-gray-200" required>
                    <input type="text" name="jurusan" placeholder="Jurusan" value="<?php echo $jurusan; ?>" 
                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-gray-200" required>
                    <div class="col-span-1 md:col-span-3">
                        <label for="foto" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Foto Siswa</label>
                        <input type="file" name="foto" id="foto" 
                            class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 dark:file:bg-gray-700 file:text-indigo-700 dark:file:text-indigo-300 hover:file:bg-indigo-100 dark:hover:file:bg-gray-600">
                        <?php if ($foto): ?>
                            <div class="mt-2">
                                <img src="uploads/<?php echo $foto; ?>" alt="Foto Siswa" class="w-24 h-24 rounded-full object-cover">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <button type="submit" name="<?php echo $edit_mode ? 'edit_siswa' : 'add_siswa'; ?>" 
                    class="mt-4 bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:ring-offset-gray-800">
                    <?php echo $edit_mode ? "Simpan Perubahan" : "Tambah Siswa"; ?>
                </button>
            </form>
        </div>

        <!-- Tabel data siswa -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jurusan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php
                    if ($result->num_rows > 0):
                        while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-800"><?php echo $row['id']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-800"><?php echo $row['nama']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-800"><?php echo $row['kelas']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-800"><?php echo $row['jurusan']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($row['foto']): ?>
                                        <img src="uploads/<?php echo $row['foto']; ?>" alt="Foto Siswa" class="w-12 h-12 rounded-full object-cover">
                                    <?php else: ?>
                                        <span class="text-gray-800">Tidak ada foto</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap space-x-2">
                                    <a href="?edit=<?php echo $row['id']; ?>" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Edit
                                    </a>
                                    <a href="?delete=<?php echo $row['id']; ?>" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        Hapus
                                    </a>
                                    <button onclick="openModal(<?php echo $row['id']; ?>)" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        Lihat Biodata
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile;
                    else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-800">Tidak ada data siswa.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal untuk menampilkan biodata siswa -->
    <div id="biodataModal" class="modal">
        <div class="modal-content bg-white rounded-xl shadow-2xl p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold text-gray-900">Biodata Siswa</h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="biodataContent" class="space-y-6"></div>
        </div>
    </div>

    <script>
        // Only keep modal related JavaScript
        function openModal(id) {
            fetch(`get_siswa.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    const biodataContent = `
                        <div class="bg-gray-50 rounded-lg p-6">
                            <div class="flex flex-col md:flex-row items-center gap-8">
                                <div class="relative">
                                    <img src="uploads/${data.foto}" alt="Foto ${data.nama}" 
                                         class="w-48 h-48 rounded-lg object-cover shadow-lg border-4 border-indigo-600">
                                    <div class="absolute -bottom-3 -right-3 bg-indigo-600 text-white px-4 py-1 rounded-full text-sm font-semibold">
                                        ID: ${data.id}
                                    </div>
                                </div>
                                <div class="flex-1 space-y-4">
                                    <div class="space-y-2">
                                        <h3 class="text-gray-500 text-sm font-medium">Nama Lengkap</h3>
                                        <p class="text-2xl font-bold text-gray-900">${data.nama}</p>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <h3 class="text-gray-500 text-sm font-medium">Kelas</h3>
                                            <p class="text-xl font-semibold text-gray-900">${data.kelas}</p>
                                        </div>
                                        <div class="space-y-2">
                                            <h3 class="text-gray-500 text-sm font-medium">Jurusan</h3>
                                            <p class="text-xl font-semibold text-gray-900">${data.jurusan}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    document.getElementById('biodataContent').innerHTML = biodataContent;
                    document.getElementById('biodataModal').style.display = 'block';
                });
        }

        function closeModal() {
            document.getElementById('biodataModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('biodataModal');
            if (event.target === modal) {
                closeModal();
            }
        }

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