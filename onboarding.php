<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Start buffering to trap any HTML errors/warnings
    ob_start();
    
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    // We are now looking for 'user_id', NOT 'firebase_uid'
    if (isset($data['user_id'])) {
        try {
            require 'dbconfig.php';
            
            // ✅ Default to 'na' if fields are empty
            $country_code = !empty($data['country_code']) ? $data['country_code'] : 'na';$phone_number = !empty($data['phone_number']) ?$data['phone_number'] : 'na';
            $city         = !empty($data['city']) ? $data['city'] : 'na';$state        = !empty($data['state']) ?$data['state'] : 'na';
            
            // Fix for PostgreSQL ARRAY column: default to '{na}' if empty
            $skills_pg_array = '{na}';
            if (!empty($data['skills'])) {
                $skills_list = array_map('trim', explode(',',$data['skills']));
                $skills_pg_array = '{' . implode(',', $skills_list) . '}';
            }
            
            // Combine first and last name for the 'name' column
            $full_name = trim($data['first_name'] . ' ' .$data['last_name']);
            
            $stmt =$pdo->prepare("
                INSERT INTO public.candidates 
                (user_id, name, first_name, last_name, email, country_code, phone_number, city, state, skills) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            // Exactly 10 matching values (Comments removed for safe copy-pasting)
            $stmt->execute([
                $data['user_id'],$full_name,
                $data['first_name'],$data['last_name'],
                $data['email'],$country_code,
                $phone_number,$city,
                $state,$skills_pg_array
            ]);
            
            // Log the user in
            $_SESSION['user_id'] =$data['user_id'];
            
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit();

        } catch (Exception $e) {
            // Error 23505 means the account already exists
            if ($e->getCode() == '23505') { 
                $_SESSION['user_id'] =$data['user_id'];
                ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => true]); 
                exit();
            }
            
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit();
        }
    } else {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No User ID provided.']);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Profile - HireFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #fbfbfd; -webkit-font-smoothing: antialiased; }
        .ios-card-white { background: white; border-radius: 36px; border: 1px solid #f2f2f7; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.04); transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); }
        .input-field { background-color: #f5f5f7; transition: all 0.2s ease; }
        .input-field:focus { background-color: #ffffff; border-color: #007aff; box-shadow: 0 0 0 4px rgba(0, 122, 255, 0.1); outline: none; }
    </style>
</head>
<body class="text-[#1d1d1f] min-h-screen flex flex-col">

    <nav class="w-full z-50 px-4 md:px-6 py-6 absolute top-0">
        <div class="max-w-5xl mx-auto flex items-center justify-center relative">
            <div class="text-2xl font-bold tracking-tighter leading-tight cursor-pointer">HireFlow.</div>
        </div>
    </nav>

    <main class="flex-grow flex items-center justify-center px-4 pt-24 pb-12">
        <div class="ios-card-white w-full max-w-2xl p-8 md:p-12 relative overflow-hidden">
            <div class="text-center mb-8">
                <h1 class="text-3xl md:text-4xl font-bold tracking-tight mb-3">Complete your profile.</h1>
                <p class="text-[#86868b] text-sm md:text-base" id="welcomeText">Let's get you set up for your next big opportunity.</p>
            </div>

            <div id="statusMessage" class="hidden text-center text-sm font-medium py-3 px-4 rounded-xl mb-6 break-words"></div>

            <form id="onboardingForm" class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1 ml-1">First Name *</label><input type="text" id="first_name" required class="input-field w-full px-5 py-3 rounded-2xl border border-gray-200 text-base"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1 ml-1">Last Name *</label><input type="text" id="last_name" required class="input-field w-full px-5 py-3 rounded-2xl border border-gray-200 text-base"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="md:col-span-1"><label class="block text-sm font-medium text-gray-700 mb-1 ml-1">Code</label><input type="text" id="country_code" placeholder="+91" class="input-field w-full px-5 py-3 rounded-2xl border border-gray-200 text-base"></div>
                    <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1 ml-1">Phone Number</label><input type="tel" id="phone_number" class="input-field w-full px-5 py-3 rounded-2xl border border-gray-200 text-base"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1 ml-1">City</label><input type="text" id="city" class="input-field w-full px-5 py-3 rounded-2xl border border-gray-200 text-base"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1 ml-1">State</label><input type="text" id="state" class="input-field w-full px-5 py-3 rounded-2xl border border-gray-200 text-base"></div>
                </div>

                <div><label class="block text-sm font-medium text-gray-700 mb-1 ml-1">Key Skills (Comma separated)</label><textarea id="skills" rows="2" placeholder="e.g. React, Node.js, Project Management" class="input-field w-full px-5 py-3 rounded-2xl border border-gray-200 text-base resize-none"></textarea></div>

                <button type="submit" id="submitBtn" class="w-full py-4 mt-4 bg-black text-white rounded-full font-bold text-lg shadow-xl hover:opacity-80 transition-all active:scale-95 flex justify-center items-center gap-2">
                    <span>Complete Onboarding</span>
                    <svg id="spinner" class="animate-spin hidden h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </button>
            </form>
        </div>
    </main>

    <script>
        const statusMessage = document.getElementById('statusMessage');
        function showMessage(msg, isError = true) {
            statusMessage.textContent = msg;
            statusMessage.className = `text-center text-sm font-medium py-3 px-4 rounded-xl mb-6 ${isError ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-green-50 text-green-600 border border-green-100'}`;
            statusMessage.classList.remove('hidden');
        }

        const urlParams = new URLSearchParams(window.location.search);
        
        // Grab the uid passed from signup.php/login.php
        const supabaseUserId = urlParams.get('uid');
        const userEmail = urlParams.get('email');

        if (!supabaseUserId || !userEmail) window.location.href = 'login.php';
        document.getElementById('welcomeText').innerText = `Setting up profile for ${decodeURIComponent(userEmail)}`;

        const form = document.getElementById('onboardingForm');
        const submitBtn = document.getElementById('submitBtn');
        const spinner = document.getElementById('spinner');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            submitBtn.disabled = true; 
            submitBtn.classList.add('opacity-80', 'cursor-not-allowed'); 
            spinner.classList.remove('hidden'); 
            statusMessage.classList.add('hidden');

            const candidateData = {
                user_id: supabaseUserId,
                email: decodeURIComponent(userEmail),
                first_name: document.getElementById('first_name').value, 
                last_name: document.getElementById('last_name').value,
                country_code: document.getElementById('country_code').value, 
                phone_number: document.getElementById('phone_number').value,
                city: document.getElementById('city').value, 
                state: document.getElementById('state').value,
                skills: document.getElementById('skills').value
            };

            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(candidateData)
                });
                
                const rawText = await response.text();
                
                try {
                    const result = JSON.parse(rawText);

                    if (result.success) {
                        showMessage("Profile created successfully! Redirecting...", false);
                        setTimeout(() => { window.location.href = 'index.php'; }, 1000);
                    } else {
                        throw new Error(result.message);
                    }
                } catch (parseErr) {
                    console.error("PHP outputted non-JSON data:", rawText);
                    const cleanText = rawText.replace(/(<([^>]+)>)/gi, "").substring(0, 100);
                    throw new Error("DB Error: " + cleanText);
                }
            } catch (err) {
                console.error("Error:", err);
                showMessage("Failed to save profile. " + err.message, true);
                submitBtn.disabled = false; 
                submitBtn.classList.remove('opacity-80', 'cursor-not-allowed'); 
                spinner.classList.add('hidden');
            }
        });
    </script>
</body>
</html>