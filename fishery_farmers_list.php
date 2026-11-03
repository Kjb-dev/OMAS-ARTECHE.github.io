<?php
/**
 * Fishery & Fisherfolk Registry & Masterlist
 * Office of Agricultural Services (OMAS) - Arteche, Eastern Samar
 */

// 1. Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Session Guard
if (!isset($_SESSION['staff_logged_in']) || $_SESSION['staff_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// 3. Include Database Connection
require_once __DIR__ . '/db.php';

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

// 4. Handle Pagination & Search Filters
$limit = 10; // Records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$search_query = trim($_GET['search'] ?? '');
$filter_barangay = trim($_GET['barangay'] ?? '');

// Build Query Condition
$whereClauses = [];
$params = [];

if ($search_query !== '') {
    $whereClauses[] = "(first_name LIKE ? OR last_name LIKE ? OR rsbsa_number LIKE ? OR registration_number LIKE ?)";
    $searchTerm = "%{$search_query}%";
    array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
}

if ($filter_barangay !== '') {
    $whereClauses[] = "barangay = ?";
    $params[] = $filter_barangay;
}

$whereSQL = count($whereClauses) > 0 ? "WHERE " . implode(" AND ", $whereClauses) : "";

// Fetch Total Records for Pagination
try {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM fishery_farmer {$whereSQL}");
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetchColumn();
    $totalPages = ceil($totalRecords / $limit);
} catch (PDOException $e) {
    $totalRecords = 0;
    $totalPages = 1;
}

// Fetch Paginated Records
$fisheryFarmers = [];
try {
    $sql = "SELECT * FROM fishery_farmer {$whereSQL} ORDER BY id DESC LIMIT {$limit} OFFSET {$offset}";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $fisheryFarmers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fishery & Fisherfolk Registry | OMAS LGU Arteche</title>
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
                <span class="text-lg font-black tracking-wide">OMAS ARTECHE <span class="text-xs font-normal text-cyan-400">| Fishery & Fisherfolk Registry</span></span>
            </div>
            <a href="dashboard.php" class="bg-slate-800 hover:bg-slate-700 text-xs px-3 py-2 rounded-xl border border-slate-700 transition font-semibold">
                Back to Dashboard
            </a>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full space-y-8">

        <!-- INDIVIDUAL REGISTRATION / UPDATE FORM CONTAINER (HIDDEN BY DEFAULT UNLESS EDITING) -->
        <div id="registrationFormContainer" class="hidden bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-sm scroll-mt-24">
            <div class="border-b border-slate-100 pb-4 mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-extrabold text-slate-900 flex items-center gap-2" id="formTitle">
                        <i class="fa-solid fa-fish text-cyan-600"></i> Individual Fisherfolk Registration Entry
                    </h1>
                    <p class="text-xs text-slate-500 mt-1">Record registration numbers, gear details, vessel statistics, and catch yield by marine category.</p>
                </div>
                <button type="button" id="resetFormBtn" onclick="resetToRegisterMode()" class="text-xs bg-slate-200 hover:bg-slate-300 text-slate-700 px-3 py-1.5 rounded-xl font-bold transition">
                    <i class="fa-solid fa-xmark mr-1"></i> Cancel Edit
                </button>
            </div>

            <div id="formAlert" class="hidden p-4 mb-6 rounded-2xl text-xs font-semibold whitespace-pre-wrap"></div>

            <form id="fisheryFarmerForm" class="space-y-8">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" id="formAction" value="save_fishery_farmer">
                <input type="hidden" name="id" id="recordId" value="">

                <!-- SECTION 1: REGISTRATION & IDENTIFICATION -->
                <div>
                    <h2 class="text-xs font-black uppercase text-cyan-700 tracking-wider mb-4 border-b pb-1">1. Registration & Identification</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Registration Number</label>
                            <input type="text" name="registration_number" required placeholder="e.g. FISH-2026-001" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-cyan-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">RSBSA Number</label>
                            <input type="text" name="rsbsa_number" required placeholder="e.g. 08-26-01-0001" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-cyan-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Registration Date</label>
                            <input type="date" name="registration_date" value="<?= date('Y-m-d') ?>" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-cyan-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Barangay</label>
                            <select name="barangay" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-cyan-500 bg-slate-50">
                                <option value="">-- Select Barangay --</option>
                                <?php foreach ($arteche_barangays as $bg): ?>
                                    <option value="<?= htmlspecialchars($bg, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($bg, ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: PERSONAL DEMOGRAPHICS -->
                <div>
                    <h2 class="text-xs font-black uppercase text-cyan-700 tracking-wider mb-4 border-b pb-1">2. Fisherfolk Personal Profile</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Last Name</label>
                            <input type="text" name="last_name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-cyan-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">First Name</label>
                            <input type="text" name="first_name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-cyan-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Middle Name</label>
                            <input type="text" name="middle_name" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-cyan-500 bg-slate-50">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Birth Date</label>
                            <input type="date" name="birth_date" required id="birth_date" onchange="calculateAge(this.value)" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-cyan-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Gender</label>
                            <select name="gender" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-cyan-500 bg-slate-50">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Age</label>
                            <input type="number" name="age" id="age" readonly required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm bg-slate-200 text-slate-600 font-bold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Civil Status</label>
                            <select name="civil_status" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-cyan-500 bg-slate-50">
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Widowed">Widowed</option>
                                <option value="Separated">Separated</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: HOUSEHOLD PROFILE -->
                <div>
                    <h2 class="text-xs font-black uppercase text-cyan-700 tracking-wider mb-4 border-b pb-1">3. Household Profile</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ave. Monthly Income (₱)</label>
                            <input type="number" step="0.01" name="ave_monthly_income" value="0.00" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-cyan-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. of Family Members</label>
                            <input type="number" name="no_family_members" value="1" min="1" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-cyan-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. of Children (If Married)</label>
                            <input type="number" name="no_children" value="0" min="0" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-cyan-500 bg-slate-50">
                        </div>
                    </div>
                </div>

                <!-- SECTION 4: FISHING OPERATIONS & GEARS -->
                <div class="bg-slate-50 p-4 sm:p-5 rounded-2xl border border-slate-200/80 space-y-4">
                    <h2 class="text-xs font-black uppercase text-slate-800 tracking-wider flex items-center gap-2 border-b border-slate-200 pb-2">
                        <i class="fa-solid fa-ship"></i> 4. Fishing Operations & Vessels
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kind of Fishing Activity</label>
                            <input type="text" name="kind_of_fishing_activity" placeholder="e.g. Capture Fishing, Aquaculture" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-cyan-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. of Registered Boats</label>
                            <input type="number" name="no_registered_fishing" value="0" min="0" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-cyan-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. of Unregistered Boats</label>
                            <input type="number" name="no_unregistered_fishing" value="0" min="0" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-cyan-500 bg-white">
                        </div>
                    </div>
                </div>

                <!-- SECTION 5: CATCH & YIELD BY MARINE CATEGORY -->
                <div class="bg-cyan-50/50 p-4 sm:p-5 rounded-2xl border border-cyan-200/80 space-y-6">
                    <h2 class="text-xs font-black uppercase text-cyan-800 tracking-wider flex items-center gap-2 border-b border-cyan-200 pb-2">
                        <i class="fa-solid fa-water"></i> 5. Catch Yield Metrics by Species
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="bg-white p-4 rounded-xl border border-cyan-100 space-y-3">
                            <span class="text-xs font-bold text-cyan-900 uppercase block border-b pb-1">Fish Production</span>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Kind of Fish</label>
                                <input type="text" name="kind_of_fish" placeholder="e.g. Tuna, Galunggong" class="w-full px-3 py-2 rounded-lg border text-sm">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">No. of Catch (Kls)</label>
                                    <input type="number" step="0.01" name="fish_catch_kls" value="0.00" class="w-full px-3 py-2 rounded-lg border text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Type of Gears Used</label>
                                    <input type="text" name="fish_gears_used" placeholder="e.g. Hook & Line" class="w-full px-3 py-2 rounded-lg border text-sm">
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-4 rounded-xl border border-cyan-100 space-y-3">
                            <span class="text-xs font-bold text-cyan-900 uppercase block border-b pb-1">Seashells & Invertebrates</span>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Kind of Seashells</label>
                                <input type="text" name="kind_of_seashells" placeholder="e.g. Tahong, Talaba" class="w-full px-3 py-2 rounded-lg border text-sm">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">No. of Catch (Kls)</label>
                                    <input type="number" step="0.01" name="seashells_catch_kls" value="0.00" class="w-full px-3 py-2 rounded-lg border text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Type of Gears Used</label>
                                    <input type="text" name="seashells_gears_used" placeholder="e.g. Manual Gathering" class="w-full px-3 py-2 rounded-lg border text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="bg-white p-4 rounded-xl border border-cyan-100 space-y-3">
                            <span class="text-xs font-bold text-cyan-900 uppercase block border-b pb-1">Seaweeds Farming</span>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Seaweeds Variety</label>
                                <input type="text" name="seaweeds_variety" placeholder="e.g. Eucheuma Cottonii" class="w-full px-3 py-2 rounded-lg border text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">No. Produced (Kls)</label>
                                <input type="number" step="0.01" name="seaweeds_produced_kls" value="0.00" class="w-full px-3 py-2 rounded-lg border text-sm">
                            </div>
                        </div>

                        <div class="bg-white p-4 rounded-xl border border-cyan-100 space-y-3">
                            <span class="text-xs font-bold text-cyan-900 uppercase block border-b pb-1">Sea Cucumber Harvesting</span>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Sea Cucumber Variety</label>
                                <input type="text" name="sea_cucumber_variety" placeholder="e.g. Balatan" class="w-full px-3 py-2 rounded-lg border text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">No. of Catch (Kls)</label>
                                <input type="number" step="0.01" name="sea_cucumber_catch_kls" value="0.00" class="w-full px-3 py-2 rounded-lg border text-sm">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="bg-white p-4 rounded-xl border border-cyan-100 space-y-3">
                            <span class="text-xs font-bold text-cyan-900 uppercase block border-b pb-1">Blue Swimming Crabs</span>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">No. of Catch (Kls)</label>
                                <input type="number" step="0.01" name="blue_swimming_crabs_catch_kls" value="0.00" class="w-full px-3 py-2 rounded-lg border text-sm">
                            </div>
                        </div>

                        <div class="bg-white p-4 rounded-xl border border-cyan-100 space-y-3">
                            <span class="text-xs font-bold text-cyan-900 uppercase block border-b pb-1">Mangrove Crabs</span>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">No. of Catch (Kls)</label>
                                <input type="number" step="0.01" name="mangrove_crabs_catch_kls" value="0.00" class="w-full px-3 py-2 rounded-lg border text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="submit" id="submitBtnText" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 shadow-md flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Update Fishery Profile</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- MASTERLIST TABLE SECTION WITH SEARCH & PAGINATION -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-sm space-y-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-list text-cyan-600"></i> Registered Fisherfolk Masterlist
                    </h2>
                    <p class="text-xs text-slate-500">Showing records stored in the database with filtering and pagination.</p>
                </div>
                <span class="px-3 py-1 bg-cyan-50 text-cyan-700 text-xs font-black rounded-xl border border-cyan-100">
                    Total Records: <?= $totalRecords ?>
                </span>
            </div>

            <!-- Search and Filter Controls -->
            <form method="GET" action="" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <input type="text" name="search" value="<?= htmlspecialchars($search_query) ?>" placeholder="Search name, RSBSA, Reg No..." class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-cyan-500 bg-slate-50">
                </div>
                <div>
                    <select name="barangay" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-cyan-500 bg-slate-50">
                        <option value="">-- All Barangays --</option>
                        <?php foreach ($arteche_barangays as $bg): ?>
                            <option value="<?= htmlspecialchars($bg) ?>" <?= $filter_barangay === $bg ? 'selected' : '' ?>><?= htmlspecialchars($bg) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition">
                        <i class="fa-solid fa-filter mr-1"></i> Filter
                    </button>
                    <a href="fishery_farmers_list.php" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-bold transition">
                        Reset
                    </a>
                </div>
            </form>

            <!-- Table -->
            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-900 text-white uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Barangay</th>
                            <th class="px-4 py-3">RSBSA No.</th>
                            <th class="px-4 py-3">Reg. No.</th>
                            <th class="px-4 py-3">Full Name</th>
                            <th class="px-4 py-3">Fishing Activity</th>
                            <th class="px-4 py-3">Monthly Income</th>
                            <th class="px-4 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <?php if (count($fisheryFarmers) > 0): ?>
                            <?php foreach ($fisheryFarmers as $index => $farmer): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-3 font-bold text-slate-500"><?= $offset + $index + 1 ?></td>
                                    <td class="px-4 py-3 font-semibold text-slate-700"><?= htmlspecialchars($farmer['barangay'] ?? '') ?></td>
                                    <td class="px-4 py-3 font-mono text-slate-600"><?= htmlspecialchars($farmer['rsbsa_number'] ?? '') ?></td>
                                    <td class="px-4 py-3 font-mono text-slate-600"><?= htmlspecialchars($farmer['registration_number'] ?? '') ?></td>
                                    <td class="px-4 py-3 font-extrabold text-slate-900"><?= htmlspecialchars(($farmer['last_name'] ?? '') . ', ' . ($farmer['first_name'] ?? '') . ' ' . ($farmer['middle_name'] ?? '')) ?></td>
                                    <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($farmer['kind_of_fishing_activity'] ?? 'N/A') ?></td>
                                    <td class="px-4 py-3 font-semibold text-emerald-700">₱<?= number_format((float)($farmer['ave_monthly_income'] ?? 0), 2) ?></td>
                                    <td class="px-4 py-3 text-center space-x-1">
                                        <button type="button" onclick='loadRecordForEdit(<?= json_encode($farmer) ?>)' class="px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-bold transition shadow-sm" title="Edit Record">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button type="button" onclick="deleteRecord(<?= $farmer['id'] ?>)" class="px-2.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition shadow-sm" title="Delete Record">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-8 text-slate-400 italic">No fishery records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <?php if ($totalPages > 1): ?>
                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <span class="text-xs text-slate-500">Page <strong><?= $page ?></strong> of <strong><?= $totalPages ?></strong></span>
                    <div class="flex items-center space-x-1">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search_query) ?>&barangay=<?= urlencode($filter_barangay) ?>" class="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-bold transition">Prev</a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <a href="?page=<?= $i ?>&search=<?= urlencode($search_query) ?>&barangay=<?= urlencode($filter_barangay) ?>" class="px-3 py-1.5 <?= $i === $page ? 'bg-cyan-600 text-white font-black' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?> rounded-lg text-xs font-bold transition"><?= $i ?></a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search_query) ?>&barangay=<?= urlencode($filter_barangay) ?>" class="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-bold transition">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
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

        function loadRecordForEdit(farmer) {
            const form = document.getElementById('fisheryFarmerForm');
            for (const key in farmer) {
                const input = form.elements[key];
                if (input) {
                    input.value = farmer[key];
                }
            }
            document.getElementById('recordId').value = farmer.id;
            document.getElementById('formAction').value = 'update_fishery_farmer';
            document.getElementById('formTitle').innerHTML = '<i class="fa-solid fa-pen-to-square text-amber-600"></i> Edit Fisherfolk Registry Entry (ID: ' + farmer.id + ')';
            
            if (farmer.birth_date) {
                calculateAge(farmer.birth_date);
            }

            // Unhide the form container and scroll smoothly to it
            const container = document.getElementById('registrationFormContainer');
            container.classList.remove('hidden');
            container.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function resetToRegisterMode() {
            document.getElementById('fisheryFarmerForm').reset();
            document.getElementById('recordId').value = '';
            document.getElementById('formAction').value = 'update_fishery_farmer';
            // Hide the form container again when canceling
            document.getElementById('registrationFormContainer').classList.add('hidden');
        }

        function deleteRecord(id) {
            if (!confirm('Are you sure you want to delete this fisherfolk record?')) return;

            const formData = new FormData();
            formData.append('action', 'delete_fishery_farmer');
            formData.append('id', id);
            formData.append('csrf_token', '<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>');

            fetch('api_dashboard.php', { method: 'POST', body: formData })
            .then(async res => {
                const text = await res.text();
                try { return JSON.parse(text); } catch(err) { throw new Error(text); }
            })
            .then(data => {
                alert(data.message || 'Action completed successfully.');
                if (data.success) { location.reload(); }
            })
            .catch(err => { alert('Error: ' + err.message); });
        }

        // Handle Individual Update Submission
        document.getElementById('fisheryFarmerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('api_dashboard.php', { method: 'POST', body: formData })
            .then(async res => {
                const text = await res.text();
                try { return JSON.parse(text); } catch (err) { throw new Error(text); }
            })
            .then(data => {
                const alertBox = document.getElementById('formAlert');
                alertBox.classList.remove('hidden', 'bg-red-50', 'text-red-700', 'bg-emerald-50', 'text-emerald-700');
                if (data.success) {
                    alertBox.classList.add('bg-emerald-50', 'text-emerald-700');
                    alertBox.innerText = data.message || "Profile updated successfully.";
                    resetToRegisterMode();
                    setTimeout(() => location.reload(), 1200);
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
            });
        });
    </script>
</body>
</html>