<?php
session_start();

// Fungsi untuk memeriksa login admin
function isAdmin($username, $password) {
    $admin = [
        'username' => 'admin',
        'password' => password_hash('admin123', PASSWORD_DEFAULT)
    ];
    if ($username === $admin['username'] && password_verify($password, $admin['password'])) {
        return true;
    }
    return false;
}

// Fungsi untuk memeriksa login siswa
function isSiswa($username, $password) {
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']); 
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    if ($role === 'admin') {
        if ($username === 'admin' && $password === 'admin123') {
            $_SESSION['role'] = 'admin';
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Username atau password salah!";
        }
    } elseif ($role === 'siswa' && isSiswa($username, $password)) {
        $_SESSION['role'] = 'siswa';
        header("Location: siswa_absensi.php");
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        .role-option input[type="radio"]:checked + div {
            border-color: #4F46E5;
            background-color: #EEF2FF;
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
        <h1 class="text-2xl font-bold text-center mb-8">Login</h1>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-6">
            <!-- Role Selection -->
            <div class="flex justify-center space-x-4 mb-6">
                <label class="role-option">
                    <input type="radio" name="role" value="admin" class="hidden" checked>
                    <div class="px-6 py-3 bg-white border-2 border-gray-200 rounded-lg cursor-pointer transition-all duration-200 
                                hover:border-indigo-500 hover:shadow-md flex items-center space-x-2
                                peer-checked:border-indigo-500 peer-checked:bg-indigo-50">
                        <i class="fas fa-user-shield text-xl text-indigo-500"></i>
                        <span class="font-medium text-gray-700">Admin</span>
                    </div>
                </label>
                <label class="role-option">
                    <input type="radio" name="role" value="siswa" class="hidden">
                    <div class="px-6 py-3 bg-white border-2 border-gray-200 rounded-lg cursor-pointer transition-all duration-200 
                                hover:border-indigo-500 hover:shadow-md flex items-center space-x-2
                                peer-checked:border-indigo-500 peer-checked:bg-indigo-50">
                        <i class="fas fa-user-graduate text-xl text-indigo-500"></i>
                        <span class="font-medium text-gray-700">Siswa</span>
                    </div>
                </label>
            </div>

            <!-- Username Field -->
            <div class="space-y-2">
                <label for="username" class="block text-sm font-semibold text-gray-700">Username</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-user text-gray-400"></i>
                    </div>
                    <input type="text" name="username" id="username" 
                           class="block w-full pl-10 px-4 py-3 rounded-lg border border-gray-300
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                                  transition duration-150 ease-in-out" 
                           required>
                </div>
            </div>

            <!-- Password Field -->
            <div class="space-y-2">
                <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400"></i>
                    </div>
                    <input type="password" name="password" id="password" 
                           class="block w-full pl-10 px-4 py-3 rounded-lg border border-gray-300
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                                  transition duration-150 ease-in-out" 
                           required>
                    <button type="button" 
                            onclick="togglePassword()"
                            class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-600 hover:text-indigo-500 transition-colors">
                        <i class="fas fa-eye text-lg" id="togglePassword"></i>
                    </button>
                </div>
            </div>

            <!-- Login Button -->
            <button type="submit" 
                    class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg
                           focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500
                           transform transition-all duration-150 hover:scale-[1.02]
                           flex items-center justify-center space-x-2">
                <i class="fas fa-sign-in-alt"></i>
                <span>Login</span>
            </button>

            <!-- Guest Login and Back Button Container -->
            <div class="space-y-4">
                <!-- Guest Login Link -->
                <div class="text-center">
                    <a href="dashboard.php?role=tamu" 
                       class="inline-flex items-center space-x-2 text-indigo-600 hover:text-indigo-700 font-medium transition-colors">
                        <i class="fas fa-user-alt"></i>
                        <span>Login as Guest</span>
                    </a>
                </div>

                <!-- Back Button with improved visibility -->
                <div class="text-center mt-6">
                    <a href="index.php" 
                       class="inline-flex items-center px-6 py-3 
                              bg-gray-100 hover:bg-indigo-500
                              border-2 border-gray-200 hover:border-indigo-600
                              rounded-full text-gray-700 hover:text-white
                              font-medium transition-all duration-300
                              transform hover:scale-105 hover:shadow-lg group
                              shadow-md">
                        <i class="fas fa-arrow-left mr-2 transform group-hover:-translate-x-1 transition-transform duration-300"></i>
                        <span>Kembali ke Beranda</span>
                    </a>
                </div>
            </div>
        </form>
    </div>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('togglePassword');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>
