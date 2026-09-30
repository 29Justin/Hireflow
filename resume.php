<?php
session_start();
require_once 'aiconfig.php';
// Optional: Include dbconfig and session checks here if you want to lock it down like the dashboard
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resume Builder - HireFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/markdown-it@13.0.1/dist/markdown-it.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;700&family=Space+Mono&family=Merriweather:wght@300;700&family=Lato:wght@400;700&family=Oswald:wght@500;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            -webkit-font-smoothing: antialiased; 
            -moz-osx-font-smoothing: grayscale;
            margin: 0;
            padding: 0;
            color: #ffffff;
            overflow: hidden;
        }

        /* Sleek Black, Grey, and White Gradient */
        .custom-bg {
            background: linear-gradient(to top right, #000000 10%, #171717 45%, #404040 75%, #a3a3a3 100%);
            position: relative;
            background-attachment: fixed;
        }

        /* Vertical light streaks glass texture overlay */
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

        /* Custom Scrollbars */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.2); border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.4); }

        /* Editor Layout with Dark Glass */
        .editor-container { 
            height: calc(100vh - 80px); 
            overflow-y: auto; 
            background: rgba(20, 20, 20, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        
        .preview-pane { 
            height: calc(100vh - 80px); 
            overflow-y: auto; 
            background: rgba(0, 0, 0, 0.4); 
            display: flex; 
            justify-content: center; 
            padding: 40px; 
        }
        
        .resume-sheet { 
            width: 210mm; 
            min-height: 297mm; 
            background: white; 
            color: black;
            padding: 20mm; 
            box-shadow: 0 20px 50px rgba(0,0,0,0.6); 
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden; 
        }

        /* Dark Mode Inputs */
        .input-field { 
            background-color: rgba(255, 255, 255, 0.05); 
            border: 1px solid rgba(255, 255, 255, 0.1); 
            color: white;
            transition: all 0.2s ease; 
        }
        .input-field:focus { 
            background-color: rgba(255, 255, 255, 0.08); 
            border-color: #ffffff; 
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.2); 
            outline: none; 
        }
        .input-field::placeholder { color: rgba(255, 255, 255, 0.4); }

        /* --- THEMES (Targeting .resume-sheet which is white) --- */
        .theme-modern .res-name { color: #2563eb; font-size: 32px; font-weight: 800; letter-spacing: -0.5px; }
        .theme-modern .res-section { border-bottom: 2px solid #f2f2f7; padding-bottom: 8px; margin-top: 24px; font-weight: 700; color: #1d1d1f; text-transform: uppercase; letter-spacing: 1px; font-size: 14px; }
        .theme-modern .meta-row { color: #86868b; font-size: 13px; font-weight: 500; margin-bottom: 8px; }

        .theme-classic { font-family: 'Playfair Display', serif; }
        .theme-classic .res-name { text-align: center; border-bottom: 1px solid #000; padding-bottom: 15px; font-size: 36px; color: #111; }
        .theme-classic .res-section { text-align: center; border-bottom: 1px solid #eee; margin-top: 25px; font-style: italic; font-size: 18px; color: #444; }
        .theme-classic .header-content { text-align: center; }
        
        .theme-minimal { font-family: 'Space Mono', monospace; color: #333; }
        .theme-minimal .res-name { background: #000; color: #fff; display: inline-block; padding: 4px 12px; font-size: 24px; }
        .theme-minimal .res-section { background: #eee; padding: 4px 10px; margin-top: 24px; font-weight: bold; text-transform: uppercase; font-size: 14px; display: inline-block; }

        .theme-professional { font-family: 'Lato', sans-serif; border-top: 10px solid #1e3a8a; }
        .theme-professional .res-name { color: #1e3a8a; font-size: 38px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; }
        .theme-professional .res-title { color: #7f8c8d; font-weight: 400; letter-spacing: 1px; margin-top: 5px; }
        .theme-professional .res-section { border-left: 4px solid #3b82f6; padding-left: 15px; margin-top: 30px; font-size: 16px; font-weight: 700; text-transform: uppercase; color: #1e3a8a; background: #eff6ff; padding-top: 5px; padding-bottom: 5px; }

        .theme-creative { font-family: 'Lato', sans-serif; }
        .theme-creative .header-box { background: #3b82f6; margin: -20mm -20mm 20px -20mm; padding: 30mm 20mm 20mm 20mm; clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%); }
        .theme-creative .res-name { color: white; font-size: 42px; font-weight: 900; }
        .theme-creative .res-title { color: #eff6ff; font-weight: 500; font-size: 18px; }
        .theme-creative .res-section { color: #3b82f6; border-bottom: 2px dashed #3b82f6; margin-top: 30px; font-size: 18px; font-weight: 800; }

        .theme-executive { font-family: 'Georgia', serif; color: #1e293b; }
        .theme-executive .res-name { text-align: center; text-transform: uppercase; letter-spacing: 4px; font-size: 32px; border-bottom: 3px double #1e293b; padding-bottom: 15px; margin-bottom: 10px; }
        .theme-executive .res-title { text-align: center; font-style: italic; color: #475569; }
        .theme-executive .res-section { text-align: center; text-transform: uppercase; letter-spacing: 2px; font-size: 14px; font-weight: bold; margin-top: 35px; color: #1e293b; display: flex; align-items: center; justify-content: center; }
        .theme-executive .res-section::before, .theme-executive .res-section::after { content: ""; height: 1px; background: #cbd5e0; flex-grow: 1; margin: 0 15px; }

        .theme-swiss { font-family: 'Inter', sans-serif; }
        .theme-swiss .res-name { font-family: 'Oswald', sans-serif; font-size: 50px; text-transform: uppercase; line-height: 1; color: #000; }
        .theme-swiss .res-title { background: #2563eb; color: #fff; display: inline-block; padding: 2px 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; font-size: 14px; margin-top: 10px; }
        .theme-swiss .res-section { font-family: 'Oswald', sans-serif; font-size: 24px; border-top: 4px solid #2563eb; padding-top: 5px; margin-top: 40px; text-transform: uppercase; color: #000; }

        .flex-row-spaced { display: flex; justify-content: space-between; align-items: baseline; }

        @media print {
            body * { visibility: hidden; }
            .preview-pane, .preview-pane * { visibility: visible; }
            .preview-pane { position: absolute; left: 0; top: 0; padding: 0; background: white; width: 100%; height: auto; }
            .resume-sheet { box-shadow: none; margin: 0; width: 100%; min-height: 100vh; }
            .no-print { display: none; }
        }
    </style>
</head>
<body class="custom-bg flex flex-col h-screen w-screen overflow-hidden">

    <!-- Fixed Background Elements -->
    <div class="fixed top-[-5%] right-[10%] w-[50vw] h-[50vw] rounded-full bg-white/10 blur-[140px] pointer-events-none z-0 no-print"></div>
    <div class="fixed bottom-[0%] left-[-10%] w-[60vw] h-[60vw] rounded-full bg-gray-500/20 blur-[130px] pointer-events-none z-0 no-print"></div>
    <div class="glass-streaks no-print"></div>

    <!-- Top Navbar with Dark Glass -->
    <nav class="h-[80px] bg-black/40 border-b border-white/10 backdrop-blur-md px-6 flex items-center justify-between no-print z-50 flex-shrink-0 w-full relative shadow-lg">
        <div class="flex items-center gap-6 overflow-hidden">
            <!-- HireFlow Logo -->
            <div class="text-2xl font-extrabold tracking-tighter text-white cursor-pointer drop-shadow-md" onclick="window.location.href='index.php'">
                HireFlow.
            </div>
            
            <!-- Theme Scroller -->
            <div class="flex gap-2 p-1.5 bg-white/5 rounded-xl overflow-x-auto custom-scrollbar border border-white/10">
                <button onclick="setTheme('theme-modern', this)" class="theme-btn px-4 py-2 text-xs font-bold rounded-lg bg-white shadow-sm text-black transition-all whitespace-nowrap">Modern</button>
                <button onclick="setTheme('theme-classic', this)" class="theme-btn px-4 py-2 text-xs font-bold rounded-lg hover:bg-white/10 text-gray-300 transition-all whitespace-nowrap">Classic</button>
                <button onclick="setTheme('theme-minimal', this)" class="theme-btn px-4 py-2 text-xs font-bold rounded-lg hover:bg-white/10 text-gray-300 transition-all whitespace-nowrap">Minimal</button>
                <button onclick="setTheme('theme-professional', this)" class="theme-btn px-4 py-2 text-xs font-bold rounded-lg hover:bg-white/10 text-gray-300 transition-all whitespace-nowrap">Pro</button>
                <div class="w-[1px] bg-white/20 mx-1"></div>
                <button onclick="setTheme('theme-creative', this)" class="theme-btn px-4 py-2 text-xs font-bold rounded-lg hover:bg-white/10 text-gray-300 transition-all whitespace-nowrap">Creative</button>
                <button onclick="setTheme('theme-executive', this)" class="theme-btn px-4 py-2 text-xs font-bold rounded-lg hover:bg-white/10 text-gray-300 transition-all whitespace-nowrap font-serif">Executive</button>
                <button onclick="setTheme('theme-swiss', this)" class="theme-btn px-4 py-2 text-xs font-bold rounded-lg hover:bg-white/10 text-gray-300 transition-all whitespace-nowrap font-sans uppercase">Swiss</button>
            </div>
        </div>
        <div class="flex gap-3 flex-shrink-0">
            <button onclick="openAIModal()" class="px-5 py-2.5 bg-white text-black rounded-full text-sm font-bold shadow-lg hover:bg-gray-200 transition-all active:scale-95 flex items-center gap-2">
                AI Summary
            </button>
            <button onclick="window.print()" class="hidden sm:flex px-5 py-2.5 bg-white/10 text-white border border-white/20 rounded-full text-sm font-bold shadow-sm hover:bg-white/20 transition-all items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download PDF
            </button>
        </div>
    </nav>

    <!-- Editor & Preview Area -->
    <div class="flex flex-col md:flex-row flex-grow overflow-hidden no-print relative z-10 w-full">
        
        <!-- Left Panel: Editor with Dark Grey Glass -->
        <div class="w-full md:w-[40%] editor-container p-6 md:p-8 custom-scrollbar border-r border-white/10 z-10 flex flex-col space-y-10">
            
            <div>
                <h2 class="text-xl font-bold mb-5 text-white flex items-center gap-3">
                    <span class="w-1.5 h-6 bg-white rounded-full shadow-[0_0_10px_rgba(255,255,255,0.8)]"></span>
                    Personal Details
                </h2>
                <div class="space-y-4">
                    <input id="inp-name" type="text" oninput="updatePreview('name', this.value)" placeholder="Full Name" class="input-field w-full p-4 rounded-2xl text-sm font-medium">
                    <input id="inp-title" type="text" oninput="updatePreview('title', this.value)" placeholder="Current Job Title" class="input-field w-full p-4 rounded-2xl text-sm font-medium">
                </div>
            </div>

            <div>
                <h2 class="text-xl font-bold mb-5 text-white flex items-center gap-3">
                    <span class="w-1.5 h-6 bg-white rounded-full shadow-[0_0_10px_rgba(255,255,255,0.8)]"></span>
                    Professional Summary
                </h2>
                <textarea id="inp-summary" oninput="updatePreview('summary', this.value)" rows="5" placeholder="Brief summary of your career (Use AI to generate this)..." class="input-field w-full p-4 rounded-2xl text-sm font-medium resize-none"></textarea>
            </div>

            <div>
                <h2 class="text-xl font-bold mb-5 text-white flex items-center gap-3">
                    <span class="w-1.5 h-6 bg-white rounded-full shadow-[0_0_10px_rgba(255,255,255,0.8)]"></span>
                    Work Experience
                </h2>
                <div class="space-y-4">
                    <input id="inp-exp-role" type="text" oninput="updateExperience()" placeholder="Role (e.g. Senior Engineer)" class="input-field w-full p-4 rounded-2xl text-sm font-medium">
                    <input id="inp-exp-comp" type="text" oninput="updateExperience()" placeholder="Company Name" class="input-field w-full p-4 rounded-2xl text-sm font-medium">
                    
                    <div class="grid grid-cols-2 gap-3">
                        <input id="inp-exp-start" type="text" oninput="updateExperience()" placeholder="Start Date" class="input-field p-4 rounded-2xl text-sm font-medium">
                        <input id="inp-exp-end" type="text" oninput="updateExperience()" placeholder="End Date" class="input-field p-4 rounded-2xl text-sm font-medium">
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <select id="inp-exp-type" oninput="updateExperience()" class="input-field p-4 rounded-2xl text-xs font-bold appearance-none cursor-pointer">
                            <option value="Full-time" class="bg-[#141414] text-white">Full-time</option>
                            <option value="Part-time" class="bg-[#141414] text-white">Part-time</option>
                            <option value="Internship" class="bg-[#141414] text-white">Internship</option>
                            <option value="Contract" class="bg-[#141414] text-white">Contract</option>
                        </select>
                        <select id="inp-exp-mode" oninput="updateExperience()" class="input-field p-4 rounded-2xl text-xs font-bold appearance-none cursor-pointer">
                            <option value="Onsite" class="bg-[#141414] text-white">Onsite</option>
                            <option value="Remote" class="bg-[#141414] text-white">Remote</option>
                            <option value="Hybrid" class="bg-[#141414] text-white">Hybrid</option>
                        </select>
                        <input id="inp-exp-loc" type="text" oninput="updateExperience()" placeholder="City, Country" class="input-field p-4 rounded-2xl text-xs font-medium">
                    </div>

                    <textarea id="inp-exp-desc" oninput="updateExperience()" rows="5" placeholder="Describe your responsibilities..." class="input-field w-full p-4 rounded-2xl text-sm font-medium resize-none"></textarea>
                </div>
            </div>

            <div>
                <h2 class="text-xl font-bold mb-5 text-white flex items-center gap-3">
                    <span class="w-1.5 h-6 bg-white rounded-full shadow-[0_0_10px_rgba(255,255,255,0.8)]"></span>
                    Education
                </h2>
                <div class="space-y-4">
                    <input id="inp-edu-degree" type="text" oninput="updateEducation()" placeholder="Degree" class="input-field w-full p-4 rounded-2xl text-sm font-medium">
                    <div class="grid grid-cols-2 gap-3">
                        <input id="inp-edu-school" type="text" oninput="updateEducation()" placeholder="University/School" class="input-field p-4 rounded-2xl text-sm font-medium">
                        <input id="inp-edu-year" type="text" oninput="updateEducation()" placeholder="Year" class="input-field p-4 rounded-2xl text-sm font-medium">
                    </div>
                </div>
            </div>

            <div class="pb-10">
                <h2 class="text-xl font-bold mb-5 text-white flex items-center gap-3">
                    <span class="w-1.5 h-6 bg-white rounded-full shadow-[0_0_10px_rgba(255,255,255,0.8)]"></span>
                    Skills & Certs
                </h2>
                <div class="space-y-6">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Skills</label>
                        <input id="inp-skills" type="text" oninput="updatePreview('skills', this.value)" placeholder="React, Node.js, AWS" class="input-field w-full p-4 rounded-2xl text-sm font-medium">
                    </div>
                    
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Certification</label>
                        <div class="grid grid-cols-2 gap-3">
                            <input id="inp-cert-name" type="text" oninput="updateCert()" placeholder="Cert Name" class="input-field p-4 rounded-2xl text-sm font-medium">
                            <input id="inp-cert-issuer" type="text" oninput="updateCert()" placeholder="Issuer" class="input-field p-4 rounded-2xl text-sm font-medium">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel: Resume Preview Sheet -->
        <div class="w-full md:w-[60%] preview-pane custom-scrollbar">
            <div id="resume-sheet" class="resume-sheet theme-modern">
                <div class="header-box">
                    <div class="mb-10 header-content">
                        <h1 id="prev-name" class="res-name">Your Name</h1>
                        <p id="prev-title" class="res-title text-lg font-medium">Professional Title</p>
                    </div>
                </div>

                <div class="res-section">Profile</div>
                <p id="prev-summary" class="text-gray-700 leading-relaxed text-[15px] mt-3 whitespace-pre-wrap">Your professional summary will appear here...</p>

                <div class="res-section">Experience</div>
                <div class="mt-4">
                    <div class="flex-row-spaced">
                        <h3 id="prev-exp-role" class="font-bold text-lg">Job Role</h3>
                        <span id="prev-exp-dates" class="text-sm font-medium text-gray-500 whitespace-nowrap">Jan 2023 - Present</span>
                    </div>
                    <div class="meta-row mt-1 text-gray-600 text-sm">
                        <span id="prev-exp-comp" class="font-bold">Company</span> 
                        <span id="prev-exp-meta">| Location • Full-time • Onsite</span>
                    </div>
                    <p id="prev-exp-desc" class="text-gray-700 leading-relaxed text-[15px] mt-3 whitespace-pre-wrap">Your experience details will appear here...</p>
                </div>

                <div class="res-section">Education</div>
                <div class="mt-4">
                    <div class="flex-row-spaced">
                        <h3 id="prev-edu-degree" class="font-bold text-lg">Degree Name</h3>
                        <span id="prev-edu-year" class="text-sm font-medium text-gray-500">Year</span>
                    </div>
                    <p id="prev-edu-school" class="text-gray-600">University Name</p>
                </div>

                <div class="res-section">Certifications</div>
                <div class="mt-4 flex gap-4 text-gray-700" id="cert-container">
                    <div class="bg-gray-50 border border-gray-200 px-4 py-2 rounded-lg">
                        <span id="prev-cert-name" class="font-bold block text-gray-900">Cert Name</span>
                        <span id="prev-cert-issuer" class="text-xs text-gray-500">Issuer</span>
                    </div>
                </div>

                <div class="res-section">Core Competencies</div>
                <div id="prev-skills" class="mt-4 flex flex-wrap gap-2 text-gray-700">
                    <span class="text-gray-400 italic">No skills added yet.</span>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Generator Modal with Monochrome Styling -->
    <div id="ai-modal-overlay" class="fixed inset-0 bg-black/80 backdrop-blur-md z-[100] hidden flex items-center justify-center p-4">
        <div class="w-full max-w-2xl rounded-[32px] shadow-2xl overflow-hidden flex flex-col max-h-[90vh] border border-white/10 bg-[#141414]/95 backdrop-blur-xl text-white">
            <div class="bg-white/5 p-8 flex-shrink-0 border-b border-white/10">
                <h3 class="text-2xl font-extrabold text-white tracking-tight"> AI Summary Generator</h3>
                <p class="text-gray-400 text-sm mt-2 font-medium">Paste a Job Description (JD) to generate a tailored professional summary.</p>
            </div>
            
            <div class="p-8 overflow-y-auto flex-grow custom-scrollbar bg-black/20">
                <div id="ai-step-input">
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Target Job Description</label>
                    <textarea id="jd-input" rows="8" class="input-field w-full p-5 rounded-2xl text-sm font-mono resize-none focus:border-white focus:ring-1 focus:ring-white/50" placeholder="Paste JD here..."></textarea>
                </div>

                <div id="processing-state" class="hidden py-12 flex flex-col items-center justify-center text-white">
                    <svg class="animate-spin h-10 w-10 mb-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="font-extrabold text-base tracking-wide text-gray-300">AI is writing your summary...</span>
                </div>

                <div id="ai-step-review" class="hidden space-y-6">
                    <div class="bg-white/5 border border-white/10 p-5 rounded-2xl">
                        <h4 class="text-sm font-bold text-gray-300 mb-3 uppercase tracking-wider">Generated Professional Summary</h4>
                        <div id="review-desc" class="text-sm text-gray-200 bg-black/20 p-4 rounded-xl border border-white/5 font-medium leading-relaxed"></div>
                    </div>

                    <div class="bg-white/5 border border-white/10 p-5 rounded-2xl">
                        <h4 class="text-sm font-bold text-gray-300 mb-3 uppercase tracking-wider">Suggested Missing Skills</h4>
                        <div id="review-skills" class="text-sm text-gray-200 bg-black/20 p-4 rounded-xl border border-white/5 font-medium"></div>
                    </div>
                </div>
            </div>

            <div class="p-6 border-t border-white/10 flex justify-end gap-4 bg-white/5 flex-shrink-0">
                <button onclick="closeAIModal()" class="px-6 py-3 text-sm font-bold text-gray-400 hover:text-white transition-colors cursor-pointer">Cancel</button>
                
                <button id="btn-analyze" onclick="analyzeJD()" class="px-8 py-3 bg-white text-black text-sm font-bold rounded-full shadow-lg hover:bg-gray-200 transition-all active:scale-95 cursor-pointer">
                    Generate Summary
                </button>

                <button id="btn-accept" onclick="acceptChanges()" class="hidden px-8 py-3 bg-white text-black text-sm font-bold rounded-full shadow-lg hover:bg-gray-200 transition-all active:scale-95 flex items-center gap-2 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    Confirm & Add
                </button>
            </div>
        </div>
    </div>

    <script>
        // CONFIG - Pulling dynamically from aiconfig.php
        const GROQ_API_KEY = "<?php echo GROQ_API_KEY; ?>"; 
        const GROQ_URL = "https://api.groq.com/openai/v1/chat/completions";
        const MODEL_ID = "llama-3.3-70b-versatile"; 

        // --- Core Functions ---
        function updatePreview(id, value) {
            const element = document.getElementById('prev-' + id);
            if (id === 'skills') {
                if(!value) {
                    element.innerHTML = '<span class="text-gray-400 italic">No skills added yet.</span>';
                    return;
                }
                const skillArr = value.split(',');
                element.innerHTML = skillArr.map(s => {
                    const trimmed = s.trim();
                    return trimmed ? `<span class="bg-gray-100 text-black px-3 py-1 rounded-md text-sm border border-gray-300 font-semibold">${trimmed}</span>` : '';
                }).join(' ');
            } else {
                element.innerText = value || '';
            }
        }

        function updateExperience() {
            document.getElementById('prev-exp-role').innerText = document.getElementById('inp-exp-role').value;
            document.getElementById('prev-exp-comp').innerText = document.getElementById('inp-exp-comp').value;
            document.getElementById('prev-exp-dates').innerText = document.getElementById('inp-exp-start').value + " - " + document.getElementById('inp-exp-end').value;
            document.getElementById('prev-exp-meta').innerText = ` | ${document.getElementById('inp-exp-loc').value} • ${document.getElementById('inp-exp-type').value} • ${document.getElementById('inp-exp-mode').value}`;
            document.getElementById('prev-exp-desc').innerText = document.getElementById('inp-exp-desc').value;
        }

        function updateEducation() {
            document.getElementById('prev-edu-degree').innerText = document.getElementById('inp-edu-degree').value;
            document.getElementById('prev-edu-school').innerText = document.getElementById('inp-edu-school').value;
            document.getElementById('prev-edu-year').innerText = document.getElementById('inp-edu-year').value;
        }

        function updateCert() {
            document.getElementById('prev-cert-name').innerText = document.getElementById('inp-cert-name').value;
            document.getElementById('prev-cert-issuer').innerText = document.getElementById('inp-cert-issuer').value;
        }

        function setTheme(themeName, btnElement) {
            const sheet = document.getElementById('resume-sheet');
            sheet.className = 'resume-sheet ' + themeName;
            
            if(btnElement) {
                document.querySelectorAll('.theme-btn').forEach(btn => {
                    btn.className = 'theme-btn px-4 py-2 text-xs font-bold rounded-lg hover:bg-white/10 text-gray-300 transition-all whitespace-nowrap';
                    if (btn.innerText === 'Executive') btn.classList.add('font-serif');
                    if (btn.innerText === 'Swiss') btn.classList.add('font-sans', 'uppercase');
                });
                
                btnElement.className = 'theme-btn px-4 py-2 text-xs font-bold rounded-lg bg-white shadow-sm text-black transition-all whitespace-nowrap';
                if (btnElement.innerText === 'Executive') btnElement.classList.add('font-serif');
                if (btnElement.innerText === 'Swiss') btnElement.classList.add('font-sans', 'uppercase');
            }
        }

        // --- AI Logic ---
        let pendingChanges = null;

        function openAIModal() { 
            document.getElementById('ai-modal-overlay').classList.remove('hidden'); 
            document.getElementById('ai-step-input').classList.remove('hidden');
            document.getElementById('ai-step-review').classList.add('hidden');
            document.getElementById('btn-analyze').classList.remove('hidden');
            document.getElementById('btn-accept').classList.add('hidden');
            document.getElementById('jd-input').value = '';
        }

        function closeAIModal() { 
            document.getElementById('ai-modal-overlay').classList.add('hidden'); 
            document.getElementById('processing-state').classList.add('hidden');
        }

        async function analyzeJD() {
            const jd = document.getElementById('jd-input').value;
            const currentTitle = document.getElementById('inp-title').value;

            if(!jd) return alert("Please paste a JD.");
            
            document.getElementById('ai-step-input').classList.add('hidden');
            document.getElementById('processing-state').classList.remove('hidden');
            document.getElementById('btn-analyze').classList.add('hidden');

            const prompt = `
                You are a Resume Optimizer. 
                1. Write a professional Summary (3-4 sentences) tailored to the Job Description (JD) and my title.
                2. Suggest 3-5 missing hard skills based on the JD.

                My Current Title: ${currentTitle || "Professional"}
                Job Description: ${jd}

                Return ONLY valid JSON (no markdown):
                {
                    "professional_summary": "string",
                    "missing_skills": "string (comma separated)"
                }
            `;

            try {
                const response = await fetch(GROQ_URL, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${GROQ_API_KEY}`
                    },
                    body: JSON.stringify({
                        model: MODEL_ID,
                        messages: [{ role: "user", content: prompt }],
                        temperature: 0.5,
                        response_format: { type: "json_object" }
                    })
                });

                if (!response.ok) {
                    const err = await response.json();
                    throw new Error(err.error?.message || response.statusText);
                }

                const data = await response.json();
                let rawContent = data.choices[0].message.content;
                
                rawContent = rawContent.replace(/```json/g, "").replace(/```/g, "").trim();
                const start = rawContent.indexOf('{');
                const end = rawContent.lastIndexOf('}');
                if(start > -1 && end > -1) rawContent = rawContent.substring(start, end+1);

                const result = JSON.parse(rawContent);

                pendingChanges = {
                    summary: result.professional_summary,
                    skills: result.missing_skills
                };

                document.getElementById('review-desc').innerText = pendingChanges.summary;
                document.getElementById('review-skills').innerText = pendingChanges.skills;

                document.getElementById('processing-state').classList.add('hidden');
                document.getElementById('ai-step-review').classList.remove('hidden');
                document.getElementById('btn-accept').classList.remove('hidden');

            } catch (error) {
                console.error(error);
                alert("AI Error: " + error.message);
                closeAIModal();
            }
        }

        function acceptChanges() {
            if(!pendingChanges) return;

            document.getElementById('inp-summary').value = pendingChanges.summary;
            updatePreview('summary', pendingChanges.summary);
            
            const oldSkills = document.getElementById('inp-skills').value;
            const finalSkills = oldSkills ? oldSkills + ", " + pendingChanges.skills : pendingChanges.skills;
            document.getElementById('inp-skills').value = finalSkills;
            updatePreview('skills', finalSkills);

            closeAIModal();
        }
    </script>
</body>
</html>