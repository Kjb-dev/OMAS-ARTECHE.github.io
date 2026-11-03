<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php';

if (!isset($_SESSION['staff_logged_in']) || $_SESSION['staff_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Updated SQL search condition to match table schema (farmer_name instead of last_name/first_name)
$where = $search !== '' ? "WHERE rsbsa_no LIKE ? OR farmer_name LIKE ? OR barangay LIKE ?" : "";
$params = $search !== '' ? ["%$search%", "%$search%", "%$search%"] : [];

$stmt = $pdo->prepare("SELECT * FROM corn_and_cassava $where ORDER BY id DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$farmers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Corn & Cassava Farmers Registry</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 text-slate-800 font-sans p-6">
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <h1 class="text-xl font-black text-slate-900"><i class="fa-solid fa-plant-wilt text-amber-600 mr-2"></i>Corn & Cassava Registry</h1>
            <a href="dashboard.php" class="bg-slate-800 hover:bg-slate-700 text-white text-xs px-4 py-2.5 rounded-xl font-bold">Dashboard</a>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 font-bold uppercase text-slate-600">
                    <tr>
                        <th class="p-3.5">RSBSA No.</th>
                        <th class="p-3.5">Full Name</th>
                        <th class="p-3.5">Barangay</th>
                        <th class="p-3.5">Corn Area (ha)</th>
                        <th class="p-3.5">Cassava Area (ha)</th>
                        <th class="p-3.5 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($farmers)): ?>
                        <tr><td colspan="6" class="p-4 text-center text-slate-400">No records found.</td></tr>
                    <?php else: foreach ($farmers as $f): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="p-3.5 font-bold"><?= htmlspecialchars($f['rsbsa_no'] ?? '') ?></td>
                            <td class="p-3.5 font-semibold"><?= htmlspecialchars($f['farmer_name'] ?? '') ?></td>
                            <td class="p-3.5"><?= htmlspecialchars($f['barangay'] ?? '') ?></td>
                            <td class="p-3.5 font-medium text-amber-800"><?= number_format((float)($f['corn_area_has'] ?? 0), 2) ?></td>
                            <td class="p-3.5 font-medium text-emerald-800"><?= number_format((float)($f['cassava_area_has'] ?? 0), 2) ?></td>
                            <td class="p-3.5 text-center">
                                <a href="corn_cassava_farmer.php?id=<?= $f['id'] ?>" class="bg-blue-50 text-blue-600 hover:bg-blue-100 px-2.5 py-1 rounded-lg font-bold transition-colors">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>