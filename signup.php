<?php
session_start();

// Redirect already logged-in users to the dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Handle the AJAX POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ob_start(); 
    require 'dbconfig.php'; // Make sure SUPABASE_URL and SUPABASE_PUBLISHABLE_KEY are defined here
    
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    if (isset($data['email']) && isset($data['password'])) {
        $email =$data['email'];
        $password =$data['password'];

        // 1. Create a new user with Supabase Auth REST API
        $ch = curl_init(SUPABASE_URL . '/auth/v1/signup');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: ' . SUPABASE_PUBLISHABLE_KEY,
            'Content-Type: application/json'
        ]);
        // Send email and password to create the account
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'email' => $email,
            'password' => $password
        ]));

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $authResult = json_decode($response, true);

        // 2. Check if signup was successful
        if ($httpcode >= 200 && $httpcode < 300 && isset($authResult['user']['id'])) {
            $user_id =$authResult['user']['id'];
            $user_email =$authResult['user']['email'];
            
            // NOTE: We do NOT set $_SESSION['user_id'] here yet. 
            // We want to force them through onboarding.php first!

            ob_clean();
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'user_id' => $user_id,
                'email' => $user_email
            ]);
            exit();
        } else {
            // Signup failed (e.g., user already exists, password too weak)
            ob_clean();
            header('Content-Type: application/json');
            // Supabase uses 'msg' for signup errors sometimes, or 'error_description'
            $errorMsg = $authResult['msg'] ?? ($authResult['error_description'] ?? 'Signup failed. Email may already be in use.');
            echo json_encode(['success' => false, 'message' => $errorMsg]);
            exit();
        }
    } else {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Email and password required']);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - HireFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Note: Keeping reCAPTCHA script as requested, though you may implement it in PHP later if needed -->
    <script src="https://www.google.com/recaptcha/enterprise.js?render=6LeljHAsAAAAAKCUuSYJv6uYmirQF4mhdSWonitu"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #fbfbfd; -webkit-font-smoothing: antialiased; }
        .ios-card-white { background: white; border-radius: 36px; border: 1px solid #f2f2f7; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.04); transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); }
        .ios-card-white:hover { transform: translateY(-5px); box-shadow: 0 20px 40px -15px rgba(0,0,0,0.08); }
        .input-field { background-color: #f5f5f7; transition: all 0.2s ease; }
        .input-field:focus { background-color: #ffffff; border-color: #007aff; box-shadow: 0 0 0 4px rgba(0, 122, 255, 0.1); outline: none; }
    </style>
</head>
<body class="text-[#1d1d1f] min-h-screen flex flex-col">

    <nav class="w-full z-50 px-4 md:px-6 py-6 absolute top-0">
        <div class="max-w-5xl mx-auto flex items-center justify-center relative">
            <div class="text-2xl font-bold tracking-tighter leading-tight cursor-pointer" onclick="window.location.href='index.php'">
                HireFlow.
            </div>
        </div>
    </nav>

    <main class="flex-grow flex items-center justify-center px-4 pt-24 pb-12">
        <div class="ios-card-white w-full max-w-md p-8 md:p-12 relative overflow-hidden">
            <div class="text-center mb-8">
                <h1 class="text-3xl md:text-4xl font-bold tracking-tight mb-3">Create an account.</h1>
                <p class="text-[#86868b] text-sm md:text-base">Join HireFlow to land your next big role.</p>
            </div>

            <div id="statusMessage" class="hidden text-center text-sm font-medium py-3 px-4 rounded-xl mb-6"></div>

            <form id="signupForm" class="space-y-4">
                <div><input type="email" id="email" required placeholder="Email address" class="input-field w-full px-5 py-4 rounded-2xl border border-gray-200 outline-none text-base"></div>
                
                <!-- Notice we enforce a minlength of 6 for Supabase default settings -->
                <div><input type="password" id="password" required minlength="6" placeholder="Password (min 6 characters)" class="input-field w-full px-5 py-4 rounded-2xl border border-gray-200 outline-none text-base"></div>
                
                <button type="submit" id="submitBtn" class="w-full py-4 mt-4 bg-black text-white rounded-full font-bold text-lg shadow-xl hover:opacity-80 transition-all active:scale-95 flex justify-center items-center gap-2">
                    <span>Sign Up</span>
                    <svg id="spinner" class="animate-spin hidden h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </button>
            </form>

            <div class="mt-8 text-center text-sm text-[#86868b]">Already have an account? <a href="login.php" class="text-[#007aff] font-semibold hover:underline">Sign In</a></div>
            <div class="mt-6 text-center text-[10px] text-gray-400 max-w-xs mx-auto leading-tight">This site is protected by reCAPTCHA Enterprise and the Google <a href="https://policies.google.com/privacy" class="underline">Privacy Policy</a> and <a href="https://policies.google.com/terms" class="underline">Terms of Service</a> apply.</div>
        </div>
    </main>

    <script>
        const signupForm = document.getElementById('signupForm');
        const submitBtn = document.getElementById('submitBtn');
        const spinner = document.getElementById('spinner');
        const statusMessage = document.getElementById('statusMessage');

        function showMessage(msg, isError = true) {
            statusMessage.textContent = msg;
            statusMessage.className = `text-center text-sm font-medium py-3 px-4 rounded-xl mb-6 ${isError ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-green-50 text-green-600 border border-green-100'}`;
            statusMessage.classList.remove('hidden');
        }

        async function getRecaptchaToken() {
            return new Promise((resolve, reject) => {
                grecaptcha.enterprise.ready(async () => {
                    try { resolve(await grecaptcha.enterprise.execute('6LeljHAsAAAAAKCUuSYJv6uYmirQF4mhdSWonitu', {action: 'SIGNUP'})); } 
                    catch (err) { reject(err); }
                });
            });
        }

        signupForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            submitBtn.disabled = true; 
            submitBtn.classList.add('opacity-80', 'cursor-not-allowed'); 
            spinner.classList.remove('hidden'); 
            statusMessage.classList.add('hidden');
            
            try {
                // Uncomment this if you enforce reCAPTCHA validation on the backend
                // await getRecaptchaToken(); 
                
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;

                // Send the credentials securely to our PHP backend
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage("Account created! Redirecting to setup...", false);
                    
                    // Route directly to onboarding with the brand new Supabase user_id!
                    setTimeout(() => { 
                        window.location.href = `onboarding.php?uid=${result.user_id}&email=${encodeURIComponent(result.email)}`; 
                    }, 1000);
                } else {
                    throw new Error(result.message || "Failed to create account.");
                }
            } catch (error) {
                showMessage(error.message, true);
                submitBtn.disabled = false; 
                submitBtn.classList.remove('opacity-80', 'cursor-not-allowed'); 
                spinner.classList.add('hidden');
            } 
        });
    </script>
</body>
</html>