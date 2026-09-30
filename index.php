<?php include 'header.php'; ?>

    <!-- Import Inter Font and Define Modern Glass UI Aesthetics -->
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

        /* Vertical light streaks glass texture overlay (subtle for dark mode) */
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
        
        /* High-contrast Accent Colors */
        .text-accent {
            color: #ffffff;
        }
        .bg-accent {
            background-color: #ffffff;
            color: #000000;
        }
        .hover-bg-accent:hover {
            background-color: #e5e5e5;
        }

        /* Subtle floating animation for the Mascot */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
            100% { transform: translateY(0px); }
        }
        .animate-float {
            animation: float 5s ease-in-out infinite;
        }
    </style>

    <!-- Main Content Area with Dark Background -->
    <div class="relative w-full text-white font-inter antialiased overflow-hidden custom-bg">
        
        <!-- Glowing Orbs (Soft Whites and Greys) to enhance the background -->
        <div class="absolute top-[-5%] right-[10%] w-[50vw] h-[50vw] rounded-full bg-white/10 blur-[140px] pointer-events-none z-0"></div>
        <div class="absolute bottom-[0%] left-[-10%] w-[60vw] h-[60vw] rounded-full bg-gray-500/20 blur-[130px] pointer-events-none z-0"></div>

        <!-- The Vertical Light Streaks Glass Texture Overlay -->
        <div class="glass-streaks"></div>

        <!-- Main Content Wrapper -->
        <div class="relative z-10 flex flex-col min-h-screen">
            
            <!-- Hero Section -->
            <header class="text-center px-4 sm:px-6 pt-12 md:pt-24 pb-10 md:pb-20 max-w-5xl mx-auto flex-grow w-full">
                <div class="flex flex-col items-center justify-center">
                    
                    <!-- MASCOT IMAGE: Sized for a landscape hero illustration -->
                    <div class="relative z-50 animate-float mb-4 md:mb-8 flex justify-center items-center w-full">
                        <img 
                            src="Background.png" 
                            alt="HireFlow Team Mascot" 
                            class="object-contain drop-shadow-2xl w-full max-w-lg md:max-w-2xl h-auto"
                            onerror="this.onerror=null; this.outerHTML='<div style=\'width:100%;max-width:600px;height:200px;border:2px dashed #ffffff;border-radius:20px;display:flex;align-items:center;justify-content:center;color:#ffffff;font-size:14px;background:rgba(255,255,255,0.05);\'>Image path broken - ensure Background.png is in the same folder</div>';"
                        >
                    </div>

                    <h1 class="text-4xl sm:text-6xl md:text-[80px] font-bold tracking-tight text-white leading-[1.1] mb-1 md:mb-4">
                        Find Your Dream Job
                    </h1>
                    
                    <!-- Clean "Effortlessly" text -->
                    <div class="hover:scale-105 transition-transform duration-300 mb-2">
                        <span class="text-white text-4xl sm:text-5xl md:text-[64px] font-bold tracking-tight leading-none drop-shadow-md">Effortlessly</span>
                    </div>
                </div>
                
                <p class="mt-6 md:mt-10 text-gray-300 text-base sm:text-lg md:text-2xl max-w-2xl mx-auto leading-relaxed font-medium px-2">
                    HireFlow uses intelligent matching to connect you with opportunities that align with your career goals.
                </p>

                <div class="mt-8 md:mt-10 flex flex-col sm:flex-row justify-center gap-4 max-w-xs sm:max-w-none mx-auto w-full">
                    <a href="findjobs.php" class="protected-link w-full sm:w-auto px-8 py-3.5 sm:py-4 rounded-full bg-white hover:bg-gray-200 text-black font-semibold text-base sm:text-lg shadow-lg transition-all active:scale-95 text-center">
                        Get Started
                    </a>
                    <a href="resume.php" class="protected-link w-full sm:w-auto px-8 py-3.5 sm:py-4 rounded-full ui-card text-white font-medium text-base sm:text-lg hover:bg-white/10 transition-all active:scale-95 text-center">
                        Build Resume
                    </a>
                </div>
            </header>

            <!-- Stats Section -->
            <section class="max-w-5xl mx-auto px-4 sm:px-6 md:px-6 mb-12 md:my-16 w-full">
                <div class="grid grid-cols-2 md:grid-cols-4 ui-card rounded-[24px] md:rounded-[40px] overflow-hidden py-4 md:py-8">
                    <div class="text-center p-4 border-r border-b md:border-b-0 border-white/10">
                        <div class="text-2xl sm:text-3xl font-bold text-white mb-1">50K+</div>
                        <div class="text-gray-400 text-[10px] sm:text-xs font-semibold uppercase tracking-wider">Jobs Posted</div>
                    </div>
                    <div class="text-center p-4 border-b md:border-b-0 md:border-r border-white/10">
                        <div class="text-2xl sm:text-3xl font-bold text-white mb-1">25K+</div>
                        <div class="text-gray-400 text-[10px] sm:text-xs font-semibold uppercase tracking-wider">Happy Users</div>
                    </div>
                    <div class="text-center p-4 border-r md:border-r border-white/10">
                        <div class="text-2xl sm:text-3xl font-bold text-white mb-1">95%</div>
                        <div class="text-gray-400 text-[10px] sm:text-xs font-semibold uppercase tracking-wider">Success Rate</div>
                    </div>
                    <div class="text-center p-4">
                        <div class="text-2xl sm:text-3xl font-bold text-white mb-1">500+</div>
                        <div class="text-gray-400 text-[10px] sm:text-xs font-semibold uppercase tracking-wider">Companies</div>
                    </div>
                </div>
            </section>

            <!-- Optimiser Section -->
            <section class="px-4 sm:px-6 py-8 md:py-12 w-full">
                <div class="max-w-6xl mx-auto ui-card p-6 sm:p-12 lg:p-16 relative overflow-hidden rounded-[24px] md:rounded-[32px]">
                    <div class="flex flex-col lg:flex-row gap-8 lg:gap-16 items-center">
                        <div class="lg:w-1/2 text-center lg:text-left">
                            <div class="inline-flex items-center gap-2 mb-6 ui-card-inner px-4 py-2 rounded-xl">
                                <div class="w-2 h-2 rounded-full bg-white"></div>
                                <span class="text-white font-semibold uppercase text-[10px] tracking-wider">Optimiser</span>
                            </div>
                            <h2 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold text-white tracking-tight leading-tight mb-4 md:mb-6">Edit away.</h2>
                            <p class="text-gray-300 text-base md:text-xl leading-relaxed max-w-md mx-auto lg:mx-0 font-medium">
                                Upload your CV and paste the job description. Our AI performs a pixel-perfect analysis of your fit.
                            </p>
                        </div>

                        <div class="lg:w-1/2 w-full">
                            <div class="ui-card-inner rounded-[20px] md:rounded-[32px] p-6 md:p-8">
                                <a href="resume.php" class="protected-link h-40 sm:h-48 border-2 border-dashed border-white/20 rounded-2xl flex flex-col items-center justify-center gap-3 hover:border-white/50 transition-colors cursor-pointer group bg-white/5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="group-hover:stroke-white transition-all"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                    <span class="text-gray-300 font-medium text-sm sm:text-base group-hover:text-white transition-colors">Start Building Resume</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Skill Analytics Section -->
            <section class="px-4 sm:px-6 py-8 md:py-12 w-full">
                <div class="max-w-6xl mx-auto ui-card p-6 sm:p-12 lg:p-16 flex flex-col lg:flex-row items-center gap-8 lg:gap-16 rounded-[24px] md:rounded-[32px]">
                    <div class="lg:w-1/2 text-center lg:text-left">
                        <h2 class="text-3xl sm:text-5xl lg:text-6xl font-bold tracking-tight leading-tight mb-4 md:mb-6 text-white">See where you are.</h2>
                        <p class="text-gray-300 text-base md:text-xl leading-relaxed max-w-md mx-auto lg:mx-0 font-medium">
                            Visualize your skillset against actual hiring data. Understand exactly what skills you're missing for your dream role.
                        </p>
                        <div class="mt-8 flex justify-center lg:justify-start">
                            <div class="ui-card-inner p-1 rounded-xl flex" id="analytics-toggle-group">
                                <button id="btn-indepth" onclick="switchAnalyticsView('indepth')" class="bg-white text-black px-5 py-2 rounded-lg text-xs sm:text-sm font-semibold shadow-sm transition-all duration-200">
                                    In-depth
                                </button>
                                <button id="btn-summary" onclick="switchAnalyticsView('summary')" class="px-5 py-2 text-xs sm:text-sm font-medium text-gray-400 hover:text-white transition-colors duration-200">
                                    Summary
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="lg:w-1/2 w-full">
                        <div class="ui-card-inner p-6 sm:p-8 md:p-10 rounded-[20px] md:rounded-[32px]">
                            <!-- IN-DEPTH VIEW -->
                            <div id="view-indepth" class="space-y-6">
                                <div class="space-y-2">
                                    <div class="flex justify-between text-xs sm:text-sm font-semibold text-white">
                                        <span>React / UI Engineering</span>
                                        <span class="text-white">92%</span>
                                    </div>
                                    <div class="h-3.5 bg-white/10 rounded-full overflow-hidden border border-white/20">
                                        <div class="h-full bg-white rounded-full shadow-[0_0_10px_rgba(255,255,255,0.5)]" style="width: 92%"></div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between text-xs sm:text-sm font-semibold text-white">
                                        <span>Backend Systems</span>
                                        <span class="text-white">68%</span>
                                    </div>
                                    <div class="h-3.5 bg-white/10 rounded-full overflow-hidden border border-white/20">
                                        <div class="h-full bg-white/70 rounded-full" style="width: 68%"></div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between text-xs sm:text-sm font-semibold text-white">
                                        <span>Product Strategy</span>
                                        <span class="text-white">45%</span>
                                    </div>
                                    <div class="h-3.5 bg-white/10 rounded-full overflow-hidden border border-white/20">
                                        <div class="h-full bg-white/40 rounded-full" style="width: 45%"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- SUMMARY VIEW -->
                            <div id="view-summary" class="hidden space-y-6">
                                <div class="text-center py-2">
                                    <div class="text-5xl font-bold text-white mb-2">75%</div>
                                    <p class="text-gray-300 text-sm font-medium">Overall Role Match Score</p>
                                </div>
                                <div class="border-t border-white/10 pt-4 space-y-3 text-xs sm:text-sm text-gray-300">
                                    <div class="flex items-center justify-between">
                                        <span class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-green-400"></span> Primary Strength
                                        </span>
                                        <strong class="text-white">Frontend / React</strong>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-yellow-400"></span> Primary Gap
                                        </span>
                                        <strong class="text-white">Product Strategy</strong>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-white"></span> Key Recommendation
                                        </span>
                                        <strong class="text-white">Build 1 System Design Project</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Metrics Cards Section -->
            <section class="px-4 sm:px-6 py-8 md:py-12 w-full">
                <div class="max-w-6xl mx-auto ui-card p-6 sm:p-12 lg:p-16 relative overflow-hidden text-center text-white rounded-[24px] md:rounded-[32px]">
                    <h2 class="text-3xl sm:text-5xl lg:text-6xl font-bold tracking-tight leading-tight mb-8 sm:mb-12">Don't get ghosted.</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="p-6 sm:p-8 rounded-[24px] md:rounded-[32px] ui-card-inner flex flex-col items-center justify-center hover:bg-white/10 transition-colors">
                            <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-full border-8 border-white/10 flex items-center justify-center mb-4 sm:mb-6 relative">
                                <svg class="absolute inset-0 w-full h-full -rotate-90">
                                    <circle cx="50%" cy="50%" r="44%" fill="transparent" stroke="white" stroke-width="8" stroke-dasharray="250" stroke-dashoffset="50" stroke-linecap="round" />
                                </svg>
                                <span class="text-2xl sm:text-3xl font-bold text-white">85%</span>
                            </div>
                            <h3 class="font-semibold text-lg sm:text-xl mb-1">Success rate</h3>
                            <p class="text-gray-400 text-xs sm:text-sm font-medium">Avg. match improvement</p>
                        </div>
                        
                        <div class="p-6 sm:p-8 rounded-[24px] md:rounded-[32px] ui-card-inner flex flex-col items-center justify-center hover:bg-white/10 transition-colors">
                            <div class="p-4 bg-white/10 rounded-2xl mb-4 sm:mb-6 border border-white/10 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            </div>
                            <h3 class="font-bold text-3xl sm:text-4xl mb-1 text-white">2.4m</h3>
                            <p class="text-gray-400 text-xs sm:text-sm font-medium">Avg. analysis time</p>
                        </div>

                        <div class="p-6 sm:p-8 rounded-[24px] md:rounded-[32px] ui-card-inner flex flex-col items-center justify-center hover:bg-white/10 transition-colors">
                            <div class="p-4 bg-white/10 rounded-2xl mb-4 sm:mb-6 border border-white/10 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            </div>
                            <h3 class="font-bold text-3xl sm:text-4xl mb-1 text-white">Secure</h3>
                            <p class="text-gray-400 text-xs sm:text-sm font-medium">Encrypted CV handling</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section class="px-4 sm:px-6 py-12 md:py-24 w-full">
                <div class="max-w-6xl mx-auto">
                    <div class="mb-10 md:mb-16 text-center md:text-left">
                        <h2 class="text-3xl sm:text-5xl md:text-6xl font-bold tracking-tight mb-4 md:mb-6 text-white">Everything You Need <br class="hidden md:inline"><span class="text-gray-400">to Land Your Next Job</span></h2>
                        <p class="text-gray-300 text-base md:text-xl max-w-2xl mx-auto md:mx-0 font-medium">Our comprehensive platform provides all the tools and resources for a successful job search.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                        <div class="ui-card p-6 sm:p-8 md:p-10 flex flex-col items-start group rounded-[24px] md:rounded-[32px]">
                            <div class="w-12 h-12 sm:w-14 sm:h-14 bg-white/10 text-white rounded-2xl flex items-center justify-center mb-6 md:mb-8 shadow-sm group-hover:bg-white group-hover:text-black transition-all duration-300 border border-white/20">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            </div>
                            <h3 class="text-2xl sm:text-3xl font-bold mb-3 text-white">Smart Job Matching</h3>
                            <p class="text-gray-300 text-base sm:text-lg leading-relaxed mb-6 md:mb-8 font-medium">Our AI matches you with jobs that fit your skills and preferences perfectly. No more manual searching.</p>
                            <a href="findjobs.php" class="protected-link mt-auto text-white font-semibold flex items-center gap-2 group-hover:gap-4 transition-all text-sm sm:text-base">
                                Explore Matching 
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                            </a>
                        </div>

                        <div class="ui-card p-6 sm:p-8 md:p-10 flex flex-col items-start group rounded-[24px] md:rounded-[32px]">
                            <div class="w-12 h-12 sm:w-14 sm:h-14 bg-white/10 text-white rounded-2xl flex items-center justify-center mb-6 md:mb-8 shadow-sm group-hover:bg-white group-hover:text-black transition-all duration-300 border border-white/20">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                            </div>
                            <h3 class="text-2xl sm:text-3xl font-bold mb-3 text-white">Dynamic Resume Builder</h3>
                            <p class="text-gray-300 text-base sm:text-lg leading-relaxed mb-6 md:mb-8 font-medium">Create tailored resumes that adapt to each job application automatically. Pass every ATS check with ease.</p>
                            <a href="resume.php" class="protected-link mt-auto text-white font-semibold flex items-center gap-2 group-hover:gap-4 transition-all text-sm sm:text-base">
                                Start Building
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                            </a>
                        </div>

                        <div class="ui-card p-6 sm:p-8 md:p-10 flex flex-col items-start group rounded-[24px] md:rounded-[32px]">
                            <div class="w-12 h-12 sm:w-14 sm:h-14 bg-white/10 text-white rounded-2xl flex items-center justify-center mb-6 md:mb-8 shadow-sm group-hover:bg-white group-hover:text-black transition-all duration-300 border border-white/20">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            </div>
                            <h3 class="text-2xl sm:text-3xl font-bold mb-3 text-white">One-Click Apply</h3>
                            <p class="text-gray-300 text-base sm:text-lg leading-relaxed mb-6 md:mb-8 font-medium">Apply to multiple jobs with one click using your optimized profile. Save hundreds of hours every month.</p>
                            <a href="findjobs.php" class="protected-link mt-auto text-white font-semibold flex items-center gap-2 group-hover:gap-4 transition-all text-sm sm:text-base">
                                Find Jobs
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                            </a>
                        </div>

                        <div class="ui-card p-6 sm:p-8 md:p-10 flex flex-col items-start group rounded-[24px] md:rounded-[32px]">
                            <div class="w-12 h-12 sm:w-14 sm:h-14 bg-white/10 text-white rounded-2xl flex items-center justify-center mb-6 md:mb-8 shadow-sm group-hover:bg-white group-hover:text-black transition-all duration-300 border border-white/20">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            </div>
                            <h3 class="text-2xl sm:text-3xl font-bold mb-3 text-white">Track Progress</h3>
                            <p class="text-gray-300 text-base sm:text-lg leading-relaxed mb-6 md:mb-8 font-medium">Get personalized advice and market insights to advance your career via your Dashboard.</p>
                            <a href="dashboard.php" class="protected-link mt-auto text-white font-semibold flex items-center gap-2 group-hover:gap-4 transition-all text-sm sm:text-base">
                                Go to Dashboard
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Final Call to Action -->
            <section class="py-12 md:py-32 px-4 sm:px-6 text-center w-full">
                <div class="max-w-3xl mx-auto ui-card p-8 md:p-16 rounded-[24px] md:rounded-[32px]">
                    <h2 class="text-3xl sm:text-5xl md:text-6xl font-bold tracking-tight mb-4 md:mb-8 leading-[1.1] text-white">Ready to Transform Your Career?</h2>
                    <p class="text-gray-300 text-base sm:text-xl mb-8 md:mb-10 max-w-xl mx-auto font-medium">Join thousands of professionals who have found their dream jobs through HireFlow.</p>
                    <a href="findjobs.php" class="protected-link px-8 sm:px-10 py-3.5 sm:py-5 bg-white text-black rounded-full font-bold text-base md:text-xl shadow-lg hover:bg-gray-200 hover:scale-105 transition-all active:scale-95 inline-block w-full sm:w-auto">
                        Start Your Journey
                    </a>
                </div>
            </section>

            <!-- Recruiter Onboarding Section -->
            <section class="px-4 sm:px-6 py-12 md:py-16 w-full mb-12">
                <div class="max-w-4xl mx-auto ui-card p-6 sm:p-10 md:p-16 text-center rounded-[24px] md:rounded-[32px]">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 bg-white text-black rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="sm:w-7 sm:h-7"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    </div>
                    <h2 class="text-2xl sm:text-4xl md:text-5xl font-bold tracking-tight mb-4 text-white">Onboard as a Recruiter / Company</h2>
                    <p class="text-gray-300 text-base md:text-xl mb-8 md:mb-10 max-w-xl mx-auto font-medium">Join HireFlow Partners to seamlessly post jobs, analyze ATS scores, and hire top-tier talent effortlessly.</p>
                    <div class="flex flex-col sm:flex-row justify-center gap-4 max-w-xs sm:max-w-none mx-auto w-full">
                        <a href="https://hireflow.fun/Recruiter/login.php" class="px-8 py-3.5 sm:py-4 rounded-full ui-card-inner text-white font-bold text-base sm:text-lg hover:bg-white/10 transition-all active:scale-95 text-center w-full sm:w-auto">
                            Login
                        </a>
                        <a href="https://hireflow.fun/Recruiter/recruiter_signup.php" class="px-8 py-3.5 sm:py-4 rounded-full bg-white text-black font-bold text-base sm:text-lg shadow-md hover:bg-gray-200 transition-all active:scale-95 text-center w-full sm:w-auto">
                            Onboard
                        </a>
                    </div>
                </div>
            </section>

        </div>
    </div>

    <!-- Toggle Analytics View Script -->
    <script>
        function switchAnalyticsView(view) {
            const indepthBtn = document.getElementById('btn-indepth');
            const summaryBtn = document.getElementById('btn-summary');
            const indepthView = document.getElementById('view-indepth');
            const summaryView = document.getElementById('view-summary');

            if (view === 'summary') {
                indepthView.classList.add('hidden');
                summaryView.classList.remove('hidden');

                summaryBtn.className = "bg-white text-black px-5 py-2 rounded-lg text-xs sm:text-sm font-semibold shadow-sm transition-all duration-200";
                indepthBtn.className = "px-5 py-2 text-xs sm:text-sm font-medium text-gray-400 hover:text-white transition-colors duration-200";
            } else {
                summaryView.classList.add('hidden');
                indepthView.classList.remove('hidden');

                indepthBtn.className = "bg-white text-black px-5 py-2 rounded-lg text-xs sm:text-sm font-semibold shadow-sm transition-all duration-200";
                summaryBtn.className = "px-5 py-2 text-xs sm:text-sm font-medium text-gray-400 hover:text-white transition-colors duration-200";
            }
        }
    </script>

<?php include 'footer.php'; ?>