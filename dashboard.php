<?php
require_once 'db.php';

// Authentication Guard
if (!isset($_SESSION['staff_logged_in']) || $_SESSION['staff_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$csrf_token = function_exists('generateCSRFToken') ? generateCSRFToken() : ($_SESSION['csrf_token'] ?? '');
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | OMAS LGU Arteche</title>
    <!-- Tailwind CSS & FontAwesome -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased min-h-screen flex flex-col">

    <!-- Top Navigation Bar -->
    <header class="bg-slate-900 text-white sticky top-0 z-40 border-b border-slate-800 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <img src="image/bungto_han_arteche.png" alt="LGU Arteche" class="w-9 h-9 object-cover rounded-full bg-white p-0.5" onerror="this.src='https://via.placeholder.com/36'">
                <span class="text-lg font-black tracking-wide">OMAS ARTECHE <span class="text-xs font-normal text-emerald-400">| Management Dashboard</span></span>
            </div>

            <div class="flex items-center gap-3">
                <button onclick="openProfileModal()" class="flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-xs px-3 py-2 rounded-xl border border-slate-700 transition">
                    <i class="fa-solid fa-user-gear text-amber-400"></i>
                    <span class="hidden sm:inline font-semibold"><?= htmlspecialchars($_SESSION['staff_name'] ?? 'Staff', ENT_QUOTES, 'UTF-8') ?></span>
                </button>
                <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white text-xs px-3 py-2 rounded-xl font-bold transition flex items-center gap-1.5">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span class="hidden sm:inline">Logout</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Dashboard Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full space-y-8">
        
        <!-- Action Toolbar -->
        <section class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col lg:flex-row items-center justify-between gap-6">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900">Registered Farmer & Yield Management</h1>
                <p class="text-xs text-slate-500 mt-1">Select commodity form, view registered records, or submit monthly yield data to database.</p>
            </div>
            
            <div class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto">
                <!-- VIEW REGISTERED FARMERS DROPDOWN BUTTON -->
                <div class="relative w-full sm:w-auto">
                    <select onchange="navigateToFarmerList(this.value)" class="w-full sm:w-56 px-3.5 py-2.5 rounded-xl border border-blue-200 bg-blue-50/50 text-xs font-bold text-blue-900 focus:ring-2 focus:ring-blue-500 focus:outline-none transition cursor-pointer">
                        <option value="">📋 VIEW REGISTERED FARMERS</option>
                        <option value="rice_farmers_list.php">Rice Farmers Registry</option>
                        <option value="hvc_farmers_list.php">HVC Farmers Registry</option>
                        <option value="corn_cassava_farmers_list.php">Corn & Cassava Registry</option>
                        <option value="livestock_farmers_list.php">Livestock Farmers Registry</option>
                        <option value="fishery_farmers_list.php">Fishery Registry</option>
                    </select>
                </div>

                <!-- ADD NEW REGISTRATION FORM -->
                <div class="relative w-full sm:w-auto">
                    <select id="farmer_type" onchange="navigateToFarmerForm(this.value)" class="w-full sm:w-52 px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:outline-none transition cursor-pointer">
                        <option value="">➕ Register New Farmer</option>
                        <option value="rice_farmer.php">Rice Farmer Form</option>
                        <option value="hvc_farmer.php">HVC Farmer Form</option>
                        <option value="corn_cassava_farmer.php">Corn & Cassava Form</option>
                        <option value="livestock_farmer.php">Livestock Farmer Form</option>
                        <option value="fishery_farmer.php">Fishery Registry Form</option>
                    </select>
                </div>

                <!-- INPUT MONTHLY REPORT BUTTON -->
                <button onclick="openReportModal()" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-plus-circle text-sm"></i>
                    <span>Input Monthly Report</span>
                </button>
            </div>
        </section>

        <!-- 5 Commodities Yield Cards Overview -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-emerald-700 uppercase">Rice</span>
                    <i class="fa-solid fa-bowl-rice text-emerald-500 text-lg"></i>
                </div>
                <p class="text-2xl font-black text-slate-900" id="total-rice">0 MT</p>
                <p class="text-[11px] text-slate-400 mt-1">Yearly Yield Production</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-amber-700 uppercase">Corn & Cassava</span>
                    <i class="fa-solid fa-plant-wilt text-amber-500 text-lg"></i>
                </div>
                <p class="text-2xl font-black text-slate-900" id="total-corn_cassava">0 MT</p>
                <p class="text-[11px] text-slate-400 mt-1">Yearly Yield Production</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-purple-700 uppercase">HVC</span>
                    <i class="fa-solid fa-carrot text-purple-500 text-lg"></i>
                </div>
                <p class="text-2xl font-black text-slate-900" id="total-hvc">0 MT</p>
                <p class="text-[11px] text-slate-400 mt-1">Yearly Yield Production</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-blue-700 uppercase">Fishery</span>
                    <i class="fa-solid fa-fish text-blue-500 text-lg"></i>
                </div>
                <p class="text-2xl font-black text-slate-900" id="total-fishery">0 MT</p>
                <p class="text-[11px] text-slate-400 mt-1">Yearly Catch Metric</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-rose-700 uppercase">Livestock</span>
                    <i class="fa-solid fa-cow text-rose-500 text-lg"></i>
                </div>
                <p class="text-2xl font-black text-slate-900" id="total-livestock">0 MT</p>
                <p class="text-[11px] text-slate-400 mt-1">Livestock Output Metric</p>
            </div>
        </section>

        <!-- REGISTERED FARMERS & TOTAL AREA COVERAGE SECTION (COMPACT TWO-COLUMN GRID) -->
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            
            <!-- 1. Registered Farmers Distribution Card -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-1">
                        <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Registered Farmers Distribution</h2>
                        <span class="text-xs bg-indigo-50 text-indigo-700 border border-indigo-200 px-3 py-1 rounded-full font-bold flex items-center gap-1.5 self-start sm:self-auto">
                            <i class="fa-solid fa-users text-indigo-500"></i>
                            <span>Total Registered: <strong id="grand-total-farmers">0</strong></span>
                        </span>
                    </div>
                    <p class="text-xs text-slate-500">Individual farmer records stored across masterlists.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <div class="flex items-center justify-between p-2.5 bg-emerald-50/60 border border-emerald-100 rounded-xl">
                        <span class="text-xs font-bold text-emerald-800 flex items-center gap-2">
                            <i class="fa-solid fa-bowl-rice text-emerald-600"></i> Rice Farmers
                        </span>
                        <span class="text-sm font-black text-slate-900" id="farmer-count-rice">0</span>
                    </div>

                    <div class="flex items-center justify-between p-2.5 bg-amber-50/60 border border-amber-100 rounded-xl">
                        <span class="text-xs font-bold text-amber-800 flex items-center gap-2">
                            <i class="fa-solid fa-plant-wilt text-amber-600"></i> Corn & Cassava
                        </span>
                        <span class="text-sm font-black text-slate-900" id="farmer-count-corn-cassava">0</span>
                    </div>

                    <div class="flex items-center justify-between p-2.5 bg-purple-50/60 border border-purple-100 rounded-xl sm:col-span-2">
                        <span class="text-xs font-bold text-purple-800 flex items-center gap-2">
                            <i class="fa-solid fa-carrot text-purple-600"></i> High Value Crop (HVC) Farmers
                        </span>
                        <span class="text-sm font-black text-slate-900" id="farmer-count-hvc">0</span>
                    </div>

                    <div class="flex items-center justify-between p-2.5 bg-blue-50/60 border border-blue-100 rounded-xl">
                        <span class="text-xs font-bold text-blue-800 flex items-center gap-2">
                            <i class="fa-solid fa-fish text-blue-600"></i> Fishery Registrants
                        </span>
                        <span class="text-sm font-black text-slate-900" id="farmer-count-fishery">0</span>
                    </div>

                    <div class="flex items-center justify-between p-2.5 bg-rose-50/60 border border-rose-100 rounded-xl">
                        <span class="text-xs font-bold text-rose-800 flex items-center gap-2">
                            <i class="fa-solid fa-cow text-rose-600"></i> Livestock Farmers
                        </span>
                        <span class="text-sm font-black text-slate-900" id="farmer-count-livestock">0</span>
                    </div>
                </div>
            </div>

            <!-- 2. Total Commodity Area Card -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Total Commodity Area (Has)</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Aggregated land area registered per commodity.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <div class="flex items-center justify-between p-2.5 bg-emerald-50/60 border border-emerald-100 rounded-xl">
                        <span class="text-xs font-bold text-emerald-800 flex items-center gap-2">
                            <i class="fa-solid fa-bowl-rice text-emerald-600"></i> Rice Area
                        </span>
                        <span class="text-sm font-black text-slate-900" id="area-rice">0.00 Has</span>
                    </div>

                    <div class="flex items-center justify-between p-2.5 bg-purple-50/60 border border-purple-100 rounded-xl">
                        <span class="text-xs font-bold text-purple-800 flex items-center gap-2">
                            <i class="fa-solid fa-carrot text-purple-600"></i> Vegetables
                        </span>
                        <span class="text-sm font-black text-slate-900" id="area-vegetable">0.00 Has</span>
                    </div>

                    <div class="flex items-center justify-between p-2.5 bg-amber-50/60 border border-amber-100 rounded-xl">
                        <span class="text-xs font-bold text-amber-800 flex items-center gap-2">
                            <i class="fa-solid fa-seedling text-amber-600"></i> Rootcrops
                        </span>
                        <span class="text-sm font-black text-slate-900" id="area-rootcrops">0.00 Has</span>
                    </div>

                    <div class="flex items-center justify-between p-2.5 bg-lime-50/60 border border-lime-100 rounded-xl">
                        <span class="text-xs font-bold text-lime-800 flex items-center gap-2">
                            <i class="fa-solid fa-apple-whole text-lime-600"></i> Fruit Trees
                        </span>
                        <span class="text-sm font-black text-slate-900" id="area-fruit-trees">0.00 Has</span>
                    </div>

                    <div class="flex items-center justify-between p-2.5 bg-teal-50/60 border border-teal-100 rounded-xl">
                        <span class="text-xs font-bold text-teal-800 flex items-center gap-2">
                            <i class="fa-solid fa-tree text-teal-600"></i> Coconut Area
                        </span>
                        <span class="text-sm font-black text-slate-900" id="area-coconut">0.00 Has</span>
                    </div>

                    <div class="flex items-center justify-between p-2.5 bg-yellow-50/60 border border-yellow-100 rounded-xl">
                        <span class="text-xs font-bold text-yellow-800 flex items-center gap-2">
                            <i class="fa-solid fa-leaf text-yellow-600"></i> Banana Area
                        </span>
                        <span class="text-sm font-black text-slate-900" id="area-banana">0.00 Has</span>
                    </div>

                    <div class="flex items-center justify-between p-2.5 bg-yellow-50/60 border border-yellow-100 rounded-xl">
                        <span class="text-xs font-bold text-yellow-800 flex items-center gap-2">
                            <i class="fa-solid fa-wheat-awn text-yellow-600"></i> Corn Area
                        </span>
                        <span class="text-sm font-black text-slate-900" id="area-corn">0.00 Has</span>
                    </div>

                    <div class="flex items-center justify-between p-2.5 bg-orange-50/60 border border-orange-100 rounded-xl">
                        <span class="text-xs font-bold text-orange-800 flex items-center gap-2">
                            <i class="fa-solid fa-plant-wilt text-orange-600"></i> Cassava Area
                        </span>
                        <span class="text-sm font-black text-slate-900" id="area-cassava">0.00 Has</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Charts Section (Monthly Yields & Distribution) -->
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Monthly Yield Output per Commodity (2026)</h2>
                    <span class="text-xs bg-emerald-100 text-emerald-800 px-2.5 py-1 rounded-full font-bold">Real-time DB</span>
                </div>
                <div class="h-72 relative">
                    <canvas id="monthlyYieldChart"></canvas>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Total Production Ratio (Metric Tons)</h2>
                    <span class="text-xs bg-blue-100 text-blue-800 px-2.5 py-1 rounded-full font-bold">LGU Summary</span>
                </div>
                <div class="h-72 relative flex items-center justify-center">
                    <canvas id="commodityDistributionChart"></canvas>
                </div>
            </div>
        </section>

    </main>

    <!-- Modal: Input Monthly Report -->
    <div id="reportModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-900 text-base"><i class="fa-solid fa-chart-line text-emerald-600 mr-2"></i>Input Commodity Report</h3>
                <button onclick="closeReportModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <div id="reportAlert" class="hidden p-3 rounded-xl text-xs font-semibold whitespace-pre-wrap break-words"></div>

            <form id="reportForm" class="space-y-3">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="save_monthly_report">

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Commodity Type</label>
                    <select name="commodity_type" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        <option value="rice">Rice</option>
                        <option value="corn_cassava">Corn & Cassava</option>
                        <option value="hvc">High Value Crops (HVC)</option>
                        <option value="fishery">Fishery</option>
                        <option value="livestock">Livestock</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Month</label>
                        <select name="report_month" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                            <option value="1">January</option>
                            <option value="2">February</option>
                            <option value="3">March</option>
                            <option value="4">April</option>
                            <option value="5">May</option>
                            <option value="6">June</option>
                            <option value="7">July</option>
                            <option value="8" selected>August</option>
                            <option value="9">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Year</label>
                        <input type="number" name="report_year" value="2026" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Yield Output (Metric Tons)</label>
                    <input type="number" step="0.01" name="yield_metric_tons" placeholder="e.g. 520.50" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Active Farmers Count</label>
                    <input type="number" name="registered_farmers_count" placeholder="e.g. 620" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                </div>

                <div class="flex justify-end gap-2 pt-3">
                    <button type="button" onclick="closeReportModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md">Save Report</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Staff Settings Profile Modal -->
    <div id="profileModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-900 text-base"><i class="fa-solid fa-user-gear text-emerald-600 mr-2"></i>Staff Profile Settings</h3>
                <button onclick="closeProfileModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <div id="modalAlert" class="hidden p-3 rounded-xl text-xs font-semibold whitespace-pre-wrap break-words"></div>

            <form id="profileForm" class="space-y-3">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="update_profile">

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Full Name</label>
                    <input type="text" name="full_name" required value="<?= htmlspecialchars($_SESSION['staff_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email Address</label>
                    <input type="email" name="email" required value="<?= htmlspecialchars($_SESSION['staff_email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                </div>

                <div class="pt-2 border-t border-slate-100">
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">New Password (Optional)</label>
                    <input type="password" name="new_password" placeholder="Leave blank to keep current" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                </div>

                <div class="flex justify-end gap-2 pt-3">
                    <button type="button" onclick="closeProfileModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript Logic -->
    <script>
        let yieldChart, distributionChart;

        function navigateToFarmerForm(targetUrl) {
            if (targetUrl !== "") window.location.href = targetUrl;
        }

        function navigateToFarmerList(targetUrl) {
            if (targetUrl !== "") window.location.href = targetUrl;
        }

        function openReportModal() { document.getElementById('reportModal').classList.remove('hidden'); }
        function closeReportModal() {
            document.getElementById('reportModal').classList.add('hidden');
            document.getElementById('reportAlert').classList.add('hidden');
        }

        function openProfileModal() { document.getElementById('profileModal').classList.remove('hidden'); }
        function closeProfileModal() {
            document.getElementById('profileModal').classList.add('hidden');
            document.getElementById('modalAlert').classList.add('hidden');
        }

        // Submit Monthly Report via AJAX
        document.getElementById('reportForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('api_dashboard.php', { method: 'POST', body: formData })
            .then(async res => {
                const text = await res.text();
                try {
                    return JSON.parse(text);
                } catch (err) {
                    throw new Error("SERVER RESPONSE ERROR:\n" + text);
                }
            })
            .then(data => {
                const alertBox = document.getElementById('reportAlert');
                alertBox.classList.remove('hidden', 'bg-red-50', 'text-red-700', 'bg-emerald-50', 'text-emerald-700');
                if (data.success) {
                    alertBox.classList.add('bg-emerald-50', 'text-emerald-700');
                    alertBox.innerText = data.message;
                    fetchData();
                    setTimeout(() => { closeReportModal(); document.getElementById('reportForm').reset(); }, 1200);
                } else {
                    alertBox.classList.add('bg-red-50', 'text-red-700');
                    alertBox.innerText = data.message || "Failed to record report.";
                }
            })
            .catch(err => {
                console.error("AJAX Error:", err);
                const alertBox = document.getElementById('reportAlert');
                alertBox.classList.remove('hidden');
                alertBox.classList.add('bg-red-50', 'text-red-700');
                alertBox.innerText = err.message;
            });
        });

        // Submit Profile Changes
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('api_dashboard.php', { method: 'POST', body: formData })
            .then(async res => {
                const text = await res.text();
                try {
                    return JSON.parse(text);
                } catch (err) {
                    throw new Error("SERVER RESPONSE ERROR:\n" + text);
                }
            })
            .then(data => {
                const alertBox = document.getElementById('modalAlert');
                alertBox.classList.remove('hidden', 'bg-red-50', 'text-red-700', 'bg-emerald-50', 'text-emerald-700');
                if (data.success) {
                    alertBox.classList.add('bg-emerald-50', 'text-emerald-700');
                    alertBox.innerText = data.message;
                    setTimeout(() => location.reload(), 1200);
                } else {
                    alertBox.classList.add('bg-red-50', 'text-red-700');
                    alertBox.innerText = data.message;
                }
            })
            .catch(err => {
                const alertBox = document.getElementById('modalAlert');
                alertBox.classList.remove('hidden');
                alertBox.classList.add('bg-red-50', 'text-red-700');
                alertBox.innerText = err.message;
            });
        });

        // Initialize Charts
        function initCharts() {
            const ctxBar = document.getElementById('monthlyYieldChart').getContext('2d');
            const ctxPie = document.getElementById('commodityDistributionChart').getContext('2d');

            // 1. Monthly Yield Chart
            yieldChart = new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [
                        { label: 'Rice', data: [], backgroundColor: '#10b981' },
                        { label: 'Corn & Cassava', data: [], backgroundColor: '#f59e0b' },
                        { label: 'HVC', data: [], backgroundColor: '#a855f7' },
                        { label: 'Fishery', data: [], backgroundColor: '#3b82f6' },
                        { label: 'Livestock', data: [], backgroundColor: '#f43f5e' }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });

            // 2. Commodity Distribution Doughnut Chart
            distributionChart = new Chart(ctxPie, {
                type: 'doughnut',
                data: {
                    labels: ['Rice', 'Corn & Cassava', 'HVC', 'Fishery', 'Livestock'],
                    datasets: [{
                        data: [0, 0, 0, 0, 0],
                        backgroundColor: ['#10b981', '#f59e0b', '#a855f7', '#3b82f6', '#f43f5e']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });

            fetchData();
        }

        function fetchData() {
            fetch('api_dashboard.php?action=get_dashboard_data&year=2026')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Monthly Yield Chart Update
                        yieldChart.data.labels = data.months;
                        yieldChart.data.datasets[0].data = data.series.rice;
                        yieldChart.data.datasets[1].data = data.series.corn_cassava;
                        yieldChart.data.datasets[2].data = data.series.hvc;
                        yieldChart.data.datasets[3].data = data.series.fishery;
                        yieldChart.data.datasets[4].data = data.series.livestock;
                        yieldChart.update();

                        // Yield Ratio Chart Update
                        distributionChart.data.datasets[0].data = [
                            data.totals.rice,
                            data.totals.corn_cassava,
                            data.totals.hvc,
                            data.totals.fishery,
                            data.totals.livestock
                        ];
                        distributionChart.update();

                        // Registered Farmers Count Updates
                        if (data.registered_farmers) {
                            const riceFarmers = data.registered_farmers.rice || 0;
                            const cornCassavaFarmers = data.registered_farmers.corn_cassava || 0;
                            const hvcFarmers = data.registered_farmers.hvc || 0;
                            const fisheryFarmers = data.registered_farmers.fishery || 0;
                            const livestockFarmers = data.registered_farmers.livestock || 0;

                            document.getElementById('farmer-count-rice').innerText = riceFarmers.toLocaleString();
                            document.getElementById('farmer-count-corn-cassava').innerText = cornCassavaFarmers.toLocaleString();
                            document.getElementById('farmer-count-hvc').innerText = hvcFarmers.toLocaleString();
                            document.getElementById('farmer-count-fishery').innerText = fisheryFarmers.toLocaleString();
                            document.getElementById('farmer-count-livestock').innerText = livestockFarmers.toLocaleString();

                            const totalFarmers = riceFarmers + cornCassavaFarmers + hvcFarmers + fisheryFarmers + livestockFarmers;
                            document.getElementById('grand-total-farmers').innerText = totalFarmers.toLocaleString();
                        }

                        // Total Area Updates (Has)
                        if (data.total_areas) {
                            document.getElementById('area-rice').innerText = (data.total_areas.rice || 0).toLocaleString(undefined, {minimumFractionDigits: 2}) + ' Has';
                            document.getElementById('area-vegetable').innerText = (data.total_areas.vegetable || 0).toLocaleString(undefined, {minimumFractionDigits: 2}) + ' Has';
                            document.getElementById('area-rootcrops').innerText = (data.total_areas.rootcrops || 0).toLocaleString(undefined, {minimumFractionDigits: 2}) + ' Has';
                            document.getElementById('area-fruit-trees').innerText = (data.total_areas.fruit_trees || 0).toLocaleString(undefined, {minimumFractionDigits: 2}) + ' Has';
                            document.getElementById('area-coconut').innerText = (data.total_areas.coconut_trees || 0).toLocaleString(undefined, {minimumFractionDigits: 2}) + ' Has';
                            document.getElementById('area-banana').innerText = (data.total_areas.banana || 0).toLocaleString(undefined, {minimumFractionDigits: 2}) + ' Has';
                            document.getElementById('area-corn').innerText = (data.total_areas.corn || 0).toLocaleString(undefined, {minimumFractionDigits: 2}) + ' Has';
                            document.getElementById('area-cassava').innerText = (data.total_areas.cassava || 0).toLocaleString(undefined, {minimumFractionDigits: 2}) + ' Has';
                        }

                        // Yield Totals Display
                        document.getElementById('total-rice').innerText = data.totals.rice.toLocaleString() + ' MT';
                        document.getElementById('total-corn_cassava').innerText = data.totals.corn_cassava.toLocaleString() + ' MT';
                        document.getElementById('total-hvc').innerText = data.totals.hvc.toLocaleString() + ' MT';
                        document.getElementById('total-fishery').innerText = data.totals.fishery.toLocaleString() + ' MT';
                        document.getElementById('total-livestock').innerText = data.totals.livestock.toLocaleString() + ' MT';
                    }
                })
                .catch(err => console.error("Data Fetch Error:", err));
        }

        document.addEventListener('DOMContentLoaded', initCharts);
    </script>
</body>
</html>