<?php
// Enforce Secure Cookie Attributes
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');
    // Uncomment on HTTPS:
    // ini_set('session.cookie_secure', 1);
    session_start();
}

$host = 'localhost';
$dbname = 'omas_arteche';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Database connection failure. Please contact system administrator.");
}

// CSRF Token Helpers
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Audit Trail Logging
function logAudit($pdo, $userId, $action) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
    $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, ip_address, user_agent) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $action, $ip, $ua]);
}

// Brute-Force Rate Limiting (5 Attempts -> 3 Minutes Lockout)
function isIpThrottled($pdo) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $timeLimit = time() - (3 * 60); // 3-minute window (fixed syntax)
    
    // Clean old records
    $clearStmt = $pdo->prepare("DELETE FROM login_attempts WHERE attempt_time < ?");
    $clearStmt->execute([$timeLimit]);

    // Count recent failed attempts
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempt_time >= ?");
    $stmt->execute([$ip, $timeLimit]);
    return $stmt->fetchColumn() >= 5;
}

function recordFailedAttempt($pdo) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $stmt = $pdo->prepare("INSERT INTO login_attempts (ip_address, attempt_time) VALUES (?, ?)");
    $stmt->execute([$ip, time()]);
}

function clearFailedAttempts($pdo) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
    $stmt->execute([$ip]);
}
?>