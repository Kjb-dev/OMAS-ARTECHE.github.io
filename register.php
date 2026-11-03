<?php
require_once 'db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die("Security Validation Failed. Invalid CSRF Token.");
    }

    $full_name   = trim($_POST['full_name'] ?? '');
    $email       = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $employee_id = trim($_POST['employee_id'] ?? '');
    $designation = trim($_POST['designation'] ?? '');
    $password    = $_POST['password'] ?? '';
    $confirm_pwd = $_POST['confirm_password'] ?? '';

    if (empty($full_name) || empty($email) || empty($employee_id) || empty($designation) || empty($password)) {
        $errors[] = "All required fields must be populated.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid official email address format.";
    }
    if ($password !== $confirm_pwd) {
        $errors[] = "Entered passwords do not match.";
    }
    if (!preg_match('/^(?=.*[0-9])(?=.*[\W_]).{8,}$/', $password)) {
        $errors[] = "Password must be at least 8 characters long and contain at least one number and one special character.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM staff_users WHERE email = ? OR employee_id = ?");
        $stmt->execute([$email, $employee_id]);

        if ($stmt->rowCount() > 0) {
            $errors[] = "Account credential collision: Email or Employee ID is already registered.";
        } else {
            $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
            $hashed_password = password_hash($password, $algo);

            $insert_stmt = $pdo->prepare("INSERT INTO staff_users (full_name, email, employee_id, designation, password) VALUES (?, ?, ?, ?, ?)");
            if ($insert_stmt->execute([$full_name, $email, $employee_id, $designation, $hashed_password])) {
                $newId = $pdo->lastInsertId();
                logAudit($pdo, $newId, "New Staff Account Registered: ID $employee_id");

                $_SESSION['success_msg'] = "Registration complete. Please authenticate with your credentials.";
                header("Location: login.php");
                exit();
            } else {
                $errors[] = "Database write error. Contact IT Department.";
            }
        }
    }
}
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMAS Arteche | Secure Staff Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 text-slate-800 font-sans antialiased min-h-screen flex items-center justify-center p-4 relative overflow-x-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-emerald-950 via-emerald-900 to-slate-900 opacity-95"></div>
    
    <div class="w-full max-w-xl bg-white/95 backdrop-blur-md p-8 rounded-3xl shadow-2xl relative z-10 border border-white/20 my-8">
        <!-- Return to Homepage Link (Top) -->
        <div class="mb-4">
            <a href="index.php" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-emerald-700 transition">
                <i class="fa-solid fa-arrow-left"></i> Return to Homepage
            </a>
        </div>

        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-emerald-100 rounded-2xl mb-3 text-emerald-700 text-2xl shadow-sm">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <h1 class="text-2xl font-black text-slate-900">Official Staff Registration</h1>
            <p class="text-xs text-slate-500 mt-1">LGU Arteche — Municipal Agricultural Services</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-xl text-xs space-y-1">
                <?php foreach ($errors as $error): ?>
                    <p><i class="fa-solid fa-triangle-exclamation mr-1"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" class="space-y-4" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Full Name</label>
                <input type="text" name="full_name" required value="<?= htmlspecialchars($_POST['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. Juan Dela Cruz" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Official Email</label>
                    <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="staff@arteche.gov.ph" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Employee ID</label>
                    <input type="text" name="employee_id" required value="<?= htmlspecialchars($_POST['employee_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="OMAS-2026-XXXX" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Designation</label>
                <select name="designation" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                    <option value="">Select Position</option>
                    <option value="Agricultural Technologist">Agricultural Technologist</option>
                    <option value="RSBSA Encoder">RSBSA Encoder</option>
                    <option value="Fishery Coordinator">Fishery Coordinator</option>
                    <option value="Municipal Agriculturist">Municipal Agriculturist</option>
                    <option value="IT Support / Admin">IT Support / Admin</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Password</label>
                    <input type="password" name="password" required autocomplete="new-password" placeholder="••••••••" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Confirm Password</label>
                    <input type="password" name="confirm_password" required autocomplete="new-password" placeholder="••••••••" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                </div>
            </div>

            <button type="submit" class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-lg shadow-emerald-600/20 transition hover:-translate-y-0.5 mt-2">
                <i class="fa-solid fa-shield-check mr-1"></i> Authorize & Create Account
            </button>
        </form>

        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
            <span>Registered personnel? <a href="login.php" class="font-bold text-emerald-600 hover:underline">Sign In</a></span>
            <a href="index.php" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-house"></i> Home</a>
        </div>
    </div>
</body>
</html>