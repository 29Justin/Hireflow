<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require 'dbconfig.php';

// --- SECURE SESSION FIX: Now uses user_id instead of firebase_uid ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id =$_SESSION['user_id'];

// Fetch the actual candidate_id and info using the NEW user_id
$stmt =$pdo->prepare("SELECT candidate_id, first_name, last_name, email, phone_number FROM public.candidates WHERE user_id = ?");
$stmt->execute([$user_id]);
$candidate =$stmt->fetch(PDO::FETCH_ASSOC);

if (!$candidate) {
    die("Candidate profile not found. Please complete onboarding.");
}
$candidate_id =$candidate['candidate_id'];

// --- HANDLE POST REQUEST (APPLICATION SUBMISSION) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Force strict JSON response and clear any accidental output
    ob_start(); 
    header('Content-Type: application/json');
    
    try {
        $job_id = $_POST['job_id'] ?? '';$ats_score = isset($_POST['ats_score']) ? intval($_POST['ats_score']) : null;

        // 1. Validation
        if (empty($job_id)) throw new Exception("Job ID is missing.");
        
        // FIXED: Using the word "or" instead of symbols so it copies perfectly
        if (!isset($_FILES['resume']) or$_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Please upload a valid PDF resume.");
        }

        // 2. Check if already applied
        $checkStmt =$pdo->prepare("SELECT application_id FROM public.applications WHERE job_id = ? AND candidate_id = ?");
        $checkStmt->execute([$job_id,$candidate_id]);
        if ($checkStmt->fetch()) {
            throw new Exception("You have already applied for this position.");
        }

        // 3. Handle File Upload (Save to Recruiter/upload/resumes)
        $uploadDir = __DIR__ . '/Recruiter/upload/resumes/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true); 
        }

        $fileExt = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
        if ($fileExt !== 'pdf') {
            throw new Exception("Only PDF files are allowed.");
        }

        // Rename the file securely: resume_CANDIDATEID_JOBID_TIMESTAMP.pdf
        $cleanFirstName = preg_replace("/[^a-zA-Z0-9]/", "", $candidate['first_name']);
        $newFileName = 'resume_' .$cleanFirstName . '_' . substr(preg_replace('/[^a-zA-Z0-9]/', '', $job_id), 0, 6) . '_' . time() . '.pdf';$destPath = $uploadDir .$newFileName;
        
        // Relative URL stored in DB (so recruiter portal can click it)
        $publicUrl = 'Recruiter/upload/resumes/' .$newFileName;

        if (!move_uploaded_file($_FILES['resume']['tmp_name'],$destPath)) {
            throw new Exception("Server error: Failed to save the uploaded resume to recruiter storage.");
        }

        // 4. Insert Application into Database
        $insertStmt =$pdo->prepare("
            INSERT INTO public.applications (job_id, candidate_id, status, ats_score, resume_url)
            VALUES (?, ?, 'Applied', ?, ?)
        ");
        $insertStmt->execute([$job_id,$candidate_id, $ats_score,$publicUrl]);

        ob_clean();
        echo json_encode(['success' => true]);
        exit;

    } catch (Exception $e) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

// --- HANDLE GET REQUEST (PAGE LOAD) ---
$job_id = trim($_GET['id'] ?? '');
if (empty($job_id)) {
    header("Location: findjobs.php");
    exit;
}

// Fetch Job Details
$stmt =$pdo->prepare("
    SELECT j.job_id, j.role, j.job_description, c.name as company_name 
    FROM public.jobs j 
    JOIN public.companies c ON j.company_id = c.company_id 
    WHERE j.job_id = ?
");
$stmt->execute([$job_id]);
$job =$stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    die("Job not found or is no longer accepting applications.");
}

// Check if candidate already applied for UI rendering
$checkStmt =$pdo->prepare("SELECT application_id FROM public.applications WHERE job_id = ? AND candidate_id = ?");
$checkStmt->execute([$job_id,$candidate_id]);
$already_applied =$checkStmt->fetchColumn() ? true : false;
?>

<?php include 'header.php'; ?>

    <!-- Import Inter Font and Define Modern Dark Glass UI Aesthetics -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        .font-inter {
            font-family: 'Inter', sans-serif;
        }
        
        /* Sleek Black, Grey, and White Gradient */
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
        }

        /* Slightly more opaque for nested elements */
        .ui-card-inner {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        /* Upload Zone Dark Theme Adjustments */
        .upload-zone { border: 2px dashed rgba(255,255,255,0.2); transition: all 0.3s ease; }
        .upload-zone.dragover { border-color: #ffffff; background-color: rgba(255,255,255,0.1); }
        
        @keyframes scan { 0% { width: 0%; } 100% { width: 100%; } }
        .animate-scan { animation: scan 2s ease-in-out forwards; }
    </style>

    <!-- Main Content Area with Dark Background -->
    <div class="relative w-full text-white font-inter antialiased min-h-screen overflow-hidden custom-bg flex flex-col">
        
        <!-- Glowing Orbs -->
        <div class="absolute top-[10%] right-[10%] w-[50vw] h-[50vw] rounded-full bg-white/10 blur-[140px] pointer-events-none z-0"></div>
        <div class="absolute bottom-[20%] left-[-10%] w-[60vw] h-[60vw] rounded-full bg-gray-500/20 blur-[130px] pointer-events-none z-0"></div>

        <!-- The Vertical Light Streaks Glass Texture Overlay -->
        <div class="glass-streaks"></div>

        <!-- Main Content Wrapper -->
        <main class="flex-grow flex items-center justify-center px-4 pt-32 pb-12 relative z-10 w-full">
            <div class="ui-card w-full max-w-2xl p-8 md:p-12 relative overflow-hidden rounded-[32px]">
                
                <a href="job_details.php?id=<?= urlencode($job_id) ?>" class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-white font-medium mb-6 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Back to Job Details
                </a>

                <div class="mb-8">
                    <h1 class="text-3xl font-bold tracking-tight mb-2 text-white">Submit Application</h1>
                    <p class="text-gray-300 text-lg font-medium"><?= htmlspecialchars($job['role']) ?> at <?= htmlspecialchars($job['company_name']) ?></p>
                </div>

                <?php if ($already_applied): ?>
                    <div class="bg-green-500/10 border border-green-500/20 rounded-2xl p-8 text-center">
                        <div class="w-16 h-16 bg-green-500/20 text-green-400 rounded-full flex items-center justify-center mx-auto mb-4 border border-green-500/30">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        </div>
                        <h2 class="text-2xl font-bold text-green-400 mb-2">You've Already Applied</h2>
                        <p class="text-green-200/80 mb-6">Your application for this role is currently being processed.</p>
                        <a href="findjobs.php" class="px-8 py-3 bg-white text-black font-bold rounded-full shadow-sm hover:bg-gray-200 transition-all inline-block">Browse More Jobs</a>
                    </div>
                <?php else: ?>

                    <div id="raw-job-description" class="hidden"><?= htmlspecialchars($job['job_description']) ?></div>
                    <div id="statusMessage" class="hidden text-center text-sm font-medium py-3 px-4 rounded-xl mb-6 break-words"></div>

                    <form id="applicationForm" class="space-y-8">
                        <input type="hidden" id="job_id" value="<?= htmlspecialchars($job_id) ?>">

                        <div>
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider border-b border-white/10 pb-2 mb-4">1. Confirm Your Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase ml-1 mb-1">First Name</label>
                                    <input type="text" value="<?= htmlspecialchars($candidate['first_name']) ?>" disabled class="w-full px-5 py-3 rounded-2xl ui-card-inner text-gray-400 cursor-not-allowed border-dashed">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase ml-1 mb-1">Last Name</label>
                                    <input type="text" value="<?= htmlspecialchars($candidate['last_name']) ?>" disabled class="w-full px-5 py-3 rounded-2xl ui-card-inner text-gray-400 cursor-not-allowed border-dashed">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase ml-1 mb-1">Email Address</label>
                                    <input type="email" value="<?= htmlspecialchars($candidate['email']) ?>" disabled class="w-full px-5 py-3 rounded-2xl ui-card-inner text-gray-400 cursor-not-allowed border-dashed">
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider border-b border-white/10 pb-2 mb-4">2. Upload Resume (PDF Only)</h3>
                            <label id="drop-zone" class="upload-zone block w-full h-48 rounded-3xl flex flex-col items-center justify-center cursor-pointer bg-white/5 hover:bg-white/10">
                                <div id="upload-content" class="flex flex-col items-center pointer-events-none">
                                    <div class="w-12 h-12 bg-white/10 rounded-full shadow-sm flex items-center justify-center text-white mb-3 border border-white/20">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                    </div>
                                    <span class="font-bold text-gray-200">Click or drag PDF to upload</span>
                                    <span class="text-xs text-gray-400 mt-1">Maximum size: 5MB</span>
                                </div>
                                <div id="file-selected" class="hidden flex flex-col items-center pointer-events-none">
                                    <div class="w-12 h-12 bg-green-500/20 text-green-400 rounded-full flex items-center justify-center mb-3 border border-green-500/30">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                    </div>
                                    <span id="filename-display" class="font-bold text-green-400">resume.pdf</span>
                                    <span class="text-xs text-green-200/80 mt-1">Ready to scan</span>
                                </div>
                                <input type="file" id="resumeFile" accept="application/pdf" class="hidden">
                            </label>
                        </div>

                        <div id="processing-state" class="hidden py-4 text-center">
                            <div class="w-full bg-white/10 rounded-full h-3 mb-3 overflow-hidden border border-white/10"><div id="progress-bar" class="bg-white h-full w-0 shadow-[0_0_10px_rgba(255,255,255,0.8)]"></div></div>
                            <p id="process-text" class="text-sm font-bold text-white animate-pulse">Initializing ATS Scan...</p>
                        </div>

                        <button type="submit" id="submitBtn" class="w-full py-4 mt-4 bg-white text-black rounded-full font-bold text-lg shadow-xl hover:bg-gray-200 transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                            Analyze & Submit Application
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

        const statusMessage = document.getElementById('statusMessage');
        const form = document.getElementById('applicationForm');
        const fileInput = document.getElementById('resumeFile');
        const dropZone = document.getElementById('drop-zone');
        const uploadContent = document.getElementById('upload-content');
        const fileSelected = document.getElementById('file-selected');
        const filenameDisplay = document.getElementById('filename-display');
        
        function showMessage(msg, isError = true) {
            statusMessage.textContent = msg;
            statusMessage.className = `text-center text-sm font-medium py-3 px-4 rounded-xl mb-6 ${isError ? 'bg-red-500/10 text-red-400 border border-red-500/20' : 'bg-green-500/10 text-green-400 border border-green-500/20'}`;
            statusMessage.classList.remove('hidden');
        }

        if(fileInput) {
            fileInput.addEventListener('change', function(e) {
                if(this.files && this.files[0]) {
                    const file = this.files[0];
                    if(file.type !== 'application/pdf') {
                        showMessage('Please select a valid PDF file.');
                        this.value = '';
                        return;
                    }
                    uploadContent.classList.add('hidden');
                    fileSelected.classList.remove('hidden');
                    filenameDisplay.textContent = file.name;
                    statusMessage.classList.add('hidden');
                }
            });

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, e => { e.preventDefault(); e.stopPropagation(); }, false);
            });
            
            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => dropZone.classList.add('dragover'), false);
            });
            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => dropZone.classList.remove('dragover'), false);
            });

            dropZone.addEventListener('drop', (e) => {
                const file = e.dataTransfer.files[0];
                if(file && file.type === 'application/pdf') {
                    fileInput.files = e.dataTransfer.files;
                    uploadContent.classList.add('hidden');
                    fileSelected.classList.remove('hidden');
                    filenameDisplay.textContent = file.name;
                } else {
                    showMessage('Please select a valid PDF file.');
                }
            });
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
                            text += content.items.map(item => item.str).join(" ") + " ";
                        }
                        resolve(text);
                    } catch(err) { reject(err); }
                };
                reader.readAsArrayBuffer(file);
            });
        }

        function calculateScore(resumeText, jdText) {
            const sanitize = (text) => text.toLowerCase().replace(/[^a-z0-9]/g, ' ').split(/\s+/).filter(word => word.length > 3);
            const jdWords = new Set(sanitize(jdText));
            const resumeWords = new Set(sanitize(resumeText));
            let matches = 0;
            jdWords.forEach(word => { if (resumeWords.has(word)) matches++; });
            const matchRatio = jdWords.size > 0 ? (matches / jdWords.size) : 0;
            let score = Math.floor(40 + (matchRatio * 60)); 
            return score > 99 ? 99 : (score < 15 ? 15 : score);
        }

        if(form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                if(!fileInput.files || fileInput.files.length === 0) {
                    showMessage("Please upload a PDF resume before submitting.");
                    return;
                }

                const btn = document.getElementById('submitBtn');
                const pState = document.getElementById('processing-state');
                const pBar = document.getElementById('progress-bar');
                const pText = document.getElementById('process-text');
                
                btn.disabled = true;
                btn.classList.add('hidden');
                pState.classList.remove('hidden');

                try {
                    pText.innerText = "Scanning Resume PDF...";
                    pBar.classList.add('animate-scan');
                    const resumeText = await extractTextFromPDF(fileInput.files[0]);
                    
                    pText.innerText = "Calculating ATS Match...";
                    const jdText = document.getElementById('raw-job-description').innerText;
                    const atsScore = calculateScore(resumeText, jdText);

                    pText.innerText = `ATS Score: ${atsScore}%. Submitting...`;
                    
                    const formData = new FormData();
                    formData.append('job_id', document.getElementById('job_id').value);
                    formData.append('ats_score', atsScore);
                    formData.append('resume', fileInput.files[0]);

                    const response = await fetch('apply.php', { method: 'POST', body: formData });
                    const result = await response.json();

                    if (result.success) {
                        pText.innerText = "Application Complete!";
                        pText.style.color = "#4ade80"; // green-400
                        setTimeout(() => { window.location.href = 'candidate_dashboard.php'; }, 1500);
                    } else { throw new Error(result.message); }

                } catch(error) {
                    console.error(error);
                    const msg = error.message ? error.message : "An error occurred.";
                    showMessage(msg);
                    btn.disabled = false;
                    btn.classList.remove('hidden');
                    pState.classList.add('hidden');
                }
            });
        }
    </script>

<?php include 'footer.php'; ?>