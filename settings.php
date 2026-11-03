<?php
require_once 'db.php';

if (!isset($_SESSION['staff_logged_in']) || $_SESSION['staff_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$success = "";
$errors = [];

$stmt = $pdo->prepare("SELECT * FROM staff_users WHERE id = ?");
$stmt->execute([$_SESSION['staff_id']]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die("CSRF verification failed.");
    }

    $full_name   = trim($_POST['full_name'] ?? '');
    $designation = trim($_POST['designation'] ?? '');
    $old_pass    = $_POST['old_password'] ?? '';
    $new_pass    = $_POST['new_password'] ?? '';
    $confirm_pass= $_POST['confirm_password'] ?? '';

    if (empty($full_name) || empty($designation)) {
        $errors[] = "Full Name and Designation are required.";
    }

    if (!empty($old_pass) || !empty($new_pass)) {
        if (!password_verify($old_pass, $user['password'])) {
            $errors[] = "Current password check failed.";
        } elseif ($new_pass !== $confirm_pass) {
            $errors[] = "New password confirmation does not match.";
        } elseif (!preg_match('/^(?=.*[0-9])(?=.*[\W_]).{8,}$/', $new_pass)) {
            $errors[] = "New password must be at least 8 characters long with a number and special character.";
        }
    }

    if (empty($errors)) {
        if (!empty($new_pass)) {
            $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
            $hashed = password_hash($new_pass, $algo);
            $updateStmt = $pdo->prepare("UPDATE staff_users SET full_name = ?, designation = ?, password = ? WHERE id = ?");
            $updateStmt->execute([$full_name, $designation, $hashed, $_SESSION['staff_id']]);
        } else {
            $updateStmt = $pdo->prepare("UPDATE staff_users SET full_name = ?, designation = ? WHERE id = ?");
            $updateStmt->execute([$full_name, $designation, $_SESSION['staff_id']]);
        }

        $_SESSION['staff_name'] = $full_name;
        $_SESSION['staff_role'] = $designation;
        logAudit($pdo, $_SESSION['staff_id'], "Staff updated profile settings");
        $success = "Settings updated successfully.";
        
        $stmt->execute([$_SESSION['staff_id']]);
        $user = $stmt->fetch();
    }
}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMAS Arteche | Settings</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 text-slate-100 font-sans antialiased min-h-screen">

    <nav class="bg-slate-800 border-b border-slate-700 px-6 py-4 flex items-center justify-between">
        <a href="dashboard.php" class="text-xs font-bold text-slate-400 hover:text-white transition flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> Return to Dashboard
        </a>
        <span class="text-xs font-bold text-slate-400">Account Control</span>
    </nav>

    <div class="max-w-xl mx-auto my-10 p-8 bg-slate-800 rounded-3xl border border-slate-700 shadow-2xl">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-14 h-14 bg-emerald-600 rounded-2xl flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-user-gear"></i>
            </div>
            <div>
                <h1 class="text-xl font-black">Staff Profile Settings</h1>
                <p class="text-xs text-slate-400">Employee ID: <?= htmlspecialchars($user['employee_id']) ?></p>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="mb-4 p-3 bg-emerald-900/50 border border-emerald-500/50 text-emerald-300 rounded-xl text-xs flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="mb-4 p-3 bg-red-900/50 border border-red-500/50 text-red-300 rounded-xl text-xs space-y-1">
                <?php foreach ($errors as $e): ?>
                    <p><i class="fa-solid fa-triangle-exclamation mr-1"></i> <?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="settings.php" method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Full Name</label>
                <input type="text" name="full_name" required value="<?= htmlspecialchars($user['full_name']) ?>" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Designation</label>
                <input type="text" name="designation" required value="<?= htmlspecialchars($user['designation']) ?>" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="pt-4 border-t border-slate-700">
                <h2 class="text-xs font-bold uppercase text-slate-400 mb-3">Security & Password</h2>
                
                <div class="space-y-3">
                    <div>
                        <label class="block text-[11px] text-slate-400 mb-1">Current Password</label>
                        <input type="password" name="old_password" placeholder="••••••••" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] text-slate-400 mb-1">New Password</label>
                            <input type="password" name="new_password" placeholder="••••••••" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white">
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-400 mb-1">Confirm Password</label>
                            <input type="password" name="confirm_password" placeholder="••••••••" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white">
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 font-bold text-xs shadow-lg transition mt-4">
                Update Account Settings
            </button>
        </form>
    </div>
</body>
</html>