<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMAS-Arteche | Staff Back-Office Portal</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased selection:bg-emerald-500 selection:text-white">

    <!-- Header & Navigation -->
    <header class="bg-white/90 backdrop-blur-md shadow-sm sticky top-0 z-50 transition-all border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            
            <!-- Logos & Brand Title -->
            <a href="index.php" class="flex items-center space-x-3">
                <div class="flex items-center -space-x-2">
                    <!-- DA Logo -->
                    <div class="w-11 h-11 rounded-full bg-white flex items-center justify-center border-2 border-slate-100 overflow-hidden shadow-sm z-10" title="Department of Agriculture">
                        <img src="image/department-of-agriculture-Logo.png" alt="DA Logo" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="w-full h-full bg-emerald-100 text-emerald-700 items-center justify-center text-xs font-bold" style="display:none;">
                            <i class="fa-solid fa-seedling"></i>
                        </div>
                    </div>
                    
                    <!-- LGU Arteche Logo -->
                    <div class="w-11 h-11 rounded-full bg-white flex items-center justify-center border-2 border-slate-100 overflow-hidden shadow-sm" title="LGU Arteche">
                        <img src="image/bungto_han_arteche.png" alt="LGU Arteche Logo" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="w-full h-full bg-blue-100 text-blue-700 items-center justify-center text-xs font-bold" style="display:none;">
                            <i class="fa-solid fa-landmark"></i>
                        </div>
                    </div>
                </div>
                <div>
                    <span class="text-xl font-black tracking-wide text-emerald-800 block leading-none">OMAS</span>
                    <span class="text-[11px] font-black tracking-wider uppercase">
                        <span style="color: #8CC63F;">A</span><span style="color: #ED1C24;">r</span><span style="color: #55ACEE;">t</span><span style="color: #FF7300;">e</span><span style="color: #ce005d;">c</span><span style="color: #1115ff;">h</span><span style="color: #8CC63F;">e</span>, 
                        <span class="text-slate-500 font-semibold">Eastern Samar</span>
                    </span>
                </div>
            </a>

            <!-- Navigation Links -->
            <nav class="hidden md:flex items-center space-x-8 font-medium text-sm">
                <a href="#commodities" class="text-slate-600 hover:text-emerald-600 transition">Commodity Databases</a>
                <a href="#features" class="text-slate-600 hover:text-emerald-600 transition">Staff Tools</a>
                <a href="#about" class="text-slate-600 hover:text-emerald-600 transition">About System</a>
            </nav>

          
                <a href="register.php" class="hidden sm:inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md shadow-emerald-600/20 transition hover:-translate-y-0.5">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Admin Register</span>
                </a>
                
                <button id="menu-toggle" class="md:hidden p-2 text-slate-600 hover:text-emerald-600 focus:outline-none" aria-label="Toggle Menu">
                    <i class="fa-solid fa-bars text-xl" id="menu-icon"></i>
                </button>
            </div>

        </div>

        <!-- Mobile Nav Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-white border-b border-slate-200 px-4 pt-2 pb-6 space-y-3">
            <a href="#commodities" class="block py-2 text-slate-700 font-medium hover:text-emerald-600">Commodity Databases</a>
            <a href="#features" class="block py-2 text-slate-700 font-medium hover:text-emerald-600">Staff Tools</a>
            <a href="#about" class="block py-2 text-slate-700 font-medium hover:text-emerald-600">About System</a>
            <a href="login.php" class="block py-2 text-slate-700 font-medium hover:text-emerald-600">Admin Sign In</a>
            <div class="pt-2">
                <a href="register.php" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Enroll Farmer Data</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative bg-slate-900 text-white py-20 lg:py-28 px-4 sm:px-6 lg:px-8 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-950 via-emerald-900 to-slate-900 opacity-95"></div>
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl"></div>

        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12 items-center relative z-10">
            
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <span class="inline-flex items-center gap-2 bg-amber-500/10 text-amber-300 text-xs font-semibold px-3.5 py-1.5 rounded-full border border-amber-500/30">
                    <i class="fa-solid fa-lock text-amber-400"></i> Internal Administrative Access Only
                </span>
                
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight">
                    Office of the Municipal Agricultural Services System -
                    <span class="inline-block whitespace-nowrap">
                        <span style="color: #8CC63F;">A</span><span style="color: #ED1C24;">R</span><span style="color: #55ACEE;">T</span><span style="color: #FF7300;">E</span><span style="color: #ce0064;">C</span><span style="color: #0a19be;">H</span><span style="color: #8CC63F;">E</span>
                    </span>
                </h1>

                <p class="text-emerald-100/80 text-base sm:text-lg max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                    Internal management system restricted to Office of the Municipal Agriculturist staff for RSBSA farmer encoding, profile verification, commodity database updates, and municipal yield monitoring.
                </p>
                <div class="flex flex-col sm:flex-row justify-center lg:justify-start gap-4 pt-2">
                    <a href="login.php" class="px-7 py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold shadow-lg shadow-amber-500/20 transition hover:-translate-y-0.5 text-center flex items-center justify-center gap-2">
                        <i class="fa-solid fa-right-to-bracket"></i> Staff Login
                    </a>
                   
                </div>
            </div>

            <div class="lg:col-span-5 glass-card p-6 sm:p-8 rounded-3xl shadow-2xl space-y-6">
                <div class="flex items-center justify-between border-b border-white/10 pb-4">
                    <div>
                        <h2 class="text-sm font-bold tracking-wider text-emerald-300 uppercase">Administrative Summary</h2>
                        <p class="text-xs text-slate-400">OMAS Data Analytics</p>
                    </div>
                    <span class="flex h-3 w-3 relative">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-slate-900/50 p-4 rounded-2xl border border-white/5">
                        <div class="text-emerald-400 mb-1"><i class="fa-solid fa-users text-lg"></i></div>
                        <p class="text-xs text-slate-400 font-medium">Encoded Farmers</p>
                        <p class="text-2xl font-black mt-1 text-white">1,420+</p>
                    </div>
                    <div class="bg-slate-900/50 p-4 rounded-2xl border border-white/5">
                        <div class="text-amber-400 mb-1"><i class="fa-solid fa-wheat-awn text-lg"></i></div>
                        <p class="text-xs text-slate-400 font-medium">Recorded Yield</p>
                        <p class="text-2xl font-black mt-1 text-white">8,450 MT</p>
                    </div>
                </div>

                <div class="bg-emerald-950/60 p-4 rounded-2xl border border-emerald-500/20 text-xs text-emerald-200/90 flex items-start gap-3">
                    <div class="p-2 bg-amber-500/20 rounded-lg text-amber-400 mt-0.5">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <p class="leading-relaxed">
                        <strong class="text-white block mb-0.5">Authorized Personnel Portal</strong>
                        Staff account credentials required to modify records, approve subsidies, or export statistical reports.
                    </p>
                </div>
            </div>

        </div>
    </section>

    <!-- Commodities Section -->
    <section id="commodities" class="py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto space-y-4 mb-16">
            <span class="text-xs font-bold text-emerald-600 bg-emerald-100 px-3 py-1 rounded-full uppercase tracking-wider">Internal Registries</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900">Commodity Database Management</h2>
            <p class="text-slate-600">Direct back-office access to manage crop records, seed distributions, and fishery registrations across Arteche.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <div class="group bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 hover:shadow-xl hover:border-emerald-500/50 transition-all duration-300 relative overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="w-14 h-14 bg-emerald-100 text-emerald-700 rounded-2xl flex items-center justify-center text-2xl mb-5">
                        <i class="fa-solid fa-bowl-rice"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Rice Registry</h3>
                    <p class="text-xs text-slate-500 leading-relaxed mb-6">
                        Irrigated and rainfed palay records, input subsidy logs, and yield reporting.
                    </p>
                </div>
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md">620 Records</span>
                    <a href="login.php" class="text-xs font-bold text-slate-700 group-hover:text-emerald-600 flex items-center gap-1">
                        Manage <i class="fa-solid fa-lock text-[10px]"></i>
                    </a>
                </div>
            </div>

            <div class="group bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 hover:shadow-xl hover:border-amber-500/50 transition-all duration-300 relative overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="w-14 h-14 bg-amber-100 text-amber-700 rounded-2xl flex items-center justify-center text-2xl mb-5">
                        <i class="fa-solid fa-plant-wilt"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Corn &amp; Cassava</h3>
                    <p class="text-xs text-slate-500 leading-relaxed mb-6">
                        Yellow/white corn and root crop area data and farmer profiles.
                    </p>
                </div>
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-semibold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-md">310 Records</span>
                    <a href="login.php" class="text-xs font-bold text-slate-700 group-hover:text-amber-600 flex items-center gap-1">
                        Manage <i class="fa-solid fa-lock text-[10px]"></i>
                    </a>
                </div>
            </div>

            <div class="group bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 hover:shadow-xl hover:border-purple-500/50 transition-all duration-300 relative overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="w-14 h-14 bg-purple-100 text-purple-700 rounded-2xl flex items-center justify-center text-2xl mb-5">
                        <i class="fa-solid fa-carrot"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">HVCDP Registry</h3>
                    <p class="text-xs text-slate-500 leading-relaxed mb-6">
                        High Value Crops tracking including cacao, fruit trees, and commercial vegetables.
                    </p>
                </div>
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-semibold text-purple-700 bg-purple-50 px-2.5 py-1 rounded-md">280 Records</span>
                    <a href="login.php" class="text-xs font-bold text-slate-700 group-hover:text-purple-600 flex items-center gap-1">
                        Manage <i class="fa-solid fa-lock text-[10px]"></i>
                    </a>
                </div>
            </div>

            <div class="group bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 hover:shadow-xl hover:border-blue-500/50 transition-all duration-300 relative overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="w-14 h-14 bg-blue-100 text-blue-700 rounded-2xl flex items-center justify-center text-2xl mb-5">
                        <i class="fa-solid fa-fish"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Fishery Registry</h3>
                    <p class="text-xs text-slate-500 leading-relaxed mb-6">
                        Fisherfolk licensing, motorized boat verification, and aquaculture catch logs.
                    </p>
                </div>
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-semibold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-md">210 Records</span>
                    <a href="login.php" class="text-xs font-bold text-slate-700 group-hover:text-blue-600 flex items-center gap-1">
                        Manage <i class="fa-solid fa-lock text-[10px]"></i>
                    </a>
                </div>
            </div>

        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-slate-100/70 border-y border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto space-y-4 mb-16">
                <h2 class="text-3xl font-extrabold text-slate-900">Admin &amp; Staff Capabilities</h2>
                <p class="text-slate-600">Built to streamline municipal field encoding, RSBSA verification, and official report generation.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200 space-y-4">
                    <div class="w-12 h-12 bg-emerald-100 text-emerald-700 rounded-xl flex items-center justify-center text-xl">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900">Farmer Encoding</h3>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Input offline RSBSA enrollment sheets directly into the centralized system database.
                    </p>
                </div>

                <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200 space-y-4">
                    <div class="w-12 h-12 bg-blue-100 text-blue-700 rounded-xl flex items-center justify-center text-xl">
                        <i class="fa-solid fa-file-invoice"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900">Assistance Tracking</h3>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Record distributed fertilizer vouchers, seed allocations, and disaster relief aid per farmer.
                    </p>
                </div>

                <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200 space-y-4">
                    <div class="w-12 h-12 bg-amber-100 text-amber-700 rounded-xl flex items-center justify-center text-xl">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900">LGU &amp; DA Reporting</h3>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Export monthly yield metrics and barangay production summaries for DA Region VIII submissions.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 py-12 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-full bg-emerald-800 text-white flex items-center justify-center text-xs font-bold">
                    <i class="fa-solid fa-seedling"></i>
                </div>
                <span class="text-white font-semibold text-sm">OMAS-Arteche Internal Staff Portal</span>
            </div>
            <p class="text-xs text-slate-500 text-center sm:text-right">
                &copy; 2026 Local Government Unit of Arteche, Eastern Samar. Authorized personnel only.
            </p>
        </div>
    </footer>

    <!-- Mobile Menu Script -->
    <script>
        const menuToggle = document.getElementById('menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');
        const menuIcon = document.getElementById('menu-icon');

        menuToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
            if (mobileMenu.classList.contains('hidden')) {
                menuIcon.classList.remove('fa-xmark');
                menuIcon.classList.add('fa-bars');
            } else {
                menuIcon.classList.remove('fa-bars');
                menuIcon.classList.add('fa-xmark');
            }
        });
    </script>
</body>
</html>