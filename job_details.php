<?php
session_start();
require 'dbconfig.php'; // Ensure your secure DB connection is included

// --- SECURE SESSION FIX: Now uses user_id instead of firebase_uid ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// --- AI CHAT ENDPOINT (SECURE BACKEND PROXY) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'chat') {
    ob_start(); // Trap any hidden PHP warnings
    
    if (!file_exists('aiconfig.php')) {
        ob_clean();
        echo json_encode(['choices' => [['message' => ['content' => 'Error: aiconfig.php file is missing in this folder.']]]]);
        exit();
    }
    
    require_once 'aiconfig.php'; 
    
    $input = json_decode(file_get_contents('php://input'), true);
    $userText = $input['message'] ?? '';
    $jdText = $input['jd'] ?? '';

    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    $payload = json_encode([
        'model' => 'llama-3.1-8b-instant',
        'messages' => [
            ['role' => 'system', 'content' => "You are a helpful recruiter for HireFlow. Answer the candidate's question based strictly on this Job Description: \n\n" . $jdText],
            ['role' => 'user', 'content' => $userText]
        ]
    ]);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Bypasses localhost SSL certificate issues
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . GROQ_API_KEY
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    
    $response = curl_exec($ch);
    $curl_err = curl_error($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    ob_clean(); // Wipe clean
    header('Content-Type: application/json');
    
    if ($curl_err) {
        echo json_encode(['choices' => [['message' => ['content' => 'cURL Error: ' . $curl_err]]]]);
    } else if ($httpcode !== 200) {
        $errData = json_decode($response, true);
        $apiMsg = $errData['error']['message'] ?? "HTTP Error $httpcode: $response";
        echo json_encode(['choices' => [['message' => ['content' => 'Groq API Error: ' . $apiMsg]]]]);
    } else {
        echo $response;
    }
    exit();
}

// --- FETCH JOB DETAILS ---
if (!isset($_GET['id'])) {
    header("Location: findjobs.php");
    exit;
}

$job_id = trim($_GET['id']);

// 1. Fetch Job + Company Info
$stmt = $pdo->prepare("
    SELECT j.*, c.name as company_name, c.logo_url 
    FROM public.jobs j 
    JOIN public.companies c ON j.company_id = c.company_id 
    WHERE j.job_id = ?
");
$stmt->execute([$job_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    header("Location: findjobs.php"); 
    exit;
}

// 2. Fetch Roadmap
$stmt = $pdo->prepare("SELECT * FROM public.job_roadmaps WHERE job_id = ? ORDER BY step_number ASC");
$stmt->execute([$job_id]);
$roadmaps = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Fetch Mock Questions
$stmt = $pdo->prepare("SELECT * FROM public.job_mock_questions WHERE job_id = ?");
$stmt->execute([$job_id]);
$raw_questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Reformat questions for Javascript
$quizData = ['Easy' => [], 'Medium' => [], 'Hard' => []];
$opt_map = ['A' => 0, 'B' => 1, 'C' => 2, 'D' => 3];

foreach ($raw_questions as $q) {
    $diff = $q['difficulty'] ?? 'Medium';
    if (!isset($quizData[$diff])) $quizData[$diff] = [];
    
    $quizData[$diff][] = [
        'q' => $q['question_text'],
        'options' => [$q['option_a'], $q['option_b'], $q['option_c'], $q['option_d']],
        'correct' => $opt_map[$q['correct_option']] ?? 0
    ];
}

// Map the correct schema fields for the UI tags
$job_type_display = !empty($job['job_type']) ? str_replace('-', ' ', $job['job_type']) : '';
$work_model_display = (isset($job['is_remote']) && $job['is_remote']) ? 'Remote' : 'On-site';

// Fallback logic for Job Logo & Location
$logo_url = !empty($job['logo_url']) ? htmlspecialchars($job['logo_url']) : 'https://ui-avatars.com/api/?name='.urlencode($job['company_name']).'&background=000&color=fff&rounded=true';
$location = !empty($job['city']) && !empty($job['country']) 
    ? htmlspecialchars($job['city'] . ', ' . $job['country']) 
    : htmlspecialchars($job['country'] ?? 'Remote');

// Clean Job Code using the first 6 characters of the UUID safely
$job_code = 'HF-' . strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $job['job_id']), 0, 6));

// Include page header / navigation
include 'header.php';
?>

<!-- Custom visual styles matching the Dark Glass Theme -->
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
    
    body { 
        font-family: 'Inter', sans-serif; 
        -webkit-font-smoothing: antialiased; 
        -moz-osx-font-smoothing: grayscale;
        margin: 0;
        padding: 0;
        color: #ffffff;
    }

    /* Sleek Black, Grey, and White Gradient */
    .custom-bg {
        background: linear-gradient(to top right, #000000 10%, #171717 45%, #404040 75%, #a3a3a3 100%);
        position: relative;
        background-attachment: fixed;
    }

    /* Vertical light streaks glass texture */
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

    /* Chat & Quiz Specific Styles */
    @keyframes scan { 0% { width: 0%; } 100% { width: 100%; } }
    .animate-scan { animation: scan 2s ease-in-out forwards; }
    
    .chat-bubble { max-width: 85%; padding: 12px 16px; border-radius: 20px; font-size: 14px; line-height: 1.5; margin-bottom: 12px; }
    .user-msg { background: #ffffff; color: #000000; align-self: flex-end; border-bottom-right-radius: 4px; font-weight: 500; }
    .ai-msg { background: rgba(255, 255, 255, 0.1); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.1); align-self: flex-start; border-bottom-left-radius: 4px; word-wrap: break-word; }
    
    .typing-dot { display: inline-block; width: 6px; height: 6px; background: rgba(255,255,255,0.6); border-radius: 50%; margin-right: 3px; animation: typing 1.4s infinite ease-in-out both; }
    .typing-dot:nth-child(1) { animation-delay: -0.32s; } .typing-dot:nth-child(2) { animation-delay: -0.16s; }
    @keyframes typing { 0%, 80%, 100% { transform: scale(0); } 40% { transform: scale(1); } }
    
    .correct-answer { border-color: rgba(34, 197, 94, 0.5) !important; background-color: rgba(34, 197, 94, 0.1) !important; color: #4ade80 !important; }
    .wrong-answer { border-color: rgba(239, 68, 68, 0.5) !important; background-color: rgba(239, 68, 68, 0.1) !important; color: #f87171 !important; }
</style>

<script src="https://cdn.jsdelivr.net/npm/markdown-it@13.0.1/dist/markdown-it.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>

<div class="custom-bg min-h-screen relative overflow-x-hidden selection:bg-white/30 selection:text-white">
    <!-- Ambient Glow Backdrops -->
    <div class="fixed top-[-5%] right-[10%] w-[50vw] h-[50vw] rounded-full bg-white/10 blur-[140px] pointer-events-none z-0"></div>
    <div class="fixed bottom-[0%] left-[-10%] w-[60vw] h-[60vw] rounded-full bg-gray-500/20 blur-[130px] pointer-events-none z-0"></div>
    <div class="glass-streaks"></div>

    <!-- Main Container -->
    <main class="relative z-10 pt-32 pb-24 px-4 sm:px-6">
        <div class="max-w-6xl mx-auto">
            
            <a href="findjobs.php" class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-white font-bold mb-8 transition-colors group">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="group-hover:-translate-x-1 transition-transform"><path d="m15 18-6-6 6-6"/></svg>
                Back to Jobs
            </a>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <!-- Main Job Details Section -->
                <div class="lg:col-span-8">
                    <div class="ui-card rounded-[24px] md:rounded-[32px] p-6 sm:p-10 mb-8">
                        <header class="mb-8">
                            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                                <div class="flex flex-wrap gap-2">
                                    <?php if(!empty($job_type_display)): ?>
                                        <span class="px-3.5 py-1 rounded-full bg-white/10 text-white border border-white/20 text-xs font-bold uppercase tracking-wider shadow-sm">
                                            <?= htmlspecialchars($job_type_display) ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <span class="px-3.5 py-1 rounded-full bg-white/10 text-white border border-white/20 text-xs font-bold uppercase tracking-wider shadow-sm">
                                        <?= htmlspecialchars($work_model_display) ?>
                                    </span>
                                </div>
                                <span class="text-[11px] font-extrabold text-gray-300 bg-black/30 px-3.5 py-1.5 rounded-full border border-white/10 uppercase tracking-wider">
                                    <?= $job_code ?>
                                </span>
                            </div>

                            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight mb-4 text-white"><?= htmlspecialchars($job['role']) ?></h1>
                            <div class="flex items-center gap-2 text-base font-semibold text-gray-400">
                                <span class="text-gray-200 font-bold"><?= htmlspecialchars($job['company_name']) ?></span>
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>
                                <span><?= $location ?></span>
                            </div>
                        </header>

                        <hr class="border-white/10 mb-8">

                        <div id="raw-job-description" class="hidden"><?= htmlspecialchars($job['job_description']) ?></div>

                        <article id="job-description-content" class="text-gray-300 leading-relaxed space-y-6 text-base font-medium whitespace-pre-wrap"><?= htmlspecialchars($job['job_description']) ?></article>
                    </div>
                </div>

                <!-- Sidebar Tool Panel -->
                <div class="lg:col-span-4 relative">
                    <div class="sticky top-28 space-y-6">
                        
                        <!-- Company & Application Action Card -->
                        <div class="ui-card rounded-[24px] md:rounded-[32px] p-6 relative overflow-hidden">
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center p-2.5 shadow-sm">
                                    <img src="<?= $logo_url ?>" alt="Company Logo" class="w-full h-full object-contain rounded-xl">
                                </div>
                                <div>
                                    <h3 class="font-extrabold text-lg text-white leading-tight"><?= htmlspecialchars($job['company_name']) ?></h3>
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mt-0.5"><?= $job_code ?></p>
                                </div>
                            </div>
                            <form action="apply.php" method="GET">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($job_id) ?>">
                                <button type="submit" class="w-full py-4 bg-white hover:bg-gray-200 text-black rounded-2xl font-bold text-base shadow-lg transition-all active:scale-95 cursor-pointer text-center">
                                    Apply Now
                                </button>
                            </form>
                        </div>

                        <!-- Smart Prep Tools Card -->
                        <div class="ui-card rounded-[24px] md:rounded-[32px] p-6 border-t-4 border-t-white/40">
                            <h3 class="font-extrabold text-white text-lg mb-5 flex items-center gap-2">⚡ Smart Prep Tools</h3>

                            <!-- Recruiter AI Widget -->
                            <div class="mb-5">
                                <label class="block text-xs font-extrabold text-gray-400 uppercase tracking-wider mb-2">Have Doubts?</label>
                                <button onclick="openAIChat()" class="w-full flex items-center gap-3 p-3.5 bg-white/5 border border-white/10 rounded-2xl hover:bg-white/10 transition-all cursor-pointer text-left group">
                                    <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center text-white shadow-sm border border-white/20">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-200 group-hover:text-white transition-colors">Ask Recruiter AI</p>
                                        <p class="text-[11px] font-semibold text-gray-400">Powered by Groq</p>
                                    </div>
                                </button>
                            </div>

                            <!-- ATS Checker Widget -->
                            <div class="mb-5">
                                <label class="block text-xs font-extrabold text-gray-400 uppercase tracking-wider mb-2">Check Hiring Chances</label>
                                <button onclick="openATSModal()" class="w-full group relative flex items-center justify-between p-3.5 bg-white/5 border border-white/10 rounded-2xl hover:bg-white/10 transition-all cursor-pointer text-left">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center text-white shadow-sm border border-white/20">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-200 group-hover:text-white transition-colors">Upload Resume</p>
                                            <p class="text-[11px] font-semibold text-gray-400">Get True ATS Score</p>
                                        </div>
                                    </div>
                                    <span class="text-white font-bold group-hover:translate-x-1 transition-transform">→</span>
                                </button>
                            </div>

                            <!-- Mock Tests Widget -->
                            <div class="mb-5">
                                <label class="block text-xs font-extrabold text-gray-400 uppercase tracking-wider mb-2">Take Mock Test</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <button onclick="renderQuiz('Easy')" class="py-2.5 bg-white/5 text-emerald-400 text-xs font-bold rounded-xl border border-emerald-400/30 transition-all cursor-pointer <?= empty($quizData['Easy']) ? 'opacity-50 cursor-not-allowed border-white/10 text-gray-500' : 'hover:bg-white/10 active:scale-95' ?>" <?= empty($quizData['Easy']) ? 'disabled' : '' ?>>Easy</button>
                                    <button onclick="renderQuiz('Medium')" class="py-2.5 bg-white/5 text-amber-400 text-xs font-bold rounded-xl border border-amber-400/30 transition-all cursor-pointer <?= empty($quizData['Medium']) ? 'opacity-50 cursor-not-allowed border-white/10 text-gray-500' : 'hover:bg-white/10 active:scale-95' ?>" <?= empty($quizData['Medium']) ? 'disabled' : '' ?>>Med</button>
                                    <button onclick="renderQuiz('Hard')" class="py-2.5 bg-white/5 text-rose-400 text-xs font-bold rounded-xl border border-rose-400/30 transition-all cursor-pointer <?= empty($quizData['Hard']) ? 'opacity-50 cursor-not-allowed border-white/10 text-gray-500' : 'hover:bg-white/10 active:scale-95' ?>" <?= empty($quizData['Hard']) ? 'disabled' : '' ?>>Hard</button>
                                </div>
                            </div>

                            <!-- Roadmap Widget -->
                            <?php if(count($roadmaps) > 0): ?>
                            <div>
                                <label class="block text-xs font-extrabold text-gray-400 uppercase tracking-wider mb-2">Preparation</label>
                                <button onclick="openRoadmap()" class="w-full flex items-center justify-center gap-2 py-3.5 ui-card-inner text-white font-bold text-sm rounded-2xl hover:bg-white/10 transition-all cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>
                                    View Job Roadmap
                                </button>
                            </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal 1: Recruiter AI Chat -->
    <div id="modal-ai" class="fixed inset-0 bg-black/60 backdrop-blur-md z-[100] hidden flex items-center justify-center p-4">
        <div class="ui-card w-full max-w-md h-[600px] rounded-[32px] shadow-2xl flex flex-col overflow-hidden border border-white/20">
            <div class="p-4 bg-white/5 border-b border-white/10 text-white flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-sm leading-tight text-white">HireFlow AI (Groq)</h3>
                        <p class="text-[10px] text-gray-400 font-medium">Recruiter Assistant</p>
                    </div>
                </div>
                <button onclick="closeModal('modal-ai')" class="text-white/50 hover:text-white text-2xl font-bold cursor-pointer px-2">&times;</button>
            </div>
            
            <div id="ai-chat-box" class="flex-1 p-4 overflow-y-auto flex flex-col bg-black/20 space-y-2">
                <div class="ai-msg chat-bubble">Hello! I am ready to answer any questions about the <b><?= htmlspecialchars($job['role']) ?></b> role. Ask away!</div>
            </div>

            <div class="p-3 bg-white/5 border-t border-white/10">
                <form onsubmit="handleAIChat(event)" class="relative">
                    <input type="text" id="ai-input" class="w-full pl-4 pr-12 py-3.5 bg-white/10 rounded-2xl text-sm text-white placeholder-gray-400 outline-none focus:ring-2 focus:ring-white/30 font-medium transition-all" placeholder="E.g., What are the requirements?">
                    <button type="submit" class="absolute right-2 top-2 p-2 bg-white text-black rounded-xl hover:bg-gray-200 transition-all cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal 2: ATS Scanner -->
    <div id="modal-ats" class="fixed inset-0 bg-black/60 backdrop-blur-md z-[100] hidden flex items-center justify-center p-4">
        <div class="ui-card w-full max-w-md rounded-[32px] shadow-2xl p-6 text-center border border-white/20">
            <h3 class="text-2xl font-extrabold text-white mb-2">Check ATS Match</h3>
            <p class="text-xs font-semibold text-gray-400 mb-6">We will scan your PDF and match keywords against this specific Job Description.</p>
            
            <div id="ats-upload-state">
                <label class="block w-full h-44 border-2 border-dashed border-white/20 rounded-2xl flex flex-col items-center justify-center cursor-pointer hover:border-white hover:bg-white/5 transition-all p-4">
                    <div class="w-12 h-12 bg-white/10 text-white rounded-xl flex items-center justify-center mb-2 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    </div>
                    <span class="text-sm font-bold text-gray-200">Click to upload Resume (PDF)</span>
                    <input type="file" accept="application/pdf" class="hidden" onchange="processRealATS(this)">
                </label>
            </div>
            
            <div id="ats-processing-state" class="hidden py-8">
                <div class="w-full bg-white/10 rounded-full h-3 mb-3 overflow-hidden shadow-inner">
                    <div id="ats-bar" class="bg-white h-full w-0"></div>
                </div>
                <p id="ats-status-text" class="text-sm font-bold text-gray-300 animate-pulse">Reading PDF...</p>
            </div>
            
            <div id="ats-result-state" class="hidden">
                <div class="w-28 h-28 rounded-full border-8 border-white/10 flex items-center justify-center mx-auto mb-4 relative shadow-lg" id="ats-circle">
                    <span class="text-3xl font-extrabold text-white" id="ats-score-val">0</span>
                </div>
                <h4 class="text-xl font-extrabold text-white mb-5" id="ats-verdict">Analyzing...</h4>
                <button onclick="closeModal('modal-ats')" class="w-full py-3.5 ui-card-inner rounded-2xl font-bold text-sm text-white hover:bg-white/10 transition-all cursor-pointer">Close</button>
            </div>
        </div>
    </div>

    <!-- Modal 3: Skill Assessment Mock Test -->
    <div id="modal-test" class="fixed inset-0 bg-black/60 backdrop-blur-md z-[100] hidden flex items-center justify-center p-4">
        <div class="ui-card w-full max-w-lg max-h-[90vh] rounded-[32px] shadow-2xl p-6 sm:p-8 relative overflow-hidden flex flex-col border border-white/20">
            <div id="quiz-header-bar" class="absolute top-0 left-0 w-full h-2 bg-white/20"></div>
            
            <div class="flex justify-between items-center mb-1">
                <h3 class="text-2xl font-extrabold text-white">Skill Assessment</h3>
                <span id="quiz-score-display" class="hidden bg-white text-black px-3.5 py-1 rounded-full text-xs font-bold">Score: 0</span>
            </div>
            <p class="text-xs font-semibold text-gray-400 mb-6">Difficulty: <span id="test-difficulty" class="font-bold text-white">Level</span></p>
            
            <div class="space-y-4 overflow-y-auto pr-2 flex-grow scrollbar-hide" id="quiz-container"></div>

            <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-white/10">
                <button onclick="closeModal('modal-test')" class="px-6 py-2.5 text-gray-400 font-bold text-sm hover:text-white transition-colors cursor-pointer">Exit</button>
                <button onclick="submitQuiz()" id="btn-quiz-check" class="px-6 py-2.5 bg-white text-black rounded-2xl font-bold text-sm shadow-md hover:bg-gray-200 transition-all cursor-pointer active:scale-95">Check Answers</button>
            </div>
        </div>
    </div>

    <!-- Modal 4: Hiring Roadmap -->
    <div id="modal-roadmap" class="fixed inset-0 bg-black/60 backdrop-blur-md z-[100] hidden flex items-center justify-center p-4">
        <div class="ui-card w-full max-w-2xl rounded-[32px] shadow-2xl p-0 overflow-hidden max-h-[80vh] flex flex-col border border-white/20">
            <div class="p-6 border-b border-white/10 bg-white/5">
                <h3 class="text-xl font-extrabold text-white">Hiring Roadmap</h3>
                <p class="text-xs font-semibold text-gray-400">The process for <?= htmlspecialchars($job['role']) ?></p>
            </div>
            <div class="p-6 sm:p-8 overflow-y-auto bg-black/20">
                <div class="relative pl-8 border-l-2 border-white/20 space-y-8">
                    <?php 
                    $colors = ['text-blue-400 bg-blue-400/20', 'text-purple-400 bg-purple-400/20', 'text-amber-400 bg-amber-400/20', 'text-emerald-400 bg-emerald-400/20'];
                    foreach($roadmaps as $index => $step): 
                        $c = $colors[$index % count($colors)];
                    ?>
                        <div class="relative">
                            <div class="absolute -left-[41px] top-0 w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm border-4 border-[#171717] shadow-sm <?= $c ?>">
                                <?= htmlspecialchars($step['step_number']) ?>
                            </div>
                            <h4 class="font-extrabold text-base text-white"><?= htmlspecialchars($step['header']) ?></h4>
                            <p class="text-sm font-medium text-gray-400 mt-1"><?= htmlspecialchars($step['description']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="p-4 border-t border-white/10 text-right bg-white/5">
                <button onclick="closeModal('modal-roadmap')" class="px-6 py-2.5 bg-white text-black rounded-2xl font-bold text-sm shadow-md hover:bg-gray-200 transition-all cursor-pointer">Got it</button>
            </div>
        </div>
    </div>
</div>

<script>
    // --- 1. GLOBAL UTILS ---
    function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

    // --- 2. SECURE BACKEND AI CHAT LOGIC ---
    function openAIChat() { document.getElementById('modal-ai').classList.remove('hidden'); }

    async function handleAIChat(e) {
        e.preventDefault();
        const inputEl = document.getElementById('ai-input');
        const chatBox = document.getElementById('ai-chat-box');
        const userText = inputEl.value.trim();
        if(!userText) return;

        // Add User Message
        const userBubble = document.createElement('div');
        userBubble.className = 'user-msg chat-bubble';
        userBubble.innerText = userText;
        chatBox.appendChild(userBubble);
        inputEl.value = '';
        chatBox.scrollTop = chatBox.scrollHeight;

        // Add Loading Indicator
        const loadingBubble = document.createElement('div');
        loadingBubble.className = 'ai-msg chat-bubble';
        loadingBubble.innerHTML = '<span class="typing-dot"></span><span class="typing-dot"></span><span class="typing-dot"></span>';
        chatBox.appendChild(loadingBubble);

        // Get Job Description
        const jdText = document.getElementById('raw-job-description').innerText;

        try {
            const response = await fetch('job_details.php?action=chat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: userText, jd: jdText })
            });

            const rawText = await response.text();
            
            try {
                const data = JSON.parse(rawText);
                if (data.choices && data.choices.length > 0 && data.choices[0].message) {
                    const aiResponse = data.choices[0].message.content;
                    if (window.markdownit && !aiResponse.startsWith("cURL Error") && !aiResponse.startsWith("Groq API") && !aiResponse.startsWith("Error")) {
                        loadingBubble.innerHTML = window.markdownit().render(aiResponse);
                    } else {
                        loadingBubble.innerText = aiResponse;
                        loadingBubble.classList.add("text-red-400", "font-semibold");
                    }
                } else {
                    loadingBubble.innerText = "Error: Unrecognized API response format.";
                }
            } catch (parseErr) {
                const cleanErr = rawText.replace(/(<([^>]+)>)/gi, "").substring(0, 150);
                loadingBubble.innerText = "PHP Error: " + cleanErr;
                loadingBubble.classList.add("text-red-400", "font-semibold");
            }
        } catch (error) {
            console.error("Fetch Error:", error);
            loadingBubble.innerText = `Network Error. Check console.`;
        }
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    // --- 3. REAL ATS LOGIC (PDF.js + Keyword Matching) ---
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

    function openATSModal() {
        document.getElementById('modal-ats').classList.remove('hidden');
        document.getElementById('ats-upload-state').classList.remove('hidden');
        document.getElementById('ats-processing-state').classList.add('hidden');
        document.getElementById('ats-result-state').classList.add('hidden');
    }

    async function extractTextFromPDF(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = async function() {
                try {
                    const typedarray = new Uint8Array(this.result);
                    const pdf = await pdfjsLib.getDocument(typedarray).promise;
                    let text = "";
                    for (let i = 1; i <= pdf.numPages; i++) {
                        const page = await pdf.getPage(i);
                        const content = await page.getTextContent();
                        const strings = content.items.map(item => item.str);
                        text += strings.join(" ") + " ";
                    }
                    resolve(text);
                } catch(err) {
                    reject(err);
                }
            };
            reader.readAsArrayBuffer(file);
        });
    }

    async function processRealATS(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            document.getElementById('ats-upload-state').classList.add('hidden');
            document.getElementById('ats-processing-state').classList.remove('hidden');
            document.getElementById('ats-bar').classList.add('animate-scan');
            
            try {
                document.getElementById('ats-status-text').innerText = "Reading PDF...";
                const resumeText = await extractTextFromPDF(file);
                
                document.getElementById('ats-status-text').innerText = "Cross-referencing Job Description...";
                const jdText = document.getElementById('raw-job-description').innerText;

                const sanitize = (text) => text.toLowerCase().replace(/[^a-z0-9]/g, ' ').split(/\s+/).filter(word => word.length > 3);
                
                const jdWords = new Set(sanitize(jdText));
                const resumeWords = new Set(sanitize(resumeText));
                
                let matches = 0;
                jdWords.forEach(word => {
                    if (resumeWords.has(word)) matches++;
                });

                const matchRatio = jdWords.size > 0 ? (matches / jdWords.size) : 0;
                let finalScore = Math.floor(40 + (matchRatio * 60)); 
                if (finalScore > 99) finalScore = 99;
                if (finalScore < 15) finalScore = 15;

                setTimeout(() => {
                    document.getElementById('ats-processing-state').classList.add('hidden');
                    document.getElementById('ats-result-state').classList.remove('hidden');
                    
                    const scoreEl = document.getElementById('ats-score-val');
                    const verdictEl = document.getElementById('ats-verdict');
                    const circleEl = document.getElementById('ats-circle');
                    
                    scoreEl.innerText = finalScore;
                    
                    if (finalScore >= 75) {
                        circleEl.className = "w-28 h-28 rounded-full border-8 border-green-500 flex items-center justify-center mx-auto mb-4 shadow-[0_0_20px_rgba(34,197,94,0.3)]";
                        scoreEl.className = "text-3xl font-extrabold text-green-400";
                        verdictEl.innerText = "Great Match!";
                        verdictEl.className = "text-xl font-extrabold text-green-400 mb-5";
                    } else if (finalScore >= 50) {
                        circleEl.className = "w-28 h-28 rounded-full border-8 border-amber-400 flex items-center justify-center mx-auto mb-4 shadow-[0_0_20px_rgba(251,191,36,0.3)]";
                        scoreEl.className = "text-3xl font-extrabold text-amber-400";
                        verdictEl.innerText = "Good Potential";
                        verdictEl.className = "text-xl font-extrabold text-amber-400 mb-5";
                    } else {
                        circleEl.className = "w-28 h-28 rounded-full border-8 border-red-500 flex items-center justify-center mx-auto mb-4 shadow-[0_0_20px_rgba(239,68,68,0.3)]";
                        scoreEl.className = "text-3xl font-extrabold text-red-400";
                        verdictEl.innerText = "Needs Optimization";
                        verdictEl.className = "text-xl font-extrabold text-red-400 mb-5";
                    }
                }, 1000);

            } catch(err) {
                console.error("PDF Parsing Error:", err);
                document.getElementById('ats-status-text').innerText = "Error reading PDF. Please ensure it's text-based.";
                document.getElementById('ats-status-text').className = "text-sm font-bold text-red-400";
                document.getElementById('ats-bar').className = "bg-red-500 h-full w-full";
            }
        }
    }

    // --- 4. DYNAMIC MOCK TEST LOGIC ---
    const quizData = <?= json_encode($quizData) ?>;
    let currentQuizType = 'Medium';

    function renderQuiz(difficulty) {
        if (!quizData[difficulty] || quizData[difficulty].length === 0) return;
        currentQuizType = difficulty;
        
        document.getElementById('modal-test').classList.remove('hidden');
        const span = document.getElementById('test-difficulty');
        span.innerText = difficulty;
        const bar = document.getElementById('quiz-header-bar');
        
        document.getElementById('quiz-score-display').classList.add('hidden');
        const btn = document.getElementById('btn-quiz-check');
        btn.innerText = "Check Answers";
        btn.disabled = false;
        btn.classList.remove('opacity-50', 'cursor-not-allowed');

        if(difficulty === 'Easy') { span.className = 'font-bold text-emerald-400'; bar.className = 'absolute top-0 left-0 w-full h-2 bg-emerald-500'; }
        else if(difficulty === 'Medium') { span.className = 'font-bold text-amber-400'; bar.className = 'absolute top-0 left-0 w-full h-2 bg-amber-500'; }
        else { span.className = 'font-bold text-rose-400'; bar.className = 'absolute top-0 left-0 w-full h-2 bg-rose-500'; }

        const container = document.getElementById('quiz-container');
        container.innerHTML = '';

        const questions = quizData[difficulty];
        questions.forEach((item, index) => {
            const qDiv = document.createElement('div');
            qDiv.className = 'p-5 ui-card-inner rounded-2xl transition-colors duration-300';
            qDiv.id = `q-card-${index}`;
            
            let html = `<p class="font-bold text-sm text-white mb-4">${index + 1}. ${item.q}</p><div class="space-y-3 text-sm">`;
            item.options.forEach((opt, optIndex) => {
                if(opt && opt.trim() !== '') {
                    html += `
                        <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl bg-white/5 hover:bg-white/10 border border-transparent hover:border-white/30 transition-all font-medium text-gray-300">
                            <input type="radio" name="q${index}" value="${optIndex}" class="accent-white w-4 h-4"> 
                            <span>${opt}</span>
                        </label>`;
                }
            });
            html += `</div>`;
            qDiv.innerHTML = html;
            container.appendChild(qDiv);
        });
    }

    function submitQuiz() {
        const questions = quizData[currentQuizType];
        let score = 0;
        
        questions.forEach((item, index) => {
            const selected = document.querySelector(`input[name="q${index}"]:checked`);
            const card = document.getElementById(`q-card-${index}`);
            
            if (selected) {
                const val = parseInt(selected.value);
                if (val === item.correct) {
                    score++;
                    card.classList.add('correct-answer');
                } else {
                    card.classList.add('wrong-answer');
                }
            } else {
                card.classList.add('wrong-answer');
            }
            const inputs = document.querySelectorAll(`input[name="q${index}"]`);
            inputs.forEach(inp => inp.disabled = true);
        });

        const scoreDisplay = document.getElementById('quiz-score-display');
        scoreDisplay.innerText = `Score: ${score}/${questions.length}`;
        scoreDisplay.classList.remove('hidden');

        const btn = document.getElementById('btn-quiz-check');
        btn.innerText = "Completed";
        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-not-allowed');
    }

    // --- 5. ROADMAP LOGIC ---
    function openRoadmap() { document.getElementById('modal-roadmap').classList.remove('hidden'); }
</script>

<?php include 'footer.php'; ?>