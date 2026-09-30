<?php
// 1. Start session at the top
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Handle the AJAX POST request to establish the PHP session
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    if (isset($data['firebase_uid'])) {
        $_SESSION['firebase_uid'] = $data['firebase_uid'];
        echo json_encode(['success' => true]);
        exit();
    }
}

// 3. Determine login status
$isUserLoggedIn = isset($_SESSION['user_id']) || isset($_SESSION['firebase_uid']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HireFlow - Find Your Dream Job Effortlessly</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        
        /* Modern Edge-to-Edge Glass Header */
        .glass-header {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.03);
        }

        /* Mobile Menu Animation */
        #mobile-menu {
            transition: all 0.3s ease-in-out;
            transform-origin: top;
        }
        #mobile-menu.hidden {
            display: none;
        }
        
        /* Modal Animation */
        .modal-enter {
            animation: modalFadeIn 0.3s ease-out forwards;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body class="text-[#0f172a] overflow-x-hidden relative">

    <!-- Edge-to-Edge Glass Navigation Bar -->
    <nav class="fixed top-0 left-0 w-full z-50 glass-header transition-all duration-300">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between px-4 sm:px-6 py-3 sm:py-4">
            
            <!-- Logo & Mobile Toggle -->
            <div class="w-full md:w-auto flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="text-xl md:text-2xl font-bold tracking-tight text-[#0f172a] cursor-pointer" onclick="window.location.href='index.php'">
                        HireFlow.
                    </div>
                </div>
                <button id="menu-btn" class="md:hidden p-2 text-[#475569] hover:text-[#0f172a] focus:outline-none transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center gap-1">
                <a href="findjobs.php" class="protected-link px-4 py-2 text-sm font-medium text-[#475569] hover:text-[#0f172a] transition-colors">Find Jobs</a>
                <a href="resume.php" class="protected-link px-4 py-2 text-sm font-medium text-[#475569] hover:text-[#0f172a] transition-colors">Build Resume</a>
                <a href="dashboard.php" class="protected-link px-4 py-2 text-sm font-medium text-[#475569] hover:text-[#0f172a] transition-colors">Dashboard</a>
                
                <div class="h-4 w-px bg-slate-300 mx-2"></div>
                
                <?php if (!$isUserLoggedIn): ?>
                <div id="desktop-auth-links" class="flex items-center gap-2 ml-1">
                    <a href="login.php" class="px-4 py-2 text-sm font-medium text-[#0f172a] hover:text-[#1d4ed8] transition-colors">Log in</a>
                    <a href="signup.php" class="px-5 py-2 text-sm font-semibold bg-[#0f172a] text-white rounded-full hover:bg-black transition-all active:scale-95 shadow-sm">Sign up</a>
                </div>
                <?php else: ?>
                <div id="desktop-user-menu" class="flex items-center gap-2 ml-1">
                    <a href="profile.php" class="px-4 py-2 text-sm font-medium text-[#475569] hover:text-[#0f172a] transition-colors">Profile</a>
                    <a href="logout.php" id="logout-btn-desktop" class="px-5 py-2 text-sm font-semibold bg-red-50 text-red-600 rounded-full hover:bg-red-100 transition-all active:scale-95">Log out</a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden w-full md:hidden flex-col gap-3 pt-4 pb-2 border-t border-slate-200/50 mt-3">
                <a href="findjobs.php" class="protected-link px-2 py-2 text-sm font-medium text-[#475569] hover:text-[#0f172a] block transition-colors">Find Jobs</a>
                <a href="resume.php" class="protected-link px-2 py-2 text-sm font-medium text-[#475569] hover:text-[#0f172a] block transition-colors">Build Resume</a>
                <a href="dashboard.php" class="protected-link px-2 py-2 text-sm font-medium text-[#475569] hover:text-[#0f172a] block transition-colors">Dashboard</a>
                
                <div class="w-full h-px bg-slate-200/50 my-1"></div>
                
                <?php if (!$isUserLoggedIn): ?>
                <div id="mobile-auth-links" class="flex flex-col gap-3">
                    <a href="login.php" class="px-2 py-2 text-sm font-medium text-[#0f172a] hover:text-[#1d4ed8] block transition-colors">Log in</a>
                    <a href="signup.php" class="w-full text-center px-5 py-3 text-sm font-semibold bg-[#0f172a] text-white rounded-xl hover:bg-black transition-all shadow-sm">Sign up</a>
                </div>
                <?php else: ?>
                <div id="mobile-user-menu" class="flex flex-col gap-3">
                    <a href="profile.php" class="px-2 py-2 text-sm font-medium text-[#475569] hover:text-[#0f172a] block transition-colors">Profile</a>
                    <a href="logout.php" id="logout-btn-mobile" class="w-full text-center px-5 py-3 text-sm font-semibold bg-red-50 text-red-600 rounded-xl hover:bg-red-100 transition-all">Log out</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>