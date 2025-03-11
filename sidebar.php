<?php
if (!isset($role)) {
    $role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
}
?>

<style>
    .sidebar {
        transition: width 0.3s ease;
    }
    
    .sidebar.collapsed {
        width: 5rem;
    }
    
    .sidebar.collapsed .menu-text {
        display: none;
    }
    
    .sidebar.collapsed .sidebar-header span {
        display: none;
    }

    .main-content {
        transition: margin-left 0.3s ease;
    }

    .main-content.expanded {
        margin-left: 5rem;
    }

    .menu-item:hover .tooltip {
        opacity: 1;
        visibility: visible;
    }

    .tooltip {
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .sidebar-logo {
        width: 40px;
        height: 40px;
        transition: all 0.3s ease;
    }

    .sidebar.collapsed .sidebar-logo {
        width: 30px;
        height: 30px;
    }

    .close-btn {
        position: absolute;
        top: 1rem;
        right: -12px;
        background: #4F46E5;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        z-index: 60;
    }

    .close-btn:hover {
        background: #4338CA;
        transform: scale(1.1);
    }

    .sidebar.collapsed .close-btn {
        transform: rotate(180deg);
    }

    .sidebar.collapsed .close-btn:hover {
        transform: rotate(180deg) scale(1.1);
    }
</style>

<aside id="sidebar" class="sidebar w-64 bg-indigo-700 text-white min-h-screen fixed top-0 left-0 z-50">
    <!-- Add close button -->
    <div class="close-btn" id="sidebarClose">
        <i class="fas fa-chevron-left text-white text-sm"></i>
    </div>

    <div class="p-6">
        <!-- Updated Sidebar Header with Logo -->
        <div class="sidebar-header flex items-center justify-between mb-6">
            <div class="flex items-center space-x-3">
                <!-- Logo -->
                <div class="sidebar-logo">
                    <svg class="w-full h-full" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-2xl font-bold flex items-center">
                        <span>SIAB</span>
                    </h1>
                    <span class="text-xs text-indigo-200"><?php echo ucfirst($role); ?></span>
                </div>
            </div>
        </div>

        <nav>
            <ul class="space-y-4">
                <!-- Dashboard -->
                <li class="menu-item relative">
                    <a href="dashboard.php" 
                       class="flex items-center px-4 py-3 bg-indigo-600 rounded-lg hover:bg-indigo-500 
                              transition-all duration-200 group">
                        <i class="fas fa-home text-xl mr-3 group-hover:scale-110 transition-transform"></i>
                        <span class="menu-text">Dashboard</span>
                    </a>
                    <div class="tooltip absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm 
                                rounded-md whitespace-nowrap opacity-0">
                        Dashboard
                    </div>
                </li>

                <?php if ($role === 'admin'): ?>
                <!-- Data Siswa -->
                <li class="menu-item relative">
                    <a href="siswa.php" 
                       class="flex items-center px-4 py-3 bg-indigo-600 rounded-lg hover:bg-indigo-500 
                              transition-all duration-200 group">
                        <i class="fas fa-user-graduate text-xl mr-3 group-hover:scale-110 transition-transform"></i>
                        <span class="menu-text">Data Siswa</span>
                    </a>
                    <div class="tooltip absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm 
                                rounded-md whitespace-nowrap opacity-0">
                        Data Siswa
                    </div>
                </li>

                <!-- Absensi Siswa -->
                <li class="menu-item relative">
                    <a href="absensi.php" 
                       class="flex items-center px-4 py-3 bg-indigo-600 rounded-lg hover:bg-indigo-500 
                              transition-all duration-200 group">
                        <i class="fas fa-clipboard-check text-xl mr-3 group-hover:scale-110 transition-transform"></i>
                        <span class="menu-text">Absensi Siswa</span>
                    </a>
                    <div class="tooltip absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm 
                                rounded-md whitespace-nowrap opacity-0">
                        Absensi Siswa
                    </div>
                </li>

                <!-- Jadwal Pelajaran -->
                <li class="menu-item relative">
                    <a href="jadwal_pelajaran.php" 
                       class="flex items-center px-4 py-3 bg-indigo-600 rounded-lg hover:bg-indigo-500 
                              transition-all duration-200 group">
                        <i class="fas fa-calendar-alt text-xl mr-3 group-hover:scale-110 transition-transform"></i>
                        <span class="menu-text">Jadwal Pelajaran</span>
                    </a>
                    <div class="tooltip absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm 
                                rounded-md whitespace-nowrap opacity-0">
                        Jadwal Pelajaran
                    </div>
                </li>
                <?php endif; ?>

                <!-- Logout -->
                <li class="menu-item relative mt-auto">
                    <a href="logout.php" 
                       class="flex items-center px-4 py-3 bg-red-600 rounded-lg hover:bg-red-500 
                              transition-all duration-200 group">
                        <i class="fas fa-sign-out-alt text-xl mr-3 group-hover:scale-110 transition-transform"></i>
                        <span class="menu-text">Logout</span>
                    </a>
                    <div class="tooltip absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm 
                                rounded-md whitespace-nowrap opacity-0">
                        Logout
                    </div>
                </li>
            </ul>
        </nav>
    </div>
</aside>

<!-- Add this script at the bottom of your sidebar.php -->
<script>
    const sidebar = document.getElementById('sidebar');
    const sidebarClose = document.getElementById('sidebarClose');
    const mainContent = document.querySelector('.main-content');
    let isSidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';

    function toggleSidebar() {
        isSidebarCollapsed = !isSidebarCollapsed;
        sidebar.classList.toggle('collapsed', isSidebarCollapsed);
        mainContent.classList.toggle('expanded', isSidebarCollapsed);
        localStorage.setItem('sidebarCollapsed', isSidebarCollapsed);
    }

    // Initialize sidebar state
    if (isSidebarCollapsed) {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('expanded');
    }

    // Use the close button instead of sidebarToggle
    sidebarClose.addEventListener('click', toggleSidebar);

    // Add sticky behavior
    window.addEventListener('scroll', () => {
        if (window.scrollY > 0) {
            sidebar.classList.add('shadow-xl');
        } else {
            sidebar.classList.remove('shadow-xl');
        }
    });
</script>
