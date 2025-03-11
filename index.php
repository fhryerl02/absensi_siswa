<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Absensi Sekolah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #6366F1, #A78BFA, #E0D4FF);
        }
        .scale-hover {
            transition: transform 0.3s ease;
        }
        .scale-hover:hover {
            transform: scale(1.05);
        }
        .glow {
            animation: glow 2s infinite alternate;
        }
        @keyframes glow {
            from {
                box-shadow: 0 0 10px -10px rgba(255, 255, 255, 0.5);
            }
            to {
                box-shadow: 0 0 20px 5px rgba(255, 255, 255, 0.3);
            }
        }
        .feature-card {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .feature-card:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        .float-animation {
            animation: float 3s ease-in-out infinite;
        }
    </style>
</head>
<body class="gradient-bg min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white/10 backdrop-blur-md fixed w-full z-50">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="text-white text-2xl font-bold flex items-center">
                    <i class="fas fa-school mr-2"></i>
                    <span>SIAB</span>
                </div>
                <div class="flex space-x-4">
                    <a href="login.php" 
                       class="bg-white text-indigo-600 px-8 py-3 rounded-full font-semibold 
                              hover:bg-indigo-100 transition-all duration-300 scale-hover glow
                              flex items-center space-x-2">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Masuk</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-32 pb-20 px-4">
        <div class="container mx-auto text-center">
            <h1 class="text-5xl md:text-6xl font-bold text-white mb-6" data-aos="fade-down">
                Sistem Informasi Absensi Sekolah
            </h1>
            <p class="text-xl text-white/80 mb-12 max-w-3xl mx-auto leading-relaxed" data-aos="fade-up" data-aos-delay="200">
                Kelola kehadiran siswa dengan mudah dan efisien menggunakan sistem absensi digital modern yang terintegrasi
            </p>
        </div>
    </section>

    <!-- Features -->
    <section class="py-20 bg-white/10 backdrop-blur-md">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-white text-center mb-16" data-aos="fade-up">
                Fitur Utama
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <!-- Feature 1 -->
                <div class="feature-card bg-white/20 backdrop-blur-md p-8 rounded-xl transition-all duration-300 
                            hover:bg-white/30 hover:transform hover:-translate-y-2 hover:shadow-xl 
                            cursor-pointer group" data-aos="fade-up" data-aos-delay="100">
                    <div class="text-white text-4xl mb-6 bg-white/20 w-16 h-16 rounded-full 
                                flex items-center justify-center mx-auto 
                                group-hover:bg-indigo-500 group-hover:scale-110 
                                transition-all duration-300">
                        <i class="fas fa-user-check group-hover:rotate-12 transition-transform duration-300"></i>
                    </div>
                    <h3 class="text-2xl font-semibold text-white mb-4 text-center 
                               group-hover:text-indigo-200 transition-colors duration-300">
                        Absensi Digital
                    </h3>
                    <p class="text-white/80 text-center leading-relaxed 
                              group-hover:text-white transition-colors duration-300">
                        Catat kehadiran siswa secara digital dengan mudah dan akurat menggunakan sistem modern
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="feature-card bg-white/20 backdrop-blur-md p-8 rounded-xl transition-all duration-300 
                            hover:bg-white/30 hover:transform hover:-translate-y-2 hover:shadow-xl 
                            cursor-pointer group" data-aos="fade-up" data-aos-delay="200">
                    <div class="text-white text-4xl mb-6 bg-white/20 w-16 h-16 rounded-full 
                                flex items-center justify-center mx-auto 
                                group-hover:bg-indigo-500 group-hover:scale-110 
                                transition-all duration-300">
                        <i class="fas fa-chart-bar group-hover:rotate-12 transition-transform duration-300"></i>
                    </div>
                    <h3 class="text-2xl font-semibold text-white mb-4 text-center 
                               group-hover:text-indigo-200 transition-colors duration-300">
                        Laporan Real-time
                    </h3>
                    <p class="text-white/80 text-center leading-relaxed 
                              group-hover:text-white transition-colors duration-300">
                        Pantau kehadiran siswa secara real-time dengan laporan yang detail dan terperinci
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="feature-card bg-white/20 backdrop-blur-md p-8 rounded-xl transition-all duration-300 
                            hover:bg-white/30 hover:transform hover:-translate-y-2 hover:shadow-xl 
                            cursor-pointer group" data-aos="fade-up" data-aos-delay="300">
                    <div class="text-white text-4xl mb-6 bg-white/20 w-16 h-16 rounded-full 
                                flex items-center justify-center mx-auto 
                                group-hover:bg-indigo-500 group-hover:scale-110 
                                transition-all duration-300">
                        <i class="fas fa-mobile-alt group-hover:rotate-12 transition-transform duration-300"></i>
                    </div>
                    <h3 class="text-2xl font-semibold text-white mb-4 text-center 
                               group-hover:text-indigo-200 transition-colors duration-300">
                        Akses Mudah
                    </h3>
                    <p class="text-white/80 text-center leading-relaxed 
                              group-hover:text-white transition-colors duration-300">
                        Akses sistem dari berbagai perangkat dengan tampilan yang responsif dan user-friendly
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits -->
    <section class="py-20">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-white text-center mb-12" data-aos="fade-up">Keunggulan Sistem</h2>
            <div class="flex justify-center">
                <div class="max-w-2xl" data-aos="fade-up" data-aos-delay="100">
                    <ul class="space-y-6">
                        <li class="flex items-center space-x-4 bg-white/10 backdrop-blur-md p-4 rounded-lg hover:bg-white/20 transition-all duration-300">
                            <i class="fas fa-check-circle text-white text-2xl"></i>
                            <span class="text-white text-lg">Memudahkan pengelolaan data kehadiran siswa</span>
                        </li>
                        <li class="flex items-center space-x-4 bg-white/10 backdrop-blur-md p-4 rounded-lg hover:bg-white/20 transition-all duration-300">
                            <i class="fas fa-check-circle text-white text-2xl"></i>
                            <span class="text-white text-lg">Menghemat waktu dalam proses pencatatan absensi</span>
                        </li>
                        <li class="flex items-center space-x-4 bg-white/10 backdrop-blur-md p-4 rounded-lg hover:bg-white/20 transition-all duration-300">
                            <i class="fas fa-check-circle text-white text-2xl"></i>
                            <span class="text-white text-lg">Mengurangi kesalahan dalam pencatatan manual</span>
                        </li>
                        <li class="flex items-center space-x-4 bg-white/10 backdrop-blur-md p-4 rounded-lg hover:bg-white/20 transition-all duration-300">
                            <i class="fas fa-check-circle text-white text-2xl"></i>
                            <span class="text-white text-lg">Monitoring kehadiran siswa secara real-time</span>
                        </li>
                        <li class="flex items-center space-x-4 bg-white/10 backdrop-blur-md p-4 rounded-lg hover:bg-white/20 transition-all duration-300">
                            <i class="fas fa-check-circle text-white text-2xl"></i>
                            <span class="text-white text-lg">Akses mudah melalui berbagai perangkat</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white/10 backdrop-blur-md py-8">
        <div class="container mx-auto px-4 text-center">
            <div class="text-white/80 mb-4">
                <p class="text-lg">Sistem Informasi Absensi Sekolah</p>
                <p class="text-sm">Membuat pengelolaan absensi lebih efisien</p>
            </div>
            <p class="text-white/60">&copy; <?php echo date('Y'); ?> SIAB. All rights reserved.</p>
        </div>
    </footer>

    <script>
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });
    </script>
</body>
</html>