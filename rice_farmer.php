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
    <title>Rice Farmer Registry | OMAS LGU Arteche</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased min-h-screen flex flex-col">

    <!-- Top Header Navigation -->
    <header class="bg-slate-900 text-white sticky top-0 z-40 border-b border-slate-800 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="dashboard.php" class="text-slate-400 hover:text-white transition mr-2">
                    <i class="fa-solid fa-arrow-left text-lg"></i>
                </a>
                <img src="image/bungto_han_arteche.png" alt="LGU Arteche" class="w-9 h-9 object-cover rounded-full bg-white p-0.5" onerror="this.src='https://via.placeholder.com/36'">
                <span class="text-lg font-black tracking-wide">OMAS ARTECHE <span class="text-xs font-normal text-emerald-400">| Rice Farmer Registry Form</span></span>
            </div>
            <a href="dashboard.php" class="bg-slate-800 hover:bg-slate-700 text-xs px-3 py-2 rounded-xl border border-slate-700 transition font-semibold">
                Back to Dashboard
            </a>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full space-y-6">

        <!-- BULK EXCEL UPLOAD SECTION -->
        <div class="bg-gradient-to-r from-emerald-800 to-slate-900 p-6 rounded-3xl text-white shadow-md space-y-4">
            <div class="flex items-center justify-between border-b border-emerald-700/60 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-500/20 border border-emerald-400/30 flex items-center justify-center text-emerald-400 font-bold">
                        <i class="fa-solid fa-file-excel text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-extrabold">Batch Registration (Excel Masterlist)</h2>
                        <p class="text-xs text-slate-300">Upload farmer survey spreadsheet files directly to import bulk records.</p>
                    </div>
                </div>
            </div>

            <form id="bulkUploadForm" class="flex flex-col sm:flex-row items-center gap-3" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="upload_rice_farmers_excel">

                <input type="file" name="excel_file" accept=".xlsx, .xls, .csv" required class="block w-full text-xs text-slate-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-600 file:text-white hover:file:bg-emerald-500 bg-slate-800/80 rounded-xl border border-slate-700 cursor-pointer">
                
                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs shadow-md transition flex items-center justify-center gap-2 whitespace-nowrap">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <span>Upload & Register</span>
                </button>
            </form>
            <p class="text-[11px] text-slate-400 italic">
                <i class="fa-solid fa-circle-info mr-1"></i>Supports <strong>.xlsx</strong> files directly matching yellow RSBSA survey forms (e.g. <code>RICE 2 per FARMER.xlsx</code>).
            </p>
        </div>

        <!-- INDIVIDUAL REGISTRATION FORM -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-sm">
            <div class="border-b border-slate-100 pb-4 mb-6">
                <h1 class="text-xl font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-bowl-rice text-emerald-600"></i> Individual Rice Farmer Registration
                </h1>
                <p class="text-xs text-slate-500 mt-1">Fill out all fields matching the official RSBSA field survey masterlist.</p>
            </div>

            <div id="formAlert" class="hidden p-4 mb-6 rounded-2xl text-xs font-semibold whitespace-pre-wrap"></div>

            <form id="riceFarmerForm" class="space-y-8">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="save_rice_farmer">

                <!-- 1. Location & Identification -->
                <div>
                    <h2 class="text-xs font-black uppercase text-emerald-700 tracking-wider mb-4 border-b pb-1">1. Primary Information</h2>
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

                <!-- 2. Personal Details -->
                <div>
                    <h2 class="text-xs font-black uppercase text-emerald-700 tracking-wider mb-4 border-b pb-1">2. Farmer Profile Details</h2>
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
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Birthday</label>
                            <input type="date" name="birthday" required id="birthday" onchange="calculateAge(this.value)" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Gender</label>
                            <select name="gender" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
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
                            <select name="civil_status" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
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
                            <input type="number" step="0.01" name="ave_monthly_income" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. of Household Members</label>
                            <input type="number" name="no_household_members" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. of Children (If Married)</label>
                            <input type="number" name="no_children" value="0" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                    </div>
                </div>

                <!-- 4. Farm Land & Rice Production Metrics -->
                <div>
                    <h2 class="text-xs font-black uppercase text-emerald-700 tracking-wider mb-4 border-b pb-1">4. Rice Production Metrics</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Rice Area (Has)</label>
                            <input type="number" step="0.01" name="rice_area" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Area Planted (Has)</label>
                            <input type="number" step="0.01" name="area_planted" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Area Harvested (Has)</label>
                            <input type="number" step="0.01" name="area_harvested" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ave. Annual Prod. (MT)</label>
                            <input type="number" step="0.01" name="annual_production" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tenurial Status</label>
                            <select name="tenurial_status" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                                <option value="Owner">Owner</option>
                                <option value="Tenant">Tenant</option>
                                <option value="Lessee">Lessee</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Rice Area Status</label>
                            <select name="status_rice_area" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                                <option value="C">C - Cultivated</option>
                                <option value="U">U - Uncultivated</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ecosystem</label>
                            <select name="ecosystem" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                                <option value="I">I - Irrigated</option>
                                <option value="R">R - Rainfed</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Preferred Variety</label>
                            <select name="preferred_variety" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
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
                            <input type="number" name="hand_tractors_count" value="0" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Water Pumps Owned</label>
                            <input type="number" name="water_pumps_count" value="0" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Knapsack Sprayers Owned</label>
                            <input type="number" name="knapsack_sprayers_count" value="0" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Cropping Seasons/Yr</label>
                            <input type="number" name="cropping_season_count" value="1" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        </div>
                    </div>
                </div>

                <!-- 6. Remarks -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Remarks / Remarks Field Note</label>
                    <textarea name="remarks" rows="2" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50"></textarea>
                </div>

                <!-- Submission Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="dashboard.php" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md flex items-center gap-2">
                        <i class="fa-solid fa-save"></i>
                        <span>Save Farmer Registration</span>
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

        // Handle Excel Upload Form Submission
        document.getElementById('bulkUploadForm').addEventListener('submit', function(e) {
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
                    alertBox.innerText = data.message;
                    document.getElementById('bulkUploadForm').reset();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                } else {
                    alertBox.classList.add('bg-red-50', 'text-red-700');
                    alertBox.innerText = data.message || "Bulk upload failed.";
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

        // Handle Individual Registration Form Submission
        document.getElementById('riceFarmerForm').addEventListener('submit', function(e) {
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
                    alertBox.innerText = data.message || "Rice farmer registered successfully.";
                    document.getElementById('riceFarmerForm').reset();
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