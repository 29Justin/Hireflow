<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - HireFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #fbfbfd;
            -webkit-font-smoothing: antialiased;
        }
        .ios-glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }
        .ios-card-white {
            background: white;
            border-radius: 36px;
            border: 1px solid #f2f2f7;
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.04);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .input-field {
            background-color: #f5f5f7;
            transition: all 0.2s ease;
        }
        .input-field:focus {
            background-color: #ffffff;
            border-color: #007aff;
            box-shadow: 0 0 0 4px rgba(0, 122, 255, 0.1);
        }
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
                <div class="w-16 h-16 bg-blue-50 text-[#007aff] rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-sm border border-blue-100">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                </div>
                <h1 class="text-3xl font-bold tracking-tight mb-3">Reset Password</h1>
                <p class="text-[#86868b] text-sm md:text-base">Enter your email and we'll send you a link to reset your password.</p>
            </div>

            <div id="statusMessage" class="hidden text-center text-sm font-medium py-3 px-4 rounded-xl mb-6"></div>

            <form id="resetForm" class="space-y-4">
                <div>
                    <input type="email" id="email" required placeholder="Email address" 
                        class="input-field w-full px-5 py-4 rounded-2xl border border-gray-200 outline-none text-base">
                </div>

                <button type="submit" id="submitBtn" 
                    class="w-full py-4 mt-2 bg-black text-white rounded-full font-bold text-lg shadow-xl hover:opacity-80 transition-all active:scale-95 flex justify-center items-center gap-2">
                    <span>Send Reset Link</span>
                    <svg id="spinner" class="animate-spin hidden h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </form>

            <div class="mt-8 text-center text-sm text-[#86868b]">
                Remember your password? <a href="login.php" class="text-[#007aff] font-semibold hover:underline">Back to login</a>
            </div>
        </div>
    </main>

    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/12.9.0/firebase-app.js";
        import { getAuth, sendPasswordResetEmail } from "https://www.gstatic.com/firebasejs/12.9.0/firebase-auth.js";

        // Firebase configuration
        const firebaseConfig = {
            apiKey: "AIzaSyDM82sxUfgE8VihxpTtAUpbMPciBTra2kE",
            authDomain: "hireflow-42fb8.firebaseapp.com",
            projectId: "hireflow-42fb8",
            storageBucket: "hireflow-42fb8.firebasestorage.app",
            messagingSenderId: "222291112106",
            appId: "1:222291112106:web:053c4ad590a6e5bbb660d3"
        };

        // Initialize Firebase
        const app = initializeApp(firebaseConfig);
        const auth = getAuth(app);

        // UI Elements
        const resetForm = document.getElementById('resetForm');
        const submitBtn = document.getElementById('submitBtn');
        const spinner = document.getElementById('spinner');
        const statusMessage = document.getElementById('statusMessage');

        // Helper Function to Show Messages
        function showMessage(msg, isError = true) {
            statusMessage.textContent = msg;
            statusMessage.classList.remove('hidden', 'bg-red-50', 'text-red-600', 'bg-green-50', 'text-green-600', 'border', 'border-red-100', 'border-green-100');
            
            if (isError) {
                statusMessage.classList.add('bg-red-50', 'text-red-600', 'border', 'border-red-100');
            } else {
                statusMessage.classList.add('bg-green-50', 'text-green-600', 'border', 'border-green-100');
            }
        }

        // Handle Password Reset
        resetForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Get the email and trim any accidental whitespace
            const email = document.getElementById('email').value.trim();

            if (!email) {
                showMessage("Please enter your email address.", true);
                return;
            }

            // Update UI to loading state
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-80', 'cursor-not-allowed');
            spinner.classList.remove('hidden');
            statusMessage.classList.add('hidden');

            try {
                // Execute Firebase Call
                await sendPasswordResetEmail(auth, email);
                
                // Show success message
                showMessage("Success! Check your email inbox for the reset link.", false);
                
                // Clear the input field
                document.getElementById('email').value = '';

            } catch (error) {
                console.error("Full Reset Password Error:", error);
                
                // Advanced Error Handling
                let errorMessage = "An error occurred. Please try again.";
                
                if (error.code === 'auth/user-not-found') {
                    errorMessage = "We couldn't find an account with that email address.";
                } else if (error.code === 'auth/invalid-email') {
                    errorMessage = "Please enter a valid email address.";
                } else if (error.code === 'auth/network-request-failed') {
                    errorMessage = "Network error. Are you testing this via a local server (http://localhost)?";
                } else if (error.code === 'auth/too-many-requests') {
                    errorMessage = "Too many attempts. Please wait a few minutes and try again.";
                } else {
                    // If it's an unknown error, print the exact code so you can debug it
                    errorMessage = `Error: ${error.code || error.message}`;
                }
                
                showMessage(errorMessage, true);
            } finally {
                // Restore UI state
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-80', 'cursor-not-allowed');
                spinner.classList.add('hidden');
            }
        });
    </script>
</body>
</html>