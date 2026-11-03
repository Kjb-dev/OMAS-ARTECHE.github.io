<?php
require_once 'db.php';

if (!isset($_SESSION['staff_logged_in']) || $_SESSION['staff_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$csrf_token = function_exists('generateCSRFToken') ? generateCSRFToken() : ($_SESSION['csrf_token'] ?? '');

$arteche_barangays = [
    "Aguinaldo",
    "Balud (Poblacion)",
    "Bato (San Luis)",
    "Beri",
    "Bigo",
    "Buenavista",
    "Cagsalay",
    "Campacion",
    "Carapdapan",
    "Casidman",
    "Catumsan",
    "Central (Poblacion)",
    "Concepcion",
    "Garden (Poblacion)",
    "Inayawan",
    "Macarthur",
    "Rawis (Poblacion)",
    "Tangbo",
    "Tawagan",
    "Tebalawon"
];
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HVC Farmer Registry | OMAS LGU Arteche</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased min-h-screen flex flex-col">

    <!-- Top Navigation Header -->
    <header class="bg-slate-900 text-white sticky top-0 z-40 border-b border-slate-800 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="dashboard.php" class="text-slate-400 hover:text-white transition mr-2">
                    <i class="fa-solid fa-arrow-left text-lg"></i>
                </a>
                <img src="image/bungto_han_arteche.png" alt="LGU Arteche" class="w-9 h-9 object-cover rounded-full bg-white p-0.5" onerror="this.src='https://via.placeholder.com/36'">
                <span class="text-lg font-black tracking-wide">OMAS ARTECHE <span class="text-xs font-normal text-emerald-400">| High-Value Crops (HVC) Registry Form</span></span>
            </div>
            <a href="dashboard.php" class="bg-slate-800 hover:bg-slate-700 text-xs px-3 py-2 rounded-xl border border-slate-700 transition font-semibold">
                Back to Dashboard
            </a>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full space-y-6">
      <!-- HVC Excel Import Card -->
<div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-sm font-bold text-slate-800">Batch Import HVC Masterlist (.xlsx / .csv)</h3>
            <p class="text-xs text-slate-500">Upload the official High-Value Crops farmers masterlist spreadsheet.</p>
        </div>
        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
            <i class="fa-solid fa-file-excel"></i>
        </div>
    </div>

    <form id="hvcUploadForm" class="flex flex-col sm:flex-row items-center gap-4">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="action" value="upload_hvc_farmers_excel">
        <div class="w-full flex-1">
            <input type="file" name="excel_file" id="hvcFileField" accept=".xlsx, .xls, .csv" required 
                   class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-slate-200 rounded-xl cursor-pointer bg-slate-50">
        </div>
        <button type="submit" id="hvcUploadBtn" class="w-full sm:w-auto px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 shadow-md flex items-center justify-center gap-2 shrink-0 transition-all">
            <i class="fa-solid fa-cloud-arrow-up"></i>
            <span>Upload & Import Masterlist</span>
        </button>
    </form>
    <div id="uploadStatusMsg" class="mt-3 text-xs hidden"></div>
</div>
        <!-- INDIVIDUAL REGISTRATION FORM SECTION -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-sm">
            <div class="border-b border-slate-100 pb-4 mb-6">
                <h1 class="text-xl font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-seedling text-emerald-600"></i> High-Value Crops Farmer Data Entry
                </h1>
                <p class="text-xs text-slate-500 mt-1">Record primary farmer demographics, equipment, and commodity yield metrics (Vegetables, Root Crops, Fruits, Coconut, Banana).</p>
            </div>

            <div id="formAlert" class="hidden p-4 mb-6 rounded-2xl text-xs font-semibold whitespace-pre-wrap"></div>

            <form id="hvcFarmerForm" class="space-y-8">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="save_hvc">

                <!-- SECTION 1: PRIMARY LOCATION & RSBSA IDENTIFICATION -->
                <div>
                    <h2 class="text-xs font-black uppercase text-emerald-700 tracking-wider mb-4 border-b pb-1">1. Primary Location & RSBSA Details</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Barangay</label>
                            <select name="barangay" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                                <option value="">-- Select Barangay --</option>
                                <?php foreach ($arteche_barangays as $bg): ?>
                                    <option value="<?= htmlspecialchars($bg, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($bg, ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">RSBSA Reference Number</label>
                            <input type="text" name="rsbsa_no" required placeholder="e.g. 08-26-01-0001" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: FARMER PERSONAL DETAILS -->
                <div>
                    <h2 class="text-xs font-black uppercase text-emerald-700 tracking-wider mb-4 border-b pb-1">2. Farmer Personal Profile</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Last Name</label>
                            <input type="text" name="last_name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">First Name</label>
                            <input type="text" name="first_name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Middle Name</label>
                            <input type="text" name="middle_name" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Birthdate</label>
                            <input type="date" name="birthdate" required id="birthdate" onchange="calculateAge(this.value)" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Age</label>
                            <input type="number" name="age" id="age" readonly required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm bg-slate-200 text-slate-600 font-bold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Civil Status</label>
                            <select name="civil_status" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Widowed">Widowed</option>
                                <option value="Separated">Separated</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tenurial Status</label>
                            <select name="tenurial_status" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                                <option value="Owner">Owner</option>
                                <option value="Tenant">Tenant</option>
                                <option value="Lessee">Lessee</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: HOUSEHOLD PROFILE -->
                <div>
                    <h2 class="text-xs font-black uppercase text-emerald-700 tracking-wider mb-4 border-b pb-1">3. Household Demographics</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. of Household Members</label>
                            <input type="number" name="no_household_members" value="1" min="1" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. of Children (If Married)</label>
                            <input type="number" name="no_children" value="0" min="0" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                    </div>
                </div>

                <!-- SECTION 4: VEGETABLES PRODUCTION -->
                <div class="bg-amber-50/50 p-4 sm:p-5 rounded-2xl border border-amber-200/80 space-y-4">
                    <h2 class="text-xs font-black uppercase text-amber-800 tracking-wider flex items-center gap-2 border-b border-amber-200 pb-2">
                        <i class="fa-solid fa-carrot"></i> 4. Vegetables Commodity Details
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Vegetable Area (Has)</label>
                            <input type="number" step="0.01" name="veg_area_has" value="0.00" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Name of Crops</label>
                            <input type="text" name="veg_name_of_crops" placeholder="e.g. Eggplant, Ampalaya, Tomato" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. of Hills Planted</label>
                            <input type="number" name="veg_no_of_hills" value="0" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ecosystem</label>
                            <select name="veg_ecosystem" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                                <option value="I">I - Irrigated</option>
                                <option value="R">R - Rainfed</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Production / Cropping (MT)</label>
                            <input type="number" step="0.01" name="veg_prod_per_cropping_mt" value="0.00" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Annual Frequency of Production</label>
                            <input type="number" name="veg_annual_frequency" value="1" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Owned Machinery / Equipment</label>
                            <input type="text" name="veg_machinery_operated" placeholder="e.g. Knapsack Sprayer, Tiller" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                    </div>
                </div>

                <!-- SECTION 5: ROOTCROPS PRODUCTION -->
                <div class="bg-emerald-50/50 p-4 sm:p-5 rounded-2xl border border-emerald-200/80 space-y-4">
                    <h2 class="text-xs font-black uppercase text-emerald-800 tracking-wider flex items-center gap-2 border-b border-emerald-200 pb-2">
                        <i class="fa-solid fa-plant-wilt"></i> 5. Rootcrops Commodity Details
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Rootcrops Area (Has)</label>
                            <input type="number" step="0.01" name="root_area_has" value="0.00" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Name of Crops</label>
                            <input type="text" name="root_name_of_crops" placeholder="e.g. Cassava, Sweet Potato, Gabi" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. of Hills Planted</label>
                            <input type="number" name="root_no_of_hills" value="0" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ecosystem</label>
                            <select name="root_ecosystem" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                                <option value="I">I - Irrigated</option>
                                <option value="R" selected>R - Rainfed</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Prod / Cropping (MT)</label>
                            <input type="number" step="0.01" name="root_prod_per_cropping_mt" value="0.00" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Annual Freq. of Planting</label>
                            <input type="number" name="root_annual_frequency" value="1" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Annual Prod. (MT)</label>
                            <input type="number" step="0.01" name="root_annual_production_mt" value="0.00" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Owned Equipment</label>
                            <input type="text" name="root_machinery_operated" placeholder="e.g. Cassava Grater" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                    </div>
                </div>

                <!-- SECTION 6: FRUIT TREES PRODUCTION -->
                <div class="bg-indigo-50/50 p-4 sm:p-5 rounded-2xl border border-indigo-200/80 space-y-4">
                    <h2 class="text-xs font-black uppercase text-indigo-800 tracking-wider flex items-center gap-2 border-b border-indigo-200 pb-2">
                        <i class="fa-solid fa-apple-whole"></i> 6. Fruit Trees Commodity Details
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Fruit Trees Area (Has)</label>
                            <input type="number" step="0.01" name="fruit_area_has" value="0.00" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Name of Fruit Trees</label>
                            <input type="text" name="fruit_name_of_trees" placeholder="e.g. Mango, Calamansi, Lanzones" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. of Hills Planted</label>
                            <input type="number" name="fruit_no_of_hills" value="0" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ecosystem</label>
                            <select name="fruit_ecosystem" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                                <option value="I">I - Irrigated</option>
                                <option value="R" selected>R - Rainfed</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Prod / Bearing (MT)</label>
                            <input type="number" step="0.01" name="fruit_prod_per_bearing_mt" value="0.00" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Annual Freq. Bearing</label>
                            <input type="number" name="fruit_annual_frequency" value="1" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Annual Prod. (MT)</label>
                            <input type="number" step="0.01" name="fruit_annual_production_mt" value="0.00" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Owned Equipment</label>
                            <input type="text" name="fruit_machinery_operated" placeholder="e.g. Pruning Shears" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                    </div>
                </div>

                <!-- SECTION 7: COCONUT PRODUCTION -->
                <div class="bg-blue-50/50 p-4 sm:p-5 rounded-2xl border border-blue-200/80 space-y-4">
                    <h2 class="text-xs font-black uppercase text-blue-800 tracking-wider flex items-center gap-2 border-b border-blue-200 pb-2">
                        <i class="fa-solid fa-tree"></i> 7. Coconut Commodity Details
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Coconut Trees Area (Has)</label>
                            <input type="number" step="0.01" name="coco_area_has" value="0.00" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. of Coconut Trees</label>
                            <input type="number" name="coco_no_of_trees" value="0" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Annual Freq. of Production</label>
                            <input type="number" name="coco_annual_frequency" value="1" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                    </div>
                </div>

                <!-- SECTION 8: BANANA PRODUCTION -->
                <div class="bg-yellow-50/50 p-4 sm:p-5 rounded-2xl border border-yellow-200/80 space-y-4">
                    <h2 class="text-xs font-black uppercase text-yellow-800 tracking-wider flex items-center gap-2 border-b border-yellow-200 pb-2">
                        <i class="fa-solid fa-cubes-stacked"></i> 8. Banana Commodity Details
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Banana Area (Has)</label>
                            <input type="number" step="0.01" name="banana_area_has" value="0.00" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Variety of Banana</label>
                            <input type="text" name="banana_variety" placeholder="e.g. Saba, Lakatan, Latundan" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. of Hills Planted</label>
                            <input type="number" name="banana_no_of_hills" value="0" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ecosystem</label>
                            <select name="banana_ecosystem" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                                <option value="I">I - Irrigated</option>
                                <option value="R" selected>R - Rainfed</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Annual Freq. of Production</label>
                            <input type="number" name="banana_annual_frequency" value="1" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Annual Prod. (Metric Tons)</label>
                            <input type="number" step="0.01" name="banana_annual_production_mt" value="0.00" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Owned Equipment</label>
                            <input type="text" name="banana_machinery_operated" placeholder="e.g. Transport Cart" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                    </div>
                </div>

                <!-- REMARKS -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Field Masterlist Remarks</label>
                    <textarea name="remarks" rows="2" placeholder="Write optional survey field notes..." class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50"></textarea>
                </div>

                <!-- Form Controls -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="dashboard.php" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md flex items-center gap-2">
                        <i class="fa-solid fa-save"></i>
                        <span>Save HVC Farmer Profile</span>
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        function calculateAge(birthdateStr) {
            if (!birthdateStr) return;
            const birthDate = new Date(birthdateStr);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            document.getElementById('age').value = age > 0 ? age : 0;
        }

        // Bulk Upload Form Handler
        document.getElementById('hvcUploadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const statusMsg = document.getElementById('uploadStatusMsg');

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
                statusMsg.classList.remove('hidden', 'bg-red-50', 'text-red-700', 'bg-emerald-50', 'text-emerald-700', 'p-3', 'rounded-xl');
                statusMsg.classList.add('p-3', 'rounded-xl');
                if (data.success) {
                    statusMsg.classList.add('bg-emerald-50', 'text-emerald-700');
                    statusMsg.innerText = data.message || "Masterlist imported successfully.";
                    document.getElementById('hvcUploadForm').reset();
                } else {
                    statusMsg.classList.add('bg-red-50', 'text-red-700');
                    statusMsg.innerText = data.message || "An error occurred during import.";
                }
            })
            .catch(err => {
                statusMsg.classList.remove('hidden', 'bg-emerald-50', 'text-emerald-700');
                statusMsg.classList.add('bg-red-50', 'text-red-700', 'p-3', 'rounded-xl');
                statusMsg.innerText = err.message;
            });
        });

        // Individual Farmer Entry Form Handler
        document.getElementById('hvcFarmerForm').addEventListener('submit', function(e) {
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
                const alertBox = document.getElementById('formAlert');
                alertBox.classList.remove('hidden', 'bg-red-50', 'text-red-700', 'bg-emerald-50', 'text-emerald-700');
                if (data.success) {
                    alertBox.classList.add('bg-emerald-50', 'text-emerald-700');
                    alertBox.innerText = data.message || "HVC Farmer profile saved successfully.";
                    document.getElementById('hvcFarmerForm').reset();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                } else {
                    alertBox.classList.add('bg-red-50', 'text-red-700');
                    alertBox.innerText = data.message || "An error occurred while saving.";
                }
            })
            .catch(err => {
                const alertBox = document.getElementById('formAlert');
                alertBox.classList.remove('hidden');
                alertBox.classList.add('bg-red-50', 'text-red-700');
                alertBox.innerText = err.message;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
    </script>
</body>
</html>