<?php
session_start();

// Redirect logged-in users to the dashboard
// Notice we now use 'user_id' instead of 'firebase_uid'
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Handle the AJAX POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ob_start(); 
    require 'dbconfig.php'; // Ensure SUPABASE_URL and SUPABASE_PUBLISHABLE_KEY are in here
    
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    if (isset($data['email']) && isset($data['password'])) {
        $email =$data['email'];
        $password =$data['password'];

        // 1. Authenticate with Supabase REST API
        $ch = curl_init(SUPABASE_URL . '/auth/v1/token?grant_type=password');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: ' . SUPABASE_PUBLISHABLE_KEY,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'email' => $email,
            'password' => $password
        ]));

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $authResult = json_decode($response, true);

        // 2. Check if login was successful
        if ($httpcode >= 200 && $httpcode < 300 && isset($authResult['user']['id'])) {
            $user_id =$authResult['user']['id'];
            $user_email =$authResult['user']['email'];
            
            // 3. Check if candidate profile exists in your database
            $stmt =$pdo->prepare("SELECT candidate_id FROM public.candidates WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $exists =$stmt->fetchColumn() ? true : false;

            // ✅ SECURE FIX: ONLY set the session if the user ACTUALLY has a profile!
            if ($exists) {
                $_SESSION['user_id'] =$user_id;
            }

            ob_clean();
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'exists' => $exists,
                'user_id' => $user_id,
                'email' => $user_email
            ]);
            exit();
        } else {
            // Login failed (wrong password, doesn't exist, etc.)
            ob_clean();
            header('Content-Type: application/json');
            $errorMsg = isset($authResult['error_description']) ?$authResult['error_description'] : 'Invalid email or password.';
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
    <title>Log In - HireFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                <h1 class="text-3xl md:text-4xl font-bold tracking-tight mb-3">Welcome back.</h1>
                <p class="text-[#86868b] text-sm md:text-base">Sign in to access your HireFlow dashboard.</p>
            </div>

            <div id="statusMessage" class="hidden text-center text-sm font-medium py-3 px-4 rounded-xl mb-6"></div>

            <form id="loginForm" class="space-y-4">
                <div><input type="email" id="email" required placeholder="Email address" class="input-field w-full px-5 py-4 rounded-2xl border border-gray-200 outline-none text-base"></div>
                <div><input type="password" id="password" required placeholder="Password" class="input-field w-full px-5 py-4 rounded-2xl border border-gray-200 outline-none text-base"></div>
                <div class="flex items-center justify-between text-sm px-1 pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-[#86868b] hover:text-black transition-colors">
                        <input type="checkbox" class="rounded border-gray-300 text-[#007aff] focus:ring-[#007aff]">
                        <span>Remember me</span>
                    </label>
                    <a href="#" class="text-[#007aff] font-medium hover:underline">Forgot password?</a>
                </div>
                <button type="submit" id="submitBtn" class="w-full py-4 mt-2 bg-black text-white rounded-full font-bold text-lg shadow-xl hover:opacity-80 transition-all active:scale-95 flex justify-center items-center gap-2">
                    <span>Sign In</span>
                    <svg id="spinner" class="animate-spin hidden h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </button>
            </form>

            <div class="mt-8 text-center text-sm text-[#86868b]">Don't have an account? <a href="signup.php" class="text-black font-semibold hover:underline">Sign up</a></div>
            <div class="mt-6 text-center text-[10px] text-gray-400 max-w-xs mx-auto leading-tight">This site is protected by reCAPTCHA Enterprise and the Google <a href="https://policies.google.com/privacy" class="underline">Privacy Policy</a> and <a href="https://policies.google.com/terms" class="underline">Terms of Service</a> apply.</div>
        </div>
    </main>

    <script>
        const loginForm = document.getElementById('loginForm');
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
                    try { resolve(await grecaptcha.enterprise.execute('6LeljHAsAAAAAKCUuSYJv6uYmirQF4mhdSWonitu', {action: 'LOGIN'})); } 
                    catch (err) { reject(err); }
                });
            });
        }

        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            submitBtn.disabled = true; 
            submitBtn.classList.add('opacity-80', 'cursor-not-allowed'); 
            spinner.classList.remove('hidden'); 
            statusMessage.classList.add('hidden');
            
            try {
                // await getRecaptchaToken(); // Uncomment if you are strictly enforcing reCAPTCHA on your live server
                
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;

                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    if (result.exists) {
                        showMessage("Login successful! Redirecting...", false);
                        setTimeout(() => { window.location.href = 'index.php'; }, 1000);
                    } else {
                        showMessage("Almost there! Redirecting to setup...", false);
                        // Passes user_id to onboarding instead of firebase_uid
                        setTimeout(() => { window.location.href = `onboarding.php?uid=${result.user_id}&email=${encodeURIComponent(result.email)}`; }, 1000);
                    }
                } else {
                    throw new Error(result.message || "Invalid credentials.");
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