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
                <span class="text-lg font-black tracking-wide">OMAS ARTECHE <span class="text-xs font-normal text-cyan-400">| Fishery & Fisherfolk Registry Form</span></span>
            </div>
            <a href="dashboard.php" class="bg-slate-800 hover:bg-slate-700 text-xs px-3 py-2 rounded-xl border border-slate-700 transition font-semibold">
                Back to Dashboard
            </a>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full space-y-6">

        <!-- BULK EXCEL UPLOAD SECTION -->
        <div class="bg-gradient-to-r from-cyan-900 to-slate-900 p-6 rounded-3xl text-white shadow-md space-y-4">
            <div class="flex items-center justify-between border-b border-cyan-800/60 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-cyan-500/20 border border-cyan-400/30 flex items-center justify-center text-cyan-400 font-bold">
                        <i class="fa-solid fa-file-excel text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-extrabold">Batch Registration (Excel Masterlist)</h2>
                        <p class="text-xs text-slate-300">Upload fisherfolk survey spreadsheet files directly to import bulk records.</p>
                    </div>
                </div>
            </div>

            <form id="bulkUploadForm" class="flex flex-col sm:flex-row items-center gap-3" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="upload_fishery_farmers_excel">

                <input type="file" name="excel_file" accept=".xlsx, .xls, .csv" required class="block w-full text-xs text-slate-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-cyan-600 file:text-white hover:file:bg-cyan-500 bg-slate-800/80 rounded-xl border border-slate-700 cursor-pointer">
                
                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-black text-xs shadow-md transition flex items-center justify-center gap-2 whitespace-nowrap">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <span>Upload & Register</span>
                </button>
            </form>
            <p class="text-[11px] text-slate-400 italic">
                <i class="fa-solid fa-circle-info mr-1"></i>Supports <strong>.xlsx</strong> files directly matching survey forms (e.g. <code>FISHERY 1 PER BARANGAY_1.xlsx</code>).
            </p>
        </div>

        <!-- INDIVIDUAL REGISTRATION FORM -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-sm">
            <div class="border-b border-slate-100 pb-4 mb-6">
                <h1 class="text-xl font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-fish text-cyan-600"></i> Individual Fisherfolk Registration Entry
                </h1>
                <p class="text-xs text-slate-500 mt-1">Record registration numbers, gear details, vessel statistics, and catch yield by marine category.</p>
            </div>

            <div id="formAlert" class="hidden p-4 mb-6 rounded-2xl text-xs font-semibold whitespace-pre-wrap"></div>

            <form id="fisheryFarmerForm" class="space-y-8">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="save_fishery_farmer">

                <!-- SECTION 1: REGISTRATION & IDENTIFICATION DETAILS -->
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

                    <!-- 5A. FISH & SEASHELLS -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="bg-white p-4 rounded-xl border border-cyan-100 space-y-3">
                            <span class="text-xs font-bold text-cyan-900 uppercase block border-b pb-1">Fish Production</span>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Kind of Fish</label>
                                <input type="text" name="kind_of_fish" placeholder="e.g. Tuna, Galunggong, Maya-Maya" class="w-full px-3 py-2 rounded-lg border text-sm">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">No. of Catch (Kls)</label>
                                    <input type="number" step="0.01" name="fish_catch_kls" value="0.00" class="w-full px-3 py-2 rounded-lg border text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Type of Gears Used</label>
                                    <input type="text" name="fish_gears_used" placeholder="e.g. Hook & Line, Net" class="w-full px-3 py-2 rounded-lg border text-sm">
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-4 rounded-xl border border-cyan-100 space-y-3">
                            <span class="text-xs font-bold text-cyan-900 uppercase block border-b pb-1">Seashells & Invertebrates</span>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Kind of Seashells</label>
                                <input type="text" name="kind_of_seashells" placeholder="e.g. Tahong, Talaba, Anahaw" class="w-full px-3 py-2 rounded-lg border text-sm">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">No. of Catch (Kls)</label>
                                    <input type="number" step="0.01" name="seashells_catch_kls" value="0.00" class="w-full px-3 py-2 rounded-lg border text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Type of Gears Used</label>
                                    <input type="text" name="seashells_gears_used" placeholder="e.g. Manual Gathering, Dredge" class="w-full px-3 py-2 rounded-lg border text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 5B. SEAWEEDS & SEA CUCUMBER -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="bg-white p-4 rounded-xl border border-cyan-100 space-y-3">
                            <span class="text-xs font-bold text-cyan-900 uppercase block border-b pb-1">Seaweeds Farming</span>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Seaweeds Variety</label>
                                <input type="text" name="seaweeds_variety" placeholder="e.g. Eucheuma Cottonii, Kappaphycus" class="w-full px-3 py-2 rounded-lg border text-sm">
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
                                <input type="text" name="sea_cucumber_variety" placeholder="e.g. Balatan, Sandfish" class="w-full px-3 py-2 rounded-lg border text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">No. of Catch (Kls)</label>
                                <input type="number" step="0.01" name="sea_cucumber_catch_kls" value="0.00" class="w-full px-3 py-2 rounded-lg border text-sm">
                            </div>
                        </div>
                    </div>

                    <!-- 5C. CRABS (BLUE SWIMMING & MANGROVE) -->
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
                    <a href="dashboard.php" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-cyan-700 hover:bg-cyan-800 shadow-md flex items-center gap-2">
                        <i class="fa-solid fa-save"></i>
                        <span>Save Fishery Profile</span>
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
                    alertBox.innerText = data.message || "Fishery excel masterlist imported successfully.";
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
        document.getElementById('fisheryFarmerForm').addEventListener('submit', function(e) {
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
                    alertBox.innerText = data.message || "Fishery farmer profile saved successfully.";
                    document.getElementById('fisheryFarmerForm').reset();
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