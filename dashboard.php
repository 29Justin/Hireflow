<?php
session_start();
require 'dbconfig.php';

// 1. Ensure Candidate is Logged In
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. Fetch Candidate Info
$stmt = $pdo->prepare("SELECT candidate_id, name FROM public.candidates WHERE user_id = ?");
$stmt->execute([$user_id]);
$candidate = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$candidate) {
    // If they bypass onboarding somehow, force them back
    header("Location: onboarding.php");
    exit;
}

$candidate_id = $candidate['candidate_id'];
$full_name = $candidate['name'];

// Split the full name into parts to extract first name and initials
$name_parts = explode(' ', trim($full_name));
$first_name = htmlspecialchars($name_parts[0]);

// Generate initials
$initials = strtoupper(substr($name_parts[0], 0, 1));
if (count($name_parts) > 1) {
    $initials .= strtoupper(substr(end($name_parts), 0, 1));
}

// 3. Fetch Dashboard Stats
$statsStmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_apps,
        ROUND(AVG(ats_score)) as avg_ats,
        SUM(CASE WHEN status = 'Interviewing' THEN 1 ELSE 0 END) as interview_count
    FROM public.applications
    WHERE candidate_id = ?
");
$statsStmt->execute([$candidate_id]);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

$total_apps = $stats['total_apps'] ?? 0;
$avg_ats = $stats['avg_ats'] ?? 0;
$interview_count = $stats['interview_count'] ?? 0;

// 4. Fetch Recent Applications
$appsStmt = $pdo->prepare("
    SELECT 
        a.status, 
        a.ats_score, 
        a.applied_at,
        j.role,
        c.name as company_name
    FROM public.applications a
    JOIN public.jobs j ON a.job_id = j.job_id
    JOIN public.companies c ON j.company_id = c.company_id
    WHERE a.candidate_id = ?
    ORDER BY a.applied_at DESC
");
$appsStmt->execute([$candidate_id]);
$applications = $appsStmt->fetchAll(PDO::FETCH_ASSOC);

// Helper function for Dark Mode Status Pills
function getStatusStyle($status) {
    switch ($status) {
        case 'Applied': return 'text-gray-300 bg-white/5 border-white/10';
        case 'Reviewing': return 'text-amber-400 bg-amber-400/10 border-amber-400/20';
        case 'Interviewing': return 'text-blue-400 bg-blue-400/10 border-blue-400/20';
        case 'Rejected': return 'text-red-400 bg-red-400/10 border-red-400/20';
        case 'Hired': return 'text-green-400 bg-green-400/10 border-green-400/20';
        case 'Draft': return 'text-gray-400 bg-white/5 border-white/10';
        default: return 'text-gray-300 bg-white/5 border-white/10';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - HireFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            -webkit-font-smoothing: antialiased; 
            -moz-osx-font-smoothing: grayscale;
            margin: 0;
            padding: 0;
        }

        /* Sleek Black, Grey, and White Gradient */
        .custom-bg {
            background: linear-gradient(to top right, #000000 10%, #171717 45%, #404040 75%, #a3a3a3 100%);
            position: relative;
            background-attachment: fixed;
        }

        /* Vertical light streaks glass texture overlay */
        .glass-streaks {
            position: fixed;
            inset: 0;
            z-index: 1;
            background: repeating-linear-gradient(
                to right,
                rgba(255, 255, 255, 0.01) 0px,
                rgba(255, 255, 255, 0.05) 15px,
                rgba(255, 255, 255, 0.01) 30px
            );
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            pointer-events: none;
        }

        /* Dark Mode Dashboard Cards */
        .ui-card {
            background: rgba(20, 20, 20, 0.5);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
            position: relative;
            z-index: 10;
        }

        /* Slightly more opaque for nested elements */
        .ui-card-inner {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        /* Sidebar styling */
        .sidebar-item { transition: all 0.2s ease; }
        .sidebar-item:hover { background: rgba(255, 255, 255, 0.05); color: #ffffff; }
        .active-sidebar { background: rgba(255, 255, 255, 0.1); color: #ffffff; font-weight: 600; border: 1px solid rgba(255,255,255,0.1); }

        /* Smooth fade-in */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-up { animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    </style>
</head>
<body class="custom-bg text-white min-h-screen flex overflow-x-hidden">

    <!-- Fixed Background Elements -->
    <div class="fixed top-[-5%] right-[10%] w-[50vw] h-[50vw] rounded-full bg-white/10 blur-[140px] pointer-events-none z-0"></div>
    <div class="fixed bottom-[0%] left-[-10%] w-[60vw] h-[60vw] rounded-full bg-gray-500/20 blur-[130px] pointer-events-none z-0"></div>
    <div class="glass-streaks"></div>

    <!-- Sidebar -->
    <aside class="w-64 ui-card border-t-0 border-b-0 border-l-0 border-r border-white/10 hidden md:flex flex-col p-8 fixed h-full z-40 rounded-none">
        <div class="text-2xl font-extrabold tracking-tighter mb-12 cursor-pointer text-white drop-shadow-md" onclick="window.location.href='index.php'">HireFlow.</div>
        
        <nav class="space-y-3 flex-grow">
            <a href="candidate_dashboard.php" class="sidebar-item active-sidebar flex items-center gap-3 px-5 py-3.5 rounded-2xl">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Dashboard
            </a>
            <a href="findjobs.php" class="sidebar-item flex items-center gap-3 px-5 py-3.5 rounded-2xl text-gray-400 font-medium">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                Find Jobs
            </a>
            <a href="profile.php" class="sidebar-item flex items-center gap-3 px-5 py-3.5 rounded-2xl text-gray-400 font-medium">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                Profile
            </a>
        </nav>
        
        <a href="logout.php" class="mt-auto w-full py-3.5 ui-card-inner rounded-xl text-sm font-bold text-gray-400 hover:bg-red-500/20 hover:text-red-400 hover:border-red-500/30 transition-colors text-center block">
            Log Out
        </a>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="relative z-10 flex-grow md:ml-64 flex flex-col min-h-screen">
        <main class="flex-grow p-6 md:p-12 animate-fade-up">
            
            <!-- Header -->
            <header class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12 mt-4 md:mt-0">
                <div>
                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-2 text-white">Welcome back, <?= $first_name ?>.</h1>
                    <p class="text-gray-400 font-medium text-lg">
                        <?php if ($interview_count > 0): ?>
                            You have <span class="font-bold text-white"><?= $interview_count ?></span> interview(s) scheduled for this week.
                        <?php else: ?>
                            Keep applying! The right opportunity is out there.
                        <?php endif; ?>
                    </p>
                </div>
                <div class="flex items-center">
                    <div class="w-14 h-14 bg-white rounded-full flex items-center justify-center text-black text-lg font-bold shadow-lg shadow-white/10">
                        <?= $initials ?>
                    </div>
                </div>
            </header>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                <div class="ui-card p-8 rounded-[32px]">
                    <p class="text-gray-400 text-xs font-bold tracking-widest uppercase mb-3">Active Applications</p>
                    <p class="text-5xl font-extrabold text-white"><?= $total_apps ?></p>
                </div>
                <div class="ui-card p-8 rounded-[32px]">
                    <p class="text-gray-400 text-xs font-bold tracking-widest uppercase mb-3">Average ATS Score</p>
                    <p class="text-5xl font-extrabold <?= $avg_ats >= 75 ? 'text-green-400' : ($avg_ats >= 50 ? 'text-amber-400' : 'text-white') ?> drop-shadow-md">
                        <?= $avg_ats > 0 ? $avg_ats . '%' : '--' ?>
                    </p>
                </div>
                <div class="ui-card p-8 rounded-[32px]">
                    <p class="text-gray-400 text-xs font-bold tracking-widest uppercase mb-3">Upcoming Interviews</p>
                    <p class="text-5xl font-extrabold text-white"><?= str_pad($interview_count, 2, '0', STR_PAD_LEFT) ?></p>
                </div>
            </div>

            <!-- Recent Applications Table (Now Full Width) -->
            <div class="w-full ui-card rounded-[32px] overflow-hidden flex flex-col h-full">
                <div class="p-8 border-b border-white/10 flex justify-between items-center bg-white/5">
                    <h2 class="text-2xl font-bold tracking-tight text-white">Recent Applications</h2>
                    <a href="findjobs.php" class="text-sm font-bold text-gray-300 hover:text-white transition-colors">Find More Jobs →</a>
                </div>
                
                <div class="overflow-x-auto flex-grow">
                    <?php if (count($applications) > 0): ?>
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-gray-400 text-[11px] uppercase tracking-widest font-bold bg-black/20">
                                    <th class="px-8 py-5">Role & Company</th>
                                    <th class="px-8 py-5">Date Applied</th>
                                    <th class="px-8 py-5">Status</th>
                                    <th class="px-8 py-5 text-right">ATS Match</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <?php foreach($applications as $app): ?>
                                <tr class="hover:bg-white/5 transition-colors group cursor-default">
                                    <td class="px-8 py-6">
                                        <p class="font-bold text-white group-hover:text-gray-200 transition-colors text-base"><?= htmlspecialchars($app['role']) ?></p>
                                        <p class="text-sm text-gray-400 font-medium mt-0.5"><?= htmlspecialchars($app['company_name']) ?></p>
                                    </td>
                                    <td class="px-8 py-6 text-gray-400 text-sm font-medium">
                                        <?= date('M d, Y', strtotime($app['applied_at'])) ?>
                                    </td>
                                    <td class="px-8 py-6">
                                        <span class="px-4 py-1.5 rounded-full text-xs font-bold border <?= getStatusStyle($app['status']) ?>">
                                            <?= htmlspecialchars($app['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 text-right font-extrabold text-lg <?= $app['ats_score'] >= 75 ? 'text-green-400' : ($app['ats_score'] >= 50 ? 'text-amber-400' : 'text-gray-300') ?>">
                                        <?= $app['ats_score'] !== null ? $app['ats_score'] . '%' : '--' ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="flex flex-col items-center justify-center p-16 text-center h-full">
                            <div class="w-16 h-16 ui-card-inner text-gray-400 rounded-full flex items-center justify-center mb-6">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2">No applications yet</h3>
                            <p class="text-base text-gray-400 mb-8 font-medium">You haven't applied to any jobs. Start your journey today!</p>
                            <a href="findjobs.php" class="px-8 py-3.5 bg-white text-black rounded-full font-bold shadow-lg hover:bg-gray-200 transition-all active:scale-95">Browse Jobs</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>
</body>
</html>