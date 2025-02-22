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

// Handle form submission untuk tambah/edit guru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $nip = $_POST['nip'];
    $mata_pelajaran = $_POST['mata_pelajaran'];
    $alamat = $_POST['alamat'];
    $telepon = $_POST['telepon'];
    $email = $_POST['email'];

    if (isset($_POST['edit_id'])) {
        // Update existing data
        $id = $_POST['edit_id'];
        $stmt = $conn->prepare("UPDATE data_guru SET nama=?, nip=?, mata_pelajaran=?, alamat=?, telepon=?, email=? WHERE id=?");
        $stmt->bind_param("ssssssi", $nama, $nip, $mata_pelajaran, $alamat, $telepon, $email, $id);
        $message = "Data guru berhasil diperbarui!";
    } else {
        // Insert new data
        $stmt = $conn->prepare("INSERT INTO data_guru (nama, nip, mata_pelajaran, alamat, telepon, email) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $nama, $nip, $mata_pelajaran, $alamat, $telepon, $email);
        $message = "Data guru berhasil ditambahkan!";
    }

    if ($stmt->execute()) {
        $_SESSION['notification'] = $message;
        $_SESSION['notification_type'] = "success";
    } else {
        $_SESSION['notification'] = "Gagal menyimpan data: " . $conn->error;
        $_SESSION['notification_type'] = "error";
    }

    header("Location: guru.php");
    exit();
}

// Handle delete guru
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    $stmt = $conn->prepare("DELETE FROM data_guru WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['notification'] = "Data guru berhasil dihapus!";
        $_SESSION['notification_type'] = "success";
    } else {
        $_SESSION['notification'] = "Gagal menghapus data!";
        $_SESSION['notification_type'] = "error";
    }

    header("Location: guru.php");
    exit();
}

// Get data for edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM data_guru WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_data = $result->fetch_assoc();
}

// Fetch all teachers data
$query = "SELECT * FROM data_guru ORDER BY nama";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Data Guru</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include 'sidebar.php'; ?>

    <div class="ml-64 p-6">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-2"><?php echo $edit_data ? 'Edit Data Guru' : 'Tambah Data Guru'; ?></h2>
            
            <form method="POST" action="" class="space-y-6">
                <?php if ($edit_data) : ?>
                    <input type="hidden" name="edit_id" value="<?php echo $edit_data['id']; ?>">
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700">Nama Lengkap</label>
                        <input type="text" name="nama" required 
                               value="<?php echo $edit_data ? $edit_data['nama'] : ''; ?>"
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition duration-200 ease-in-out">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700">NIP</label>
                        <input type="text" name="nip" required 
                               value="<?php echo $edit_data ? $edit_data['nip'] : ''; ?>"
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition duration-200 ease-in-out">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700">Mata Pelajaran</label>
                        <input type="text" name="mata_pelajaran" required 
                               value="<?php echo $edit_data ? $edit_data['mata_pelajaran'] : ''; ?>"
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition duration-200 ease-in-out">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700">Telepon</label>
                        <input type="tel" name="telepon" 
                               value="<?php echo $edit_data ? $edit_data['telepon'] : ''; ?>"
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition duration-200 ease-in-out">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700">Email</label>
                        <input type="email" name="email" 
                               value="<?php echo $edit_data ? $edit_data['email'] : ''; ?>"
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition duration-200 ease-in-out">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700">Alamat</label>
                    <textarea name="alamat" rows="3" 
                              class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition duration-200 ease-in-out"><?php echo $edit_data ? $edit_data['alamat'] : ''; ?></textarea>
                </div>

                <div class="flex justify-end space-x-4 pt-4">
                    <?php if ($edit_data): ?>
                        <a href="guru.php" 
                           class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition duration-200 ease-in-out">
                            Batal
                        </a>
                    <?php endif; ?>
                    <button type="submit" 
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-200 ease-in-out">
                        <?php echo $edit_data ? 'Update Data' : 'Simpan Data'; ?>
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold mb-4">Data Guru</h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIP</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mata Pelajaran</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telepon</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alamat</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo $row['nama']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo $row['nip']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo $row['mata_pelajaran']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo $row['telepon']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo $row['email']; ?></td>
                                    <td class="px-6 py-4"><?php echo $row['alamat']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap space-x-2">
                                        <a href="?edit=<?php echo $row['id']; ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                        <a href="?delete=<?php echo $row['id']; ?>" 
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')"
                                           class="text-red-600 hover:text-red-900">Hapus</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center">Tidak ada data guru</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
