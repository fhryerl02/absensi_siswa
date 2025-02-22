<?php
session_start();
require_once 'notification.php';

// Periksa apakah pengguna adalah admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "absensi_sekolah");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Handle form submission untuk absensi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_absen'])) {
    $siswa_id = $_POST['siswa_id'];
    $mata_pelajaran = $_POST['mata_pelajaran'];
    $tanggal = $_POST['tanggal'];
    $status = $_POST['status'];
    $keterangan = $_POST['keterangan'];

    $stmt = $conn->prepare("INSERT INTO absen (siswa_id, mata_pelajaran, tanggal, status, keterangan) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $siswa_id, $mata_pelajaran, $tanggal, $status, $keterangan);

    if ($stmt->execute()) {
        $_SESSION['notification'] = "Absensi berhasil ditambahkan!";
        $_SESSION['notification_type'] = "success";
    } else {
        $_SESSION['notification'] = "Gagal menambahkan absensi!";
        $_SESSION['notification_type'] = "error";
    }

    header("Location: absen.php");
    exit();
}

// Handle delete absensi
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    $stmt = $conn->prepare("DELETE FROM absen WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['notification'] = "Absensi berhasil dihapus!";
        $_SESSION['notification_type'] = "success";
    } else {
        $_SESSION['notification'] = "Gagal menghapus absensi!";
        $_SESSION['notification_type'] = "error";
    }

    header("Location: absen.php");
    exit();
}

// Ambil data untuk tabel
$query = "SELECT a.*, s.nama as nama_siswa 
          FROM absen a 
          JOIN data_siswa s ON a.siswa_id = s.id 
          ORDER BY a.tanggal DESC";
$result = $conn->query($query);

// Ambil data siswa untuk dropdown
$siswa_query = "SELECT id, nama FROM data_siswa ORDER BY nama";
$siswa_result = $conn->query($siswa_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include 'sidebar.php'; ?>

    <div class="ml-64 p-6">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-bold mb-4">Input Absensi</h2>
            
            <form method="POST" action="" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Siswa</label>
                        <select name="siswa_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Pilih Siswa</option>
                            <?php while ($siswa = $siswa_result->fetch_assoc()): ?>
                                <option value="<?php echo $siswa['id']; ?>">
                                    <?php echo $siswa['nama']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mata Pelajaran</label>
                        <select name="mata_pelajaran" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Pilih Mata Pelajaran</option>
                            <option value="Matematika">Matematika</option>
                            <option value="Bahasa Indonesia">Bahasa Indonesia</option>
                            <option value="Bahasa Inggris">Bahasa Inggris</option>
                            <option value="IPA">IPA</option>
                            <option value="IPS">IPS</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal</label>
                        <input type="date" name="tanggal" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Pilih Status</option>
                            <option value="Hadir">Hadir</option>
                            <option value="Tidak Hadir">Tidak Hadir</option>
                            <option value="Izin">Izin</option>
                            <option value="Sakit">Sakit</option>
                        </select>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Keterangan</label>
                        <textarea name="keterangan" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" name="submit_absen" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                        Simpan Absensi
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold mb-4">Data Absensi</h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Siswa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mata Pelajaran</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo $row['nama_siswa']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo $row['mata_pelajaran']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo $row['tanggal']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo $row['status']; ?></td>
                                    <td class="px-6 py-4"><?php echo $row['keterangan']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="?delete=<?php echo $row['id']; ?>" 
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')"
                                           class="text-red-600 hover:text-red-900">Hapus</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center">Tidak ada data absensi</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
