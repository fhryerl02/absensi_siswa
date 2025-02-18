<?php
session_start();

// Fungsi untuk memeriksa login admin
function isAdmin($username, $password) {
    $admin = [
        'username' => 'admin',
        'password' => password_hash('admin123', PASSWORD_BCRYPT)
    ];

    if ($username === $admin['username'] && password_verify($password, $admin['password'])) {
        return true;
    }
    return false;
}

// Fungsi untuk memeriksa login siswa
function isSiswa($username, $password) {
    // Koneksi ke database
    $conn = new mysqli("localhost", "root", "", "absensi_sekolah");
    
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }
    
    $stmt = $conn->prepare("SELECT * FROM data_siswa WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $siswa = $result->fetch_assoc();
        // Periksa apakah password di-hash atau plain text
        if (password_verify($password, $siswa['password']) || $siswa['password'] === $password) {
            $_SESSION['siswa_id'] = $siswa['id'];
            $_SESSION['siswa_nama'] = $siswa['nama'];
            return true;
        }
    }
    $stmt->close();
    $conn->close();
    return false;
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    if ($role === 'admin' && isAdmin($username, $password)) {
        $_SESSION['role'] = 'admin';
        header("Location: dashboard.php");
        exit();
    } elseif ($role === 'siswa' && isSiswa($username, $password)) {
        $_SESSION['role'] = 'siswa';
        header("Location: siswa_absensi.php"); // Ubah redirect ke siswa_absensi.php
        exit();
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h1 class="text-2xl font-bold text-center mb-6">Login</h1>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-4">
            <div class="mb-4">
                <label for="role" class="block text-sm font-medium text-gray-700">Login Sebagai</label>
                <select name="role" id="role" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    <option value="admin">Admin</option>
                    <option value="siswa">Siswa</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" name="username" id="username" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
            </div>

            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
            </div>

            <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Login
            </button>

            <div class="mt-4 text-center">
                <a href="dashboard.php?role=tamu" class="text-indigo-600 hover:text-indigo-700">Login as Guest</a>
            </div>
        </form>
    </div>
</body>
</html>