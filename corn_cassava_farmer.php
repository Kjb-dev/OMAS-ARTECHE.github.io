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
    <title>Corn & Cassava Registry | OMAS LGU Arteche</title>
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
                <span class="text-lg font-black tracking-wide">OMAS ARTECHE <span class="text-xs font-normal text-amber-400">| Corn & Cassava Registry Form</span></span>
            </div>
            <a href="dashboard.php" class="bg-slate-800 hover:bg-slate-700 text-xs px-3 py-2 rounded-xl border border-slate-700 transition font-semibold">
                Back to Dashboard
            </a>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full space-y-6">
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-sm">
            <div class="border-b border-slate-100 pb-4 mb-6">
                <h1 class="text-xl font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-wheat-awn text-amber-600"></i> Corn & Cassava Farmer Entry
                </h1>
                <p class="text-xs text-slate-500 mt-1">Record agricultural profiles, land area, ecosystem classification, machinery, and production metrics for corn and cassava production.</p>
            </div>

            <div id="formAlert" class="hidden p-4 mb-6 rounded-2xl text-xs font-semibold whitespace-pre-wrap"></div>

            <form id="cornCassavaForm" class="space-y-8">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="save_corn_cassava">

                <!-- SECTION 1: GENERAL DEMOGRAPHICS & IDENTIFICATION -->
                <div>
                    <h2 class="text-xs font-black uppercase text-amber-700 tracking-wider mb-4 border-b pb-1">1. Farmer Profile & Demographics</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Barangay</label>
                            <select name="barangay" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 bg-slate-50">
                                <option value="">-- Select Barangay --</option>
                                <?php foreach ($arteche_barangays as $bg): ?>
                                    <option value="<?= htmlspecialchars($bg, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($bg, ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Name of Registered Farmer</label>
                            <input type="text" name="farmer_name" required placeholder="Full Name (First, Middle, Last)" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">RSBSA #</label>
                            <input type="text" name="rsbsa_no" required placeholder="e.g. 08-26-01-0001" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 bg-slate-50">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Birthday</label>
                            <input type="date" name="birthday" required id="birthday" onchange="calculateAge(this.value)" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Age</label>
                            <input type="number" name="age" id="age" readonly required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm bg-slate-200 text-slate-600 font-bold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Civil Status</label>
                            <select name="civil_status" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 bg-slate-50">
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Widowed">Widowed</option>
                                <option value="Separated">Separated</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tenurial Status</label>
                            <input type="text" name="tenurial_status" placeholder="e.g. Owner, Tenant, Leaseholder" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 bg-slate-50">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ave. Monthly HH Income (₱)</label>
                            <input type="number" step="0.01" name="ave_monthly_hh_income" value="0.00" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. of Household Members</label>
                            <input type="number" name="no_household_members" value="1" min="1" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. of Children (If Married)</label>
                            <input type="number" name="no_children" value="0" min="0" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 bg-slate-50">
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: CORN PRODUCTION METRICS -->
                <div class="bg-amber-50/50 p-4 sm:p-5 rounded-2xl border border-amber-200/80 space-y-4">
                    <h2 class="text-xs font-black uppercase text-amber-900 tracking-wider flex items-center gap-2 border-b border-amber-200 pb-2">
                        <i class="fa-solid fa-seedling text-amber-600"></i> 2. Corn Farming Operations
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Corn Area (Has)</label>
                            <input type="number" step="0.01" name="corn_area_has" value="0.00" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Variety Used</label>
                            <input type="text" name="corn_variety_used" placeholder="e.g. Yellow Corn, Glutinous White" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. of Hills Planted</label>
                            <input type="number" name="corn_no_hills_planted" value="0" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 bg-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ecosystem</label>
                            <select name="corn_ecosystem" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 bg-white">
                                <option value="">-- Select Ecosystem --</option>
                                <option value="I">I (Irrigated)</option>
                                <option value="R">R (Rainfed)</option>
                            </select>
                            <p class="text-[10px] text-slate-500 mt-0.5">Write I for irrigated, R for rainfed</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Annual Production (MT)</label>
                            <input type="number" step="0.01" name="corn_annual_production_mt" value="0.00" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Annual Frequency of Planting</label>
                            <input type="number" name="corn_annual_frequency_planting" value="0" placeholder="e.g. 2 times/year" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Production per Cropping (MT)</label>
                            <input type="number" step="0.01" name="corn_production_per_cropping_mt" value="0.00" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 bg-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Owned Machinery / Equipment & Facility Operated</label>
                        <input type="text" name="corn_machinery_operated" placeholder="e.g. Corn Sheller, Hand Tractor, Dryer" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 bg-white">
                    </div>
                </div>

                <!-- SECTION 3: CASSAVA PRODUCTION METRICS -->
                <div class="bg-lime-50/50 p-4 sm:p-5 rounded-2xl border border-lime-200/80 space-y-4">
                    <h2 class="text-xs font-black uppercase text-lime-900 tracking-wider flex items-center gap-2 border-b border-lime-200 pb-2">
                        <i class="fa-solid fa-plant-wilt text-lime-700"></i> 3. Cassava Farming Operations
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Cassava Area (Has)</label>
                            <input type="number" step="0.01" name="cassava_area_has" value="0.00" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-lime-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Variety Used</label>
                            <input type="text" name="cassava_variety_used" placeholder="e.g. Lakan, Rayong, Golden" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-lime-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. of Hills Planted</label>
                            <input type="number" name="cassava_no_hills_planted" value="0" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-lime-500 bg-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ecosystem</label>
                            <select name="cassava_ecosystem" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-lime-500 bg-white">
                                <option value="">-- Select Ecosystem --</option>
                                <option value="I">I (Irrigated)</option>
                                <option value="R">R (Rainfed)</option>
                            </select>
                            <p class="text-[10px] text-slate-500 mt-0.5">Write I for irrigated, R for rainfed</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Annual Production (MT)</label>
                            <input type="number" step="0.01" name="cassava_annual_production_mt" value="0.00" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-lime-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Owned Machinery / Equipment & Facility Operated</label>
                            <input type="text" name="cassava_machinery_operated" placeholder="e.g. Cassava Grater, Granulator, Dryer" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-lime-500 bg-white">
                        </div>
                    </div>
                </div>

                <!-- SECTION 4: ADDITIONAL REMARKS -->
                <div>
                    <h2 class="text-xs font-black uppercase text-amber-700 tracking-wider mb-2">4. Remarks</h2>
                    <textarea name="remarks" rows="3" placeholder="Enter optional notes, field observations, or additional program remarks..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 bg-slate-50"></textarea>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="dashboard.php" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 shadow-md flex items-center gap-2">
                        <i class="fa-solid fa-save"></i>
                        <span>Save Corn & Cassava Profile</span>
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

        document.getElementById('cornCassavaForm').addEventListener('submit', function(e) {
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
                    alertBox.innerText = data.message || "Corn & Cassava farmer profile saved successfully.";
                    document.getElementById('cornCassavaForm').reset();
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