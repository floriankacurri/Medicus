<?php
// Usage (PowerShell): php scripts/create_admin.php --email=admin@example.com --name="Admin" --password=Secret123
$opts = getopt('', ['email:', 'name::', 'password::']);
if (!isset($opts['email']) || !isset($opts['password'])) {
    echo "Usage: php scripts/create_admin.php --email=admin@example.com --password=Secret123 [--name='Admin']\n";
    exit(1);
}
$email = $opts['email'];
$name = $opts['name'] ?? 'Administrator';
$password = $opts['password'];

// Load app config to get DB connection
require_once __DIR__ . '/../app/Config/config.php';
// $mysqli is created by app/Config/database.php
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    echo "Database connection not available. Check app/Config/database.php\n";
    exit(1);
}

// Ensure users.role enum supports 'admin'
$colRes = $mysqli->query("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'role' LIMIT 1");
if ($colRes) {
    $col = $colRes->fetch_assoc();
    $colType = $col['COLUMN_TYPE'] ?? '';
    if (strpos($colType, "'admin'") === false) {
        echo "'admin' not present in users.role enum — attempting to alter the table to add 'admin'...\n";
        // Add 'admin' to enum list; keep existing values and append admin
        // We will derive current enum values and create a new enum including 'admin'
        preg_match_all("/'([^']+)'/", $colType, $matches);
        $vals = $matches[1] ?? [];
        if (!in_array('admin', $vals)) $vals[] = 'admin';
        $newEnum = "ENUM('" . implode("','", $vals) . "') NOT NULL DEFAULT 'patient'";
        $alterSql = "ALTER TABLE users MODIFY role $newEnum";
        if ($mysqli->query($alterSql)) {
            echo "Users.role enum altered to include 'admin'.\n";
        } else {
            echo "Failed to alter users.role: " . $mysqli->error . "\n";
            echo "Please run the following SQL manually and re-run this script:\n";
            echo $alterSql . "\n";
            exit(1);
        }
    }
}

// Check existing
$stmt = $mysqli->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
if ($res) {
    echo "A user with email {$email} already exists (id: {$res['id']}).\n";
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $mysqli->prepare('INSERT INTO users (name, email, `password`, role, created_at) VALUES (?, ?, ?, ?, NOW())');
$role = 'admin';
$stmt->bind_param('ssss', $name, $email, $hash, $role);
if ($stmt->execute()) {
    echo "Admin user created: {$email}\n";
    exit(0);
} else {
    echo "Failed to create admin user: " . $mysqli->error . "\n";
    exit(1);
}
?>
