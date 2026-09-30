<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require 'dbconfig.php';

// --- SECURE SESSION CHECK ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id =$_SESSION['user_id'];

// --- HANDLE POST REQUEST (UPDATE PROFILE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ob_start();
    
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    if ($data) {
        try {
            // Default to 'na' if fields are empty
            $country_code = !empty($data['country_code']) ? $data['country_code'] : 'na';$phone_number = !empty($data['phone_number']) ?$data['phone_number'] : 'na';
            $city         = !empty($data['city']) ? $data['city'] : 'na';$state        = !empty($data['state']) ?$data['state'] : 'na';
            
            // Format for PostgreSQL ARRAY
            $skills_pg_array = '{na}';
            if (!empty($data['skills'])) {
                $skills_list = array_map('trim', explode(',',$data['skills']));
                $skills_pg_array = '{' . implode(',', $skills_list) . '}';
            }
            
            $full_name = trim($data['first_name'] . ' ' .$data['last_name']);
            
            // Update Query
            $stmt =$pdo->prepare("
                UPDATE public.candidates 
                SET name = ?, first_name = ?, last_name = ?, country_code = ?, phone_number = ?, city = ?, state = ?, skills = ?
                WHERE user_id = ?
            ");
            
            $stmt->execute([$full_name,
                $data['first_name'],$data['last_name'],
                $country_code,$phone_number,
                $city,$state,
                $skills_pg_array,$user_id
            ]);
            
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
            exit();

        } catch (Exception $e) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
            exit();
        }
    }
}

// --- HANDLE GET REQUEST (FETCH CURRENT DATA) ---
try {
    $stmt =$pdo->prepare("SELECT * FROM public.candidates WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $candidate =$stmt->fetch(PDO::FETCH_ASSOC);

    if (!$candidate) {
        header("Location: onboarding.php");
        exit;
    }

    // Clean up PostgreSQL Array format (e.g. "{React, Node}" to "React, Node") for the textarea
    $skills_display = '';
    if (!empty($candidate['skills']) &&$candidate['skills'] !== '{na}') {
        $clean_skills = trim($candidate['skills'], '{}');
        // Handle postgres array quotes if they exist
        $skills_array = explode(',',$clean_skills);
        $skills_array = array_map(function($s) { return trim($s, '"'); }, $skills_array);
        $skills_display = implode(', ',$skills_array);
    }
    
    // Safely remove 'na' for the UI so the placeholders show up instead
    $ui_country_code = ($candidate['country_code'] === 'na') ? '' : htmlspecialchars($candidate['country_code']);$ui_phone = ($candidate['phone_number'] === 'na') ? '' : htmlspecialchars($candidate['phone_number']);
    $ui_city = ($candidate['city'] === 'na') ? '' : htmlspecialchars($candidate['city']);$ui_state = ($candidate['state'] === 'na') ? '' : htmlspecialchars($candidate['state']);

} catch (Exception $e) {
    die("Error loading profile: " . $e->getMessage());
}
?>

<?php include 'header.php'; ?>

    <!-- Import Inter Font and Define Modern Dark Glass UI Aesthetics -->
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

        /* Slightly more opaque for nested elements (Inputs, Badges) */
        .ui-card-inner {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
    </style>

    <!-- Main Content Area with Dark Background -->
    <div class="relative w-full text-white font-inter antialiased min-h-screen overflow-hidden custom-bg flex flex-col">
        
        <!-- Glowing Orbs (Soft Whites and Greys) -->
        <div class="absolute top-[10%] right-[10%] w-[50vw] h-[50vw] rounded-full bg-white/10 blur-[140px] pointer-events-none z-0"></div>
        <div class="absolute bottom-[20%] left-[-10%] w-[60vw] h-[60vw] rounded-full bg-gray-500/20 blur-[130px] pointer-events-none z-0"></div>

        <!-- The Vertical Light Streaks Glass Texture Overlay -->
        <div class="glass-streaks"></div>

        <!-- Main Content Wrapper -->
        <main class="flex-grow flex items-center justify-center px-4 pt-32 pb-12 relative z-10 w-full">
            <div class="ui-card w-full max-w-3xl p-8 md:p-12 relative overflow-hidden rounded-[32px]">
                
                <div class="flex items-center gap-4 mb-8 border-b border-white/10 pb-8">
                    <div class="w-20 h-20 ui-card-inner rounded-full flex items-center justify-center text-white text-3xl font-bold shadow-md">
                        <?= strtoupper(substr($candidate['first_name'], 0, 1)) . strtoupper(substr($candidate['last_name'], 0, 1)) ?>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight text-white"><?= htmlspecialchars($candidate['name']) ?></h1>
                        <p class="text-gray-400 text-sm mt-1"><?= htmlspecialchars($candidate['email']) ?></p>
                    </div>
                </div>

                <div id="statusMessage" class="hidden text-center text-sm font-medium py-3 px-4 rounded-xl mb-6 break-words"></div>

                <form id="profileForm" class="space-y-6">
                    
                    <div>
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Personal Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase ml-1 mb-2">First Name</label>
                                <input type="text" id="first_name" required value="<?= htmlspecialchars($candidate['first_name']) ?>" class="w-full px-5 py-4 rounded-2xl ui-card-inner text-white placeholder-gray-500 bg-transparent focus:outline-none focus:ring-2 focus:ring-white/30 text-base font-medium transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase ml-1 mb-2">Last Name</label>
                                <input type="text" id="last_name" required value="<?= htmlspecialchars($candidate['last_name']) ?>" class="w-full px-5 py-4 rounded-2xl ui-card-inner text-white placeholder-gray-500 bg-transparent focus:outline-none focus:ring-2 focus:ring-white/30 text-base font-medium transition-all">
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 mt-8">Contact & Location</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
                            <div class="md:col-span-1">
                                <label class="block text-xs font-bold text-gray-400 uppercase ml-1 mb-2">Code</label>
                                <input type="text" id="country_code" placeholder="+91" value="<?= $ui_country_code ?>" class="w-full px-5 py-4 rounded-2xl ui-card-inner text-white placeholder-gray-500 bg-transparent focus:outline-none focus:ring-2 focus:ring-white/30 text-base font-medium transition-all">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-400 uppercase ml-1 mb-2">Phone Number</label>
                                <input type="tel" id="phone_number" placeholder="Enter phone" value="<?= $ui_phone ?>" class="w-full px-5 py-4 rounded-2xl ui-card-inner text-white placeholder-gray-500 bg-transparent focus:outline-none focus:ring-2 focus:ring-white/30 text-base font-medium transition-all">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase ml-1 mb-2">City</label>
                                <input type="text" id="city" placeholder="e.g. Mumbai" value="<?= $ui_city ?>" class="w-full px-5 py-4 rounded-2xl ui-card-inner text-white placeholder-gray-500 bg-transparent focus:outline-none focus:ring-2 focus:ring-white/30 text-base font-medium transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase ml-1 mb-2">State</label>
                                <input type="text" id="state" placeholder="e.g. Maharashtra" value="<?= $ui_state ?>" class="w-full px-5 py-4 rounded-2xl ui-card-inner text-white placeholder-gray-500 bg-transparent focus:outline-none focus:ring-2 focus:ring-white/30 text-base font-medium transition-all">
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 mt-8">Professional</h3>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase ml-1 mb-2">Key Skills (Comma separated)</label>
                            <textarea id="skills" rows="3" placeholder="e.g. React, Node.js, Project Management" class="w-full px-5 py-4 rounded-2xl ui-card-inner text-white placeholder-gray-500 bg-transparent focus:outline-none focus:ring-2 focus:ring-white/30 text-base font-medium transition-all resize-none"><?= htmlspecialchars($skills_display) ?></textarea>
                        </div>
                    </div>

                    <div class="pt-6 mt-4 flex items-center justify-between border-t border-white/10">
                        <a href="logout.php" class="px-6 py-3 bg-red-500/10 text-red-400 border border-red-500/20 rounded-xl font-bold hover:bg-red-500/20 transition-colors">Logout</a>
                        <button type="submit" id="submitBtn" class="px-8 py-4 bg-white text-black rounded-full font-bold text-lg shadow-lg hover:bg-gray-200 transition-all active:scale-95 flex items-center gap-2">
                            <span>Save Changes</span>
                            <svg id="spinner" class="animate-spin hidden h-5 w-5 text-black" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </button>
                    </div>
                </form>
            </div>
        </main>

    </div>

    <script>
        const statusMessage = document.getElementById('statusMessage');
        const form = document.getElementById('profileForm');
        const submitBtn = document.getElementById('submitBtn');
        const spinner = document.getElementById('spinner');

        function showMessage(msg, isError = true) {
            statusMessage.textContent = msg;
            // Updated classes to fit the dark theme
            statusMessage.className = `text-center text-sm font-medium py-4 px-4 rounded-xl mb-6 ${isError ? 'bg-red-500/10 text-red-400 border border-red-500/20' : 'bg-green-500/10 text-green-400 border border-green-500/20'}`;
            statusMessage.classList.remove('hidden');
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            submitBtn.disabled = true; 
            submitBtn.classList.add('opacity-80', 'cursor-not-allowed'); 
            spinner.classList.remove('hidden'); 
            statusMessage.classList.add('hidden');

            const profileData = {
                first_name: document.getElementById('first_name').value, 
                last_name: document.getElementById('last_name').value,
                country_code: document.getElementById('country_code').value, 
                phone_number: document.getElementById('phone_number').value,
                city: document.getElementById('city').value, 
                state: document.getElementById('state').value,
                skills: document.getElementById('skills').value
            };

            try {
                const response = await fetch('profile.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(profileData)
                });
                
                const rawText = await response.text();
                
                try {
                    const result = JSON.parse(rawText);
                    if (result.success) {
                        showMessage(result.message, false);
                        // Reload the page after 1.5s so the UI profile circle updates
                        setTimeout(() => { window.location.reload(); }, 1500);
                    } else {
                        throw new Error(result.message);
                    }
                } catch (parseErr) {
                    console.error("Raw response:", rawText);
                    throw new Error("Server error. Check console.");
                }
            } catch (err) {
                console.error("Error:", err);
                showMessage(err.message, true);
            } finally {
                submitBtn.disabled = false; 
                submitBtn.classList.remove('opacity-80', 'cursor-not-allowed'); 
                spinner.classList.add('hidden');
            }
        });
    </script>

<?php include 'footer.php'; ?>