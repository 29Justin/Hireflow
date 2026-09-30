<?php
session_start();
require 'dbconfig.php'; // Ensure your secure DB connection is included

// Ensure the candidate is logged in using the NEW user_id
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle Search Filter securely
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

// Base query matching your exact Supabase schema
$sql = "
    SELECT 
        j.job_id, 
        j.role, 
        j.job_description, 
        j.city, 
        j.country, 
        j.job_type, 
        j.is_remote, 
        j.created_at,
        c.name AS company_name, 
        c.logo_url 
    FROM public.jobs j
    JOIN public.companies c ON j.company_id = c.company_id
    WHERE 1=1
";

$params = [];

// Add search filtering if a term is provided
if (!empty($searchTerm)) {
    $sql .= " AND (j.role ILIKE ? OR c.name ILIKE ?)";
    $params[] = "%$searchTerm%";
    $params[] = "%$searchTerm%";
}

// Order by newest first
$sql .= " ORDER BY j.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include page head / navbar
include 'header.php';
?>

<!-- Import Inter Font and Define Modern Dark Glass UI Aesthetics -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    .font-inter {
        font-family: 'Inter', sans-serif;
    }
    
    /* Sleek Black, Grey, and White Gradient matching index.php */
    .custom-bg {
        background: linear-gradient(to top right, #000000 10%, #171717 45%, #404040 75%, #a3a3a3 100%);
        position: relative;
    }

    /* Vertical light streaks glass texture overlay */
    .glass-streaks {
        position: absolute;
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
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .ui-card:hover {
        border-color: rgba(255, 255, 255, 0.3);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
        transform: translateY(-3px);
    }

    /* Slightly more opaque for nested elements */
    .ui-card-inner {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
</style>

<div class="relative w-full text-white font-inter antialiased min-h-screen overflow-hidden custom-bg">
    
    <!-- Glowing Orbs (Soft Whites and Greys) to enhance the background -->
    <div class="absolute top-[-5%] right-[10%] w-[50vw] h-[50vw] rounded-full bg-white/10 blur-[140px] pointer-events-none z-0"></div>
    <div class="absolute bottom-[0%] left-[-10%] w-[60vw] h-[60vw] rounded-full bg-gray-500/20 blur-[130px] pointer-events-none z-0"></div>

    <!-- The Vertical Light Streaks Glass Texture Overlay -->
    <div class="glass-streaks"></div>

    <!-- Main Content Wrapper -->
    <div class="relative z-10 flex flex-col min-h-screen">
        <main class="pt-28 md:pt-36 pb-24 px-4 sm:px-6 max-w-6xl mx-auto w-full flex-grow">
            
            <!-- Header Section -->
            <header class="mb-10 text-center md:text-left">
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold text-white tracking-tight mb-3">
                    Discover<span class="text-gray-400">.</span>
                </h1>
                <p class="text-gray-300 text-lg md:text-xl font-medium">The best opportunities on HireFlow.</p>
            </header>

            <!-- Search Form Card -->
            <form method="GET" action="findjobs.php" class="ui-card p-3 rounded-[28px] flex flex-col md:flex-row gap-3 mb-12">
                <div class="relative flex-grow">
                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="<?= htmlspecialchars($searchTerm) ?>" placeholder="Search by role (e.g. Developer, Designer) or Company..." 
                           class="w-full pl-12 pr-6 py-4 rounded-2xl ui-card-inner text-white placeholder-gray-400 bg-transparent focus:outline-none focus:ring-2 focus:ring-white/30 text-base font-medium transition-all">
                </div>
                <button type="submit" class="px-8 py-4 bg-white hover:bg-gray-200 text-black rounded-2xl font-bold text-base transition-all shadow-md active:scale-[0.98]">
                    Search Jobs
                </button>
            </form>

            <!-- Job Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php if (count($jobs) > 0): ?>
                    <?php foreach ($jobs as $job): 
                        // Generate a clean job code
                        $job_code = 'HF-' . strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $job['job_id']), 0, 4));
                        
                        // Handle Location
                        $location = !empty($job['city']) && !empty($job['country']) 
                            ? $job['city'] . ', ' . $job['country'] 
                            : ($job['country'] ?? 'Remote');

                        // Work Model based on is_remote
                        $work_model = $job['is_remote'] ? 'Remote' : 'On-site';

                        // Fallback Logo
                        $logo_url = !empty($job['logo_url']) 
                            ? htmlspecialchars($job['logo_url']) 
                            : 'https://ui-avatars.com/api/?name='.urlencode($job['company_name']).'&background=ffffff&color=000000&rounded=true';
                    ?>
                        <div class="ui-card rounded-[32px] p-6 sm:p-8 flex flex-col group">
                            
                            <!-- Card Header: Logo & Job Code Badge -->
                            <div class="flex justify-between items-start mb-6">
                                <div class="w-16 h-16 ui-card-inner rounded-2xl flex items-center justify-center p-2.5">
                                    <img src="<?= $logo_url ?>" alt="<?= htmlspecialchars($job['company_name']) ?> Logo" class="w-full h-full object-contain rounded-xl" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($job['company_name']) ?>&background=ffffff&color=000000'">
                                </div>
                                <span class="text-[11px] font-bold text-white bg-white/10 px-3.5 py-1.5 rounded-full border border-white/20 uppercase tracking-wider shadow-sm">
                                    <?= $job_code ?>
                                </span>
                            </div>

                            <!-- Body Content -->
                            <div class="flex-grow">
                                <h3 class="text-2xl font-bold mb-1.5 text-white group-hover:text-gray-200 transition-colors">
                                    <?= htmlspecialchars($job['role']) ?>
                                </h3>
                                <div class="flex items-center gap-2 text-sm text-gray-400 font-medium mb-5">
                                    <span class="font-semibold text-white"><?= htmlspecialchars($job['company_name']) ?></span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>
                                    <span><?= htmlspecialchars($location) ?></span>
                                </div>

                                <!-- Tags -->
                                <div class="flex flex-wrap gap-2 mb-5">
                                    <?php if(!empty($job['job_type'])): ?>
                                        <span class="px-3.5 py-1.5 bg-white/10 text-white border border-white/20 rounded-full text-xs font-semibold capitalize shadow-sm">
                                            <?= htmlspecialchars(str_replace('-', ' ', $job['job_type'])) ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <span class="px-3.5 py-1.5 ui-card-inner text-gray-300 rounded-full text-xs font-semibold shadow-sm">
                                        <?= htmlspecialchars($work_model) ?>
                                    </span>
                                </div>

                                <p class="text-gray-300 leading-relaxed text-sm line-clamp-3 mb-6 font-medium">
                                    <?= htmlspecialchars($job['job_description']) ?>
                                </p>
                            </div>

                            <!-- Footer Section -->
                            <div class="pt-6 border-t border-white/10 flex items-center justify-between mt-auto">
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">AI Status</span>
                                    <span class="text-xs font-semibold text-white flex items-center gap-1.5 mt-0.5">
                                        <span class="w-2 h-2 rounded-full bg-white shadow-[0_0_8px_rgba(255,255,255,0.8)]"></span> Ready to Scan
                                    </span>
                                </div>
                                <a href="job_details.php?id=<?= $job['job_id'] ?>" 
                                   class="px-6 py-3 bg-white hover:bg-gray-200 text-black rounded-2xl font-bold text-sm shadow-md transition-all active:scale-[0.98]">
                                    View Details
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full py-20 text-center ui-card rounded-[32px] p-8">
                        <div class="text-5xl mb-4">📂</div>
                        <h3 class="text-2xl font-bold text-white mb-2">No jobs found</h3>
                        <p class="text-gray-300 text-sm font-medium">We couldn't find any open roles matching "<?= htmlspecialchars($searchTerm) ?>".</p>
                        <a href="findjobs.php" class="inline-block mt-5 px-6 py-2.5 bg-white/10 text-white border border-white/20 font-semibold rounded-xl text-sm hover:bg-white/20 transition-all">View all jobs</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php include 'footer.php'; ?>