<?php
// Centralized auth helpers (migrated from legacy root auth.php)
// Usage: require_once __DIR__ . '/Helpers/auth.php';

// Ensure config/session is loaded
require_once __DIR__ . '/../Config/config.php';

function current_user() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function login_user_from_row($row) {
    // Prevent session fixation
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'email' => $row['email'],
        'role' => $row['role'] ?? null
    ];
    return $_SESSION['user'];
}

function require_login() {
    if (!isset($_SESSION['user'])) {
        header('Location: /Medicus/login');
        exit;
    }
    return true;
}

function require_role($role) {
    $u = current_user();
    $roles = is_array($role) ? $role : [$role];
    if (!$u) {
        header('Location: /Medicus/login');
        exit;
    }
    if (!in_array($u['role'], $roles)) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
    return true;
}

function logout() {
    // Clear session and cookie
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
    return true;
}

// Backwards-compatible camelCase wrappers
function currentUser() { return current_user(); }
function requireLogin() { return require_login(); }
function requireRole($role) { return require_role($role); }
function loginUserFromRow($row) { return login_user_from_row($row); }
function logoutUser() { return logout(); }
?>