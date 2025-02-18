<?php
session_start();

// Periksa apakah pengguna adalah tamu atau admin
if (!isset($_SESSION['role']) && !isset($_GET['role'])) {
    header("Location: login.php");
    exit();
}

$role = isset($_SESSION['role']) ? $_SESSION['role'] : $_GET['role'];

if ($role !== 'tamu' && $role !== 'admin') {
    header("Location: login.php");
    exit();
}

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "absensi_sekolah");

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Absensi Siswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <!-- Tombol Kembali ke Dashboard -->
        <a href="dashboard.php?role=<?php echo $role; ?>" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Dashboard
        </a>

        <h1 class="text-3xl font-bold mb-6">Data Absensi Siswa</h1>

        <!-- Tabel absensi -->
        <div class="bg-white p-6 rounded-lg shadow-md overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Siswa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php
                    $query = "
                        SELECT absen_siswa.id, data_siswa.nama, data_siswa.foto, absen_siswa.status, absen_siswa.tanggal 
                        FROM absen_siswa 
                        JOIN data_siswa ON absen_siswa.siswa_id = data_siswa.id
                    ";
                    $result = $conn->query($query);

                    if ($result->num_rows > 0):
                        $no = 1; // Nomor urut
                        while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $no++; ?></td>
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
                            <td colspan="5" class="px-6 py-4 text-center">Tidak ada data absensi.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>