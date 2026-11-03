<?php
require_once 'db.php';

$error = "";
$success_msg = $_SESSION['success_msg'] ?? '';
unset($_SESSION['success_msg']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die("Security Exception: CSRF Token mismatch.");
    }

    if (isIpThrottled($pdo)) {
        $error = "Too many failed login attempts (5/5). System access locked for 3 minutes for security.";
    } else {
        $identifier = trim($_POST['identifier'] ?? '');
        $password   = $_POST['password'] ?? '';

        if (empty($identifier) || empty($password)) {
            $error = "Please fill in all mandatory security fields.";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM staff_users WHERE email = ? OR employee_id = ?");
            $stmt->execute([$identifier, $identifier]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                clearFailedAttempts($pdo);
                session_regenerate_id(true);

                $_SESSION['staff_logged_in'] = true;
                $_SESSION['staff_id']        = $user['id'];
                $_SESSION['staff_name']      = $user['full_name'];
                $_SESSION['staff_email']     = $user['email'];
                $_SESSION['staff_role']      = $user['designation'];
                $_SESSION['last_activity']   = time();

                logAudit($pdo, $user['id'], "Successful Back-Office Authentication");

                header("Location: dashboard.php");
                exit();
            } else {
                recordFailedAttempt($pdo);
                logAudit($pdo, null, "Failed login attempt for identifier: " . htmlspecialchars($identifier, ENT_QUOTES, 'UTF-8'));
                $error = "Invalid credentials. Attempt logged for security monitoring.";
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
    <title>OMAS Arteche | Secure Staff Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 text-slate-800 font-sans antialiased min-h-screen flex items-center justify-center p-4 relative">
    <div class="absolute inset-0 bg-gradient-to-br from-emerald-950 via-emerald-900 to-slate-900 opacity-95"></div>
    
    <div class="w-full max-w-md bg-white/95 backdrop-blur-md p-8 rounded-3xl shadow-2xl relative z-10 border border-white/20">
        <!-- Return to Homepage Link (Top) -->
        <div class="mb-4">
            <a href="index.php" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-emerald-700 transition">
                <i class="fa-solid fa-arrow-left"></i> Return to Homepage
            </a>
        </div>

        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-amber-100 rounded-2xl mb-3 text-amber-700 text-2xl shadow-sm">
                <i class="fa-solid fa-user-lock"></i>
            </div>
            <h1 class="text-2xl font-black text-slate-900">Staff Portal Access</h1>
            <p class="text-xs text-slate-500 mt-1">Authorized OMAS Staff Only</p>
        </div>

        <?php if (!empty($success_msg)): ?>
            <div class="mb-4 p-3 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded-r-xl text-xs flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i>
                <span><?= htmlspecialchars($success_msg, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="mb-4 p-3 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-xl text-xs flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="space-y-4" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">

            <input type="text" name="fake_username" style="display:none" aria-hidden="true" tabindex="-1">
            <input type="password" name="fake_password" style="display:none" aria-hidden="true" tabindex="-1">

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email or Employee ID</label>
                <input 
                    type="text" 
                    name="identifier" 
                    id="identifier"
                    required 
                    autocomplete="off"
                    placeholder="OMAS-2026-XXXX" 
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50"
                >
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Password</label>
                <input 
                    type="password" 
                    name="password" 
                    id="password"
                    required 
                    autocomplete="new-password"
                    placeholder="••••••••" 
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 bg-slate-50"
                >
            </div>

            <button type="submit" class="w-full py-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-sm shadow-lg shadow-amber-500/20 transition hover:-translate-y-0.5 mt-2">
                <i class="fa-solid fa-right-to-bracket mr-1"></i> Authenticate Session
            </button>
        </form>

        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
            <span>Need authorization? <a href="register.php" class="font-bold text-emerald-600 hover:underline">Enroll Here</a></span>
            <a href="index.php" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-house"></i> Home</a>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(function() {
                document.getElementById("identifier").value = "";
                document.getElementById("password").value = "";
            }, 50);
        });
    </script>
</body>
</html>