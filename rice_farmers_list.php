<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// Handle Delete Action
if (isset($_POST['action']) && $_POST['action'] === 'delete_farmer') {
    $delete_id = (int)($_POST['id'] ?? 0);
    if ($delete_id > 0) {
        try {
            $del_stmt = $pdo->prepare("DELETE FROM rice_farmers WHERE id = ?");
            $del_stmt->execute([$delete_id]);
            $_SESSION['flash_message'] = "Farmer record deleted successfully.";
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = "Failed to delete record: " . $e->getMessage();
        }
    }
    header("Location: rice_farmers_list.php");
    exit();
}

// Handle Update Action
if (isset($_POST['action']) && $_POST['action'] === 'update_farmer') {
    $id = (int)($_POST['id'] ?? 0);
    
    if ($id > 0) {
        try {
            $sql = "UPDATE rice_farmers SET 
                barangay = :barangay,
                rsbsa_no = :rsbsa_no,
                last_name = :last_name,
                first_name = :first_name,
                middle_name = :middle_name,
                birthday = :birthday,
                gender = :gender,
                age = :age,
                civil_status = :civil_status,
                ave_monthly_income = :ave_monthly_income,
                no_household_members = :no_household_members,
                no_children = :no_children,
                rice_area = :rice_area,
                area_planted = :area_planted,
                area_harvested = :area_harvested,
                annual_production = :annual_production,
                tenurial_status = :tenurial_status,
                status_rice_area = :status_rice_area,
                ecosystem = :ecosystem,
                preferred_variety = :preferred_variety,
                hand_tractors_count = :hand_tractors_count,
                water_pumps_count = :water_pumps_count,
                knapsack_sprayers_count = :knapsack_sprayers_count,
                cropping_season_count = :cropping_season_count,
                remarks = :remarks
                WHERE id = :id";

            $upd_stmt = $pdo->prepare($sql);
            $upd_stmt->execute([
                ':barangay'               => trim($_POST['barangay'] ?? ''),
                ':rsbsa_no'              => trim($_POST['rsbsa_no'] ?? ''),
                ':last_name'             => trim($_POST['last_name'] ?? ''),
                ':first_name'            => trim($_POST['first_name'] ?? ''),
                ':middle_name'           => trim($_POST['middle_name'] ?? ''),
                ':birthday'              => !empty($_POST['birthday']) ? $_POST['birthday'] : null,
                ':gender'                => $_POST['gender'] ?? 'Male',
                ':age'                   => (int)($_POST['age'] ?? 0),
                ':civil_status'          => $_POST['civil_status'] ?? 'Single',
                ':ave_monthly_income'    => (float)($_POST['ave_monthly_income'] ?? 0.00),
                ':no_household_members'  => (int)($_POST['no_household_members'] ?? 0),
                ':no_children'           => (int)($_POST['no_children'] ?? 0),
                ':rice_area'             => (float)($_POST['rice_area'] ?? 0.00),
                ':area_planted'          => (float)($_POST['area_planted'] ?? 0.00),
                ':area_harvested'        => (float)($_POST['area_harvested'] ?? 0.00),
                ':annual_production'     => (float)($_POST['annual_production'] ?? 0.00),
                ':tenurial_status'       => $_POST['tenurial_status'] ?? 'Owner',
                ':status_rice_area'      => $_POST['status_rice_area'] ?? 'C',
                ':ecosystem'             => $_POST['ecosystem'] ?? 'I',
                ':preferred_variety'     => $_POST['preferred_variety'] ?? 'G',
                ':hand_tractors_count'   => (int)($_POST['hand_tractors_count'] ?? 0),
                ':water_pumps_count'     => (int)($_POST['water_pumps_count'] ?? 0),
                ':knapsack_sprayers_count' => (int)($_POST['knapsack_sprayers_count'] ?? 0),
                ':cropping_season_count' => (int)($_POST['cropping_season_count'] ?? 1),
                ':remarks'               => trim($_POST['remarks'] ?? ''),
                ':id'                    => $id
            ]);

            $_SESSION['flash_message'] = "Farmer information updated successfully.";
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = "Update failed: " . $e->getMessage();
        }
    }
    header("Location: rice_farmers_list.php");
    exit();
}

// Filter Options
$search = trim($_GET['search'] ?? '');
$barangay = trim($_GET['barangay'] ?? '');
$filter_status = trim($_GET['filter_status'] ?? ''); // 'no_rsbsa' or 'duplicate_rsbsa'
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(rsbsa_no LIKE ? OR last_name LIKE ? OR first_name LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}
if ($barangay !== '') {
    $where[] = "barangay = ?";
    $params[] = $barangay;
}

if ($filter_status === 'no_rsbsa') {
    $where[] = "(rsbsa_no IS NULL OR TRIM(rsbsa_no) = '' OR LOWER(rsbsa_no) = 'n/a' OR LOWER(rsbsa_no) = 'pending')";
} elseif ($filter_status === 'duplicate_rsbsa') {
    $where[] = "rsbsa_no IN (
        SELECT rsbsa_no FROM rice_farmers 
        WHERE rsbsa_no IS NOT NULL AND TRIM(rsbsa_no) != '' AND LOWER(rsbsa_no) != 'n/a'
        GROUP BY rsbsa_no HAVING COUNT(*) > 1
    )";
}

$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

// Fetch set of Duplicate RSBSA numbers for visual badges
$duplicate_rsbsas = [];
try {
    $dup_stmt = $pdo->query("SELECT rsbsa_no FROM rice_farmers WHERE rsbsa_no IS NOT NULL AND TRIM(rsbsa_no) != '' AND LOWER(rsbsa_no) != 'n/a' GROUP BY rsbsa_no HAVING COUNT(*) > 1");
    $duplicate_rsbsas = $dup_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $duplicate_rsbsas = [];
}

// Count Total Records
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM rice_farmers $where_sql");
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = max(1, ceil($total_records / $limit));

// Fetch Records
$stmt = $pdo->prepare("SELECT * FROM rice_farmers $where_sql ORDER BY id DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$farmers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$flash_message = $_SESSION['flash_message'] ?? null;
$flash_error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_message'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rice Farmers Masterlist | OMAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased min-h-screen p-6">
    <div class="max-w-7xl mx-auto space-y-6">

        <!-- Flash Messages -->
        <?php if ($flash_message): ?>
            <div class="p-4 bg-emerald-100 text-emerald-800 rounded-2xl font-bold text-xs border border-emerald-300">
                <i class="fa-solid fa-circle-check mr-2"></i><?= htmlspecialchars($flash_message) ?>
            </div>
        <?php endif; ?>
        <?php if ($flash_error): ?>
            <div class="p-4 bg-rose-100 text-rose-800 rounded-2xl font-bold text-xs border border-rose-300">
                <i class="fa-solid fa-triangle-exclamation mr-2"></i><?= htmlspecialchars($flash_error) ?>
            </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div>
                <h1 class="text-xl font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-bowl-rice text-emerald-600"></i> Rice Farmers Masterlist
                </h1>
                <p class="text-xs text-slate-500 mt-1">Total Registered: <strong><?= $total_records ?></strong></p>
            </div>
            <a href="dashboard.php" class="bg-slate-800 hover:bg-slate-700 text-white text-xs px-4 py-2.5 rounded-xl font-bold transition">
                Back to Dashboard
            </a>
        </div>

        <!-- Filter Form -->
        <form method="GET" class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row gap-3">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search RSBSA or Name..." class="px-3.5 py-2 border rounded-xl text-xs w-full md:w-1/4 bg-slate-50">
            
            <select name="barangay" class="px-3.5 py-2 border rounded-xl text-xs w-full md:w-1/4 bg-slate-50">
                <option value="">-- All Barangays --</option>
                <?php foreach ($arteche_barangays as $bg): ?>
                    <option value="<?= htmlspecialchars($bg) ?>" <?= $barangay === $bg ? 'selected' : '' ?>><?= htmlspecialchars($bg) ?></option>
                <?php endforeach; ?>
            </select>

            <select name="filter_status" class="px-3.5 py-2 border rounded-xl text-xs w-full md:w-1/4 bg-slate-50">
                <option value="">-- RSBSA Status --</option>
                <option value="no_rsbsa" <?= $filter_status === 'no_rsbsa' ? 'selected' : '' ?>>No RSBSA Number</option>
                <option value="duplicate_rsbsa" <?= $filter_status === 'duplicate_rsbsa' ? 'selected' : '' ?>>Duplicate RSBSA Numbers</option>
            </select>

            <div class="flex gap-2">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs px-5 py-2 rounded-xl font-bold">Filter</button>
                <a href="rice_farmers_list.php" class="bg-slate-100 text-slate-600 text-xs px-4 py-2 rounded-xl font-bold text-center flex items-center justify-center">Reset</a>
            </div>
        </form>

        <!-- Masterlist Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase font-bold">
                    <tr>
                        <th class="p-3.5">RSBSA No.</th>
                        <th class="p-3.5">Full Name</th>
                        <th class="p-3.5">Barangay</th>
                        <th class="p-3.5">Rice Area (ha)</th>
                        <th class="p-3.5">Tenurial Status</th>
                        <th class="p-3.5">Annual Prod. (MT)</th>
                        <th class="p-3.5 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($farmers)): ?>
                        <tr><td colspan="7" class="p-4 text-center text-slate-400">No rice farmer records found.</td></tr>
                    <?php else: foreach ($farmers as $f): 
                        $rsbsa_clean = trim($f['rsbsa_no'] ?? '');
                        $has_no_rsbsa = empty($rsbsa_clean) || strtolower($rsbsa_clean) === 'n/a' || strtolower($rsbsa_clean) === 'pending';
                        $is_duplicate = !$has_no_rsbsa && in_array($rsbsa_clean, $duplicate_rsbsas);
                    ?>
                        <tr class="hover:bg-slate-50">
                            <td class="p-3.5 font-bold">
                                <?php if ($has_no_rsbsa): ?>
                                    <span class="text-rose-500 italic"><i class="fa-solid fa-triangle-exclamation mr-1"></i>No RSBSA</span>
                                <?php else: ?>
                                    <span class="text-slate-900"><?= htmlspecialchars($f['rsbsa_no']) ?></span>
                                    <?php if ($is_duplicate): ?>
                                        <span class="ml-1 px-2 py-0.5 bg-amber-100 text-amber-800 rounded-md font-bold text-[10px] inline-block">Duplicate</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td class="p-3.5"><?= htmlspecialchars("{$f['last_name']}, {$f['first_name']} {$f['middle_name']}") ?></td>
                            <td class="p-3.5"><?= htmlspecialchars($f['barangay']) ?></td>
                            <td class="p-3.5"><?= number_format((float)($f['rice_area'] ?? 0), 2) ?></td>
                            <td class="p-3.5"><?= htmlspecialchars($f['tenurial_status'] ?? '') ?></td>
                            <td class="p-3.5"><?= number_format((float)($f['annual_production'] ?? 0), 2) ?></td>
                            <td class="p-3.5 text-center space-x-1 whitespace-nowrap">
                                <button onclick='openEditModal(<?= json_encode($f, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' class="bg-blue-50 text-blue-600 hover:bg-blue-100 px-2.5 py-1.5 rounded-lg font-bold transition">
                                    <i class="fa-solid fa-pen mr-1"></i> Edit
                                </button>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this farmer record?');" class="inline-block">
                                    <input type="hidden" name="action" value="delete_farmer">
                                    <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                    <button type="submit" class="bg-rose-50 text-rose-600 hover:bg-rose-100 px-2.5 py-1.5 rounded-lg font-bold transition">
                                        <i class="fa-solid fa-trash mr-1"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Navigation -->
        <?php if ($total_pages > 1): ?>
            <div class="flex items-center justify-between bg-white p-4 rounded-2xl border border-slate-200 shadow-sm text-xs">
                <span class="text-slate-500">Page <strong><?= $page ?></strong> of <strong><?= $total_pages ?></strong></span>
                <div class="flex gap-1">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&barangay=<?= urlencode($barangay) ?>&filter_status=<?= urlencode($filter_status) ?>" class="bg-slate-100 text-slate-700 px-3 py-1.5 rounded-lg font-bold hover:bg-slate-200">Previous</a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&barangay=<?= urlencode($barangay) ?>&filter_status=<?= urlencode($filter_status) ?>" class="<?= $i === $page ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?> px-3 py-1.5 rounded-lg font-bold">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&barangay=<?= urlencode($barangay) ?>&filter_status=<?= urlencode($filter_status) ?>" class="bg-slate-100 text-slate-700 px-3 py-1.5 rounded-lg font-bold hover:bg-slate-200">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- UPDATE FARMER MODAL -->
    <div id="editModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="bg-slate-900 text-white p-5 sticky top-0 z-10 flex justify-between items-center border-b border-slate-800">
                <h3 class="font-extrabold text-sm flex items-center gap-2">
                    <i class="fa-solid fa-user-pen text-emerald-400"></i> Edit Rice Farmer Record
                </h3>
                <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-white font-black text-xl line-none">&times;</button>
            </div>

            <form method="POST" class="p-6 sm:p-8 space-y-8">
                <input type="hidden" name="action" value="update_farmer">
                <input type="hidden" name="id" id="edit_id">

                <!-- 1. Location & Identification -->
                <div>
                    <h2 class="text-xs font-black uppercase text-emerald-700 tracking-wider mb-4 border-b pb-1">1. Primary Information</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Barangay</label>
                            <select name="barangay" id="edit_barangay" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                                <option value="">-- Select Barangay --</option>
                                <?php foreach ($arteche_barangays as $bg): ?>
                                    <option value="<?= htmlspecialchars(trim($bg)) ?>"><?= htmlspecialchars(trim($bg)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">RSBSA Reference Number (Optional)</label>
                            <input type="text" name="rsbsa_no" id="edit_rsbsa_no" placeholder="Leave blank if no RSBSA" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                    </div>
                </div>

                <!-- 2. Personal Details -->
                <div>
                    <h2 class="text-xs font-black uppercase text-emerald-700 tracking-wider mb-4 border-b pb-1">2. Farmer Profile Details</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Last Name</label>
                            <input type="text" name="last_name" id="edit_last_name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">First Name</label>
                            <input type="text" name="first_name" id="edit_first_name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Middle Name</label>
                            <input type="text" name="middle_name" id="edit_middle_name" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Birthday</label>
                            <input type="date" name="birthday" id="edit_birthday" onchange="calculateAge(this.value)" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Gender</label>
                            <select name="gender" id="edit_gender" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Age</label>
                            <input type="number" name="age" id="edit_age" readonly class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm bg-slate-200 text-slate-600 font-bold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Civil Status</label>
                            <select name="civil_status" id="edit_civil_status" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Widowed">Widowed</option>
                                <option value="Separated">Separated</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 3. Household Information -->
                <div>
                    <h2 class="text-xs font-black uppercase text-emerald-700 tracking-wider mb-4 border-b pb-1">3. Household Profile</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ave. Monthly HH Income (₱)</label>
                            <input type="number" step="0.01" name="ave_monthly_income" id="edit_ave_monthly_income" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. of Household Members</label>
                            <input type="number" name="no_household_members" id="edit_no_household_members" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. of Children (If Married)</label>
                            <input type="number" name="no_children" id="edit_no_children" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                    </div>
                </div>

                <!-- 4. Farm Land & Rice Production Metrics -->
                <div>
                    <h2 class="text-xs font-black uppercase text-emerald-700 tracking-wider mb-4 border-b pb-1">4. Rice Production Metrics</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Rice Area (Has)</label>
                            <input type="number" step="0.01" name="rice_area" id="edit_rice_area" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Area Planted (Has)</label>
                            <input type="number" step="0.01" name="area_planted" id="edit_area_planted" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Area Harvested (Has)</label>
                            <input type="number" step="0.01" name="area_harvested" id="edit_area_harvested" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ave. Annual Prod. (MT)</label>
                            <input type="number" step="0.01" name="annual_production" id="edit_annual_production" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tenurial Status</label>
                            <select name="tenurial_status" id="edit_tenurial_status" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                                <option value="Owner">Owner</option>
                                <option value="Tenant">Tenant</option>
                                <option value="Lessee">Lessee</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Rice Area Status</label>
                            <select name="status_rice_area" id="edit_status_rice_area" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                                <option value="C">C - Cultivated</option>
                                <option value="U">U - Uncultivated</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ecosystem</label>
                            <select name="ecosystem" id="edit_ecosystem" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                                <option value="I">I - Irrigated</option>
                                <option value="R">R - Rainfed</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Preferred Variety</label>
                            <select name="preferred_variety" id="edit_preferred_variety" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                                <option value="G">G - Good Seeds</option>
                                <option value="C">C - Certified Seeds</option>
                                <option value="H">H - Hybrid Seeds</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 5. Machinery & Equipment Owned -->
                <div>
                    <h2 class="text-xs font-black uppercase text-emerald-700 tracking-wider mb-4 border-b pb-1">5. Farm Equipment & Cropping Season</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Hand Tractors Owned</label>
                            <input type="number" name="hand_tractors_count" id="edit_hand_tractors_count" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Water Pumps Owned</label>
                            <input type="number" name="water_pumps_count" id="edit_water_pumps_count" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Knapsack Sprayers Owned</label>
                            <input type="number" name="knapsack_sprayers_count" id="edit_knapsack_sprayers_count" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Cropping Seasons/Yr</label>
                            <input type="number" name="cropping_season_count" id="edit_cropping_season_count" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                    </div>
                </div>

                <!-- 6. Remarks -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Remarks / Remarks Field Note</label>
                    <textarea name="remarks" id="edit_remarks" rows="2" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50"></textarea>
                </div>

                <!-- Submission Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md flex items-center gap-2">
                        <i class="fa-solid fa-save"></i>
                        <span>Update Farmer Record</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

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
            document.getElementById('edit_age').value = age > 0 ? age : 0;
        }

        function openEditModal(farmer) {
            document.getElementById('edit_id').value = farmer.id || '';
            document.getElementById('edit_rsbsa_no').value = farmer.rsbsa_no || '';
            document.getElementById('edit_last_name').value = farmer.last_name || '';
            document.getElementById('edit_first_name').value = farmer.first_name || '';
            document.getElementById('edit_middle_name').value = farmer.middle_name || '';
            document.getElementById('edit_birthday').value = farmer.birthday || '';
            document.getElementById('edit_gender').value = farmer.gender || 'Male';
            document.getElementById('edit_age').value = farmer.age || 0;
            document.getElementById('edit_civil_status').value = farmer.civil_status || 'Single';
            document.getElementById('edit_ave_monthly_income').value = farmer.ave_monthly_income || 0;
            document.getElementById('edit_no_household_members').value = farmer.no_household_members || 0;
            document.getElementById('edit_no_children').value = farmer.no_children || 0;
            document.getElementById('edit_rice_area').value = farmer.rice_area || 0;
            document.getElementById('edit_area_planted').value = farmer.area_planted || 0;
            document.getElementById('edit_area_harvested').value = farmer.area_harvested || 0;
            document.getElementById('edit_annual_production').value = farmer.annual_production || 0;
            document.getElementById('edit_tenurial_status').value = farmer.tenurial_status || 'Owner';
            document.getElementById('edit_status_rice_area').value = farmer.status_rice_area || 'C';
            document.getElementById('edit_ecosystem').value = farmer.ecosystem || 'I';
            document.getElementById('edit_preferred_variety').value = farmer.preferred_variety || 'G';
            document.getElementById('edit_hand_tractors_count').value = farmer.hand_tractors_count || 0;
            document.getElementById('edit_water_pumps_count').value = farmer.water_pumps_count || 0;
            document.getElementById('edit_knapsack_sprayers_count').value = farmer.knapsack_sprayers_count || 0;
            document.getElementById('edit_cropping_season_count').value = farmer.cropping_season_count || 1;
            document.getElementById('edit_remarks').value = farmer.remarks || '';

            // Robust Barangay Auto-Select Match Logic
            const barangaySelect = document.getElementById('edit_barangay');
            const dbBarangay = (farmer.barangay || '').trim().toLowerCase();
            let matched = false;

            for (let i = 0; i < barangaySelect.options.length; i++) {
                const optValue = barangaySelect.options[i].value.trim().toLowerCase();
                if (optValue === dbBarangay || (dbBarangay !== '' && optValue.includes(dbBarangay))) {
                    barangaySelect.selectedIndex = i;
                    matched = true;
                    break;
                }
            }

            if (!matched) {
                barangaySelect.value = '';
            }

            if (farmer.birthday) {
                calculateAge(farmer.birthday);
            }

            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }
    </script>
</body>
</html>