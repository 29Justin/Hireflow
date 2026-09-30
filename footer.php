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

    <!-- Minimal Grey Footer -->
    <footer class="w-full bg-gray-200 py-8 relative z-10 mt-auto">
        <div class="max-w-6xl mx-auto text-center text-gray-600 font-medium text-sm sm:text-base">
            Hireflow a hackathon project
        </div>
    </footer>

    <!-- Glassmorphism Auth Modal Matched to Theme -->
    <div id="auth-modal" class="fixed inset-0 z-[100] hidden items-center justify-center">
        <!-- Soft blur backdrop -->
        <div id="auth-modal-backdrop" class="absolute inset-0 bg-[#0f172a]/20 backdrop-blur-sm transition-opacity"></div>
        
        <!-- Glass Modal Card -->
        <div class="relative z-10 w-full max-w-md p-8 md:p-10 mx-4 shadow-2xl modal-enter rounded-[32px] bg-white/75 backdrop-blur-[20px] border border-white/90 text-[#0f172a]">
            
            <button id="close-modal-btn" class="absolute top-6 right-6 p-2 text-[#475569] hover:text-[#0f172a] transition-colors bg-white/50 hover:bg-white rounded-full border border-slate-200 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
            
            <div class="text-center mb-8 mt-4">
                <div class="w-16 h-16 bg-[#1d4ed8]/10 text-[#1d4ed8] rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-sm border border-[#1d4ed8]/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                </div>
                <h3 class="text-3xl font-bold tracking-tight mb-3">Unlock Your Potential</h3>
                <p class="text-[#475569] text-base leading-relaxed font-medium">
                    You're just one step away. Sign in or create an account to unlock our AI-powered career tools.
                </p>
            </div>
            
            <div class="flex flex-col gap-3">
                <!-- Primary Button (Matched to Header Signup button) -->
                <a href="signup.php" class="w-full py-4 bg-[#0f172a] text-white text-center rounded-full font-semibold text-lg shadow-md hover:bg-black transition-all active:scale-95">
                    Create an account
                </a>
                <!-- Secondary Button (Glass Outline) -->
                <a href="login.php" class="w-full py-4 bg-white/50 text-[#0f172a] border border-slate-300 text-center rounded-full font-semibold text-lg hover:bg-white transition-all active:scale-95 shadow-sm">
                    Log in
                </a>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Mobile Menu Logic
        const btn = document.getElementById('menu-btn');
        const menu = document.getElementById('mobile-menu');

        if (btn && menu) {
            btn.addEventListener('click', () => {
                menu.classList.toggle('hidden');
                menu.classList.toggle('flex');
            });
        }

        // Pass PHP login state safely to JavaScript
        const isUserLoggedIn = <?php echo isset($isUserLoggedIn) && $isUserLoggedIn ? 'true' : 'false'; ?>;

        // Modal Elements & Functions
        const authModal = document.getElementById('auth-modal');
        const closeModalBtn = document.getElementById('close-modal-btn');
        const authModalBackdrop = document.getElementById('auth-modal-backdrop');

        function showAuthModal() {
            if (authModal) {
                authModal.classList.remove('hidden');
                authModal.classList.add('flex');
            }
        }

        function hideAuthModal() {
            if (authModal) {
                authModal.classList.add('hidden');
                authModal.classList.remove('flex');
            }
        }

        if (closeModalBtn) closeModalBtn.addEventListener('click', hideAuthModal);
        if (authModalBackdrop) authModalBackdrop.addEventListener('click', hideAuthModal);

        // Protect specific links based on auth state
        const protectedLinks = document.querySelectorAll('.protected-link');
        
        protectedLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                if (!isUserLoggedIn) {
                    // Stop navigation
                    e.preventDefault();
                    // Show auth popup modal
                    showAuthModal();
                }
            });
        });
    </script>
</body>
</html>