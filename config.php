<?php
// Database configuration
//
// SECURITY NOTE: if this project's GitHub repo is public, do not commit real
// production (e.g. InfinityFree) credentials here. The getenv() calls below
// let you override these values from server environment variables instead,
// so config.php itself can stay safe to commit with just the local defaults.
$dsn    = getenv('DB_DSN')  ?: 'mysql:host=localhost:3306;dbname=day_care;charset=utf8mb4';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';

try {
    $conn = new PDO($dsn, $dbUser, $dbPass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // use real prepared statements
} catch (PDOException $err) {
    // Previously: die("System Error: ... " . $err->getMessage());
    // That printed raw connection details (host, driver, sometimes credentials
    // hints) straight to the browser. Now the detail is logged server-side only.
    error_log('Database connection failed: ' . $err->getMessage());
    die("System Error: Could not connect to the database. Please try again shortly.");
}

// Secure random ID generator (used for tokens/filenames elsewhere in the project)
function generateSecureId($length = 20) {
    return substr(bin2hex(random_bytes($length)), 0, $length);
}
?>
