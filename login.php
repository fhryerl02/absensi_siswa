<?php
session_start();

// Fungsi untuk memeriksa login admin
function isAdmin($username, $password) {
    $admin = [
        'username' => 'admin',
        'password' => '$2y$10$kUkIU7WH0QtZwg9GFFLhZuqjWmK1yG2du/aDSkEa5Y5VGw1GvjKmi' // password_hash('admin123', PASSWORD_BCRYPT)
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
    
    $stmt = $conn->prepare("SELECT id, nama, password FROM data_siswa WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $siswa = $result->fetch_assoc();
        if (password_verify($password, $siswa['password'])) {
            $_SESSION['siswa_id'] = $siswa['id'];
            $_SESSION['siswa_nama'] = $siswa['nama'];
            $stmt->close();
            $conn->close();
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
    <title>Login - Absensi Sekolah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom animasi untuk container login */
        .animate-fadeInDown {
            animation: fadeInDown 1s;
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        /* Animasi khusus untuk dropdown */
        .animate-dropdown {
            transition: transform 0.3s ease-in-out;
        }
        .animate-dropdown:focus {
            transform: scale(1.05);
        }
        /* Add floating animation for welcome text */
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .fade-out {
            animation: fadeOut 0.5s forwards;
        }
        .fade-in {
            animation: fadeIn 0.5s forwards;
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body class="bg-gray-100 flex flex-col items-center justify-center min-h-screen p-4" style="background: linear-gradient(135deg, #6366F1, #A78BFA, #E0D4FF);">
    <!-- Welcome Text -->
    <div class="text-center mb-8 animate-float">
        <h1 class="text-4xl md:text-5xl font-bold text-white mb-2">
            Selamat Datang
        </h1>
        <p class="text-xl md:text-2xl text-gray-200">
            di Sistem Informasi Absensi Sekolah
        </p>
    </div>

    <!-- Login Box -->
    <div class="bg-white p-8 rounded-lg shadow-md max-w-md w-full animate-fadeInDown">
        <h2 class="text-xl text-center text-gray-800 font-bold mb-2">Selamat datang di Absensi Sekolah</h2>
        <div id="admin-section" class="hidden">
            <p class="text-gray-600">Admin Login Section</p>
            <!-- Additional admin-specific fields can go here -->
        </div>
        <div id="siswa-section" class="hidden">
            <p class="text-gray-600">Siswa Login Section</p>
            <!-- Additional siswa-specific fields can go here -->
        </div>

        <h1 class="text-2xl font-bold text-center mb-6">Login</h1>
        <div class="flex justify-center space-x-4 mb-4">
            <button type="button" onclick="toggleRole('admin')" class="bg-blue-500 text-white px-4 py-2 rounded">Login as Admin</button>
            <button type="button" onclick="toggleRole('siswa')" class="bg-green-500 text-white px-4 py-2 rounded">Login as Siswa</button>
        </div>


        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-4">
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700">Login Sebagai</label>
                <select name="role" id="role" class="animate-dropdown mt-1 block w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required onchange="toggleSections()">

                    <option value="admin">Admin</option>
                    <option value="siswa">Siswa</option>
                </select>
            </div>

            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" name="username" id="username" class="mt-1 block w-full p-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="password" class="mt-1 block w-full p-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>

            <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" id="login-button">

                Login
            </button>

            <div class="mt-3 text-center">
                <a href="dashboard.php?role=tamu" class="text-indigo-600 hover:text-indigo-700">Login as Guest</a>
            </div>
        </form>
    </div>
    <script>
        function toggleSections() {
            const role = document.getElementById('role').value;
            const adminSection = document.getElementById('admin-section');
            const siswaSection = document.getElementById('siswa-section');

            if (role === 'admin') {
                siswaSection.classList.add('fade-out');
                adminSection.classList.remove('hidden');
                adminSection.classList.add('fade-in');
                siswaSection.classList.add('hidden');
            } else {
                adminSection.classList.add('fade-out');
                siswaSection.classList.remove('hidden');
                siswaSection.classList.add('fade-in');
                adminSection.classList.add('hidden');
            }
        }
        
        function toggleRole(role) {
            document.getElementById('role').value = role;
            toggleSections();
        }
    </script>
</body>

</html>
