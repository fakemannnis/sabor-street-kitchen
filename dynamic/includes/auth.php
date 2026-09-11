<?php
/**
 * Authentication & role-based access control helpers.
 */

function start_secure_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'httponly' => true,   // JS can't read the session cookie
            'samesite' => 'Lax',  // basic CSRF mitigation on cross-site requests
        ]);
        session_start();
    }
}

function current_user(): ?array {
    start_secure_session();
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool {
    return current_user() !== null;
}

function is_admin(): bool {
    $u = current_user();
    return $u !== null && $u['role'] === 'admin';
}

/** Redirects guests to the login page, preserving where they were headed. */
function require_login(): void {
    if (!is_logged_in()) {
        $redirect = urlencode($_SERVER['REQUEST_URI'] ?? 'index.php');
        // Pages inside /admin/ are one directory deeper than the site root,
        // so the relative link back to login.php needs a '../' prefix there.
        $prefix = str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') ? '../' : '';
        header('Location: ' . $prefix . 'login.php?redirect=' . $redirect);
        exit;
    }
}

/** Redirects non-admins away from admin-only pages. */
function require_admin(): void {
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        set_flash('error', 'You do not have permission to view that page.');
        header('Location: ../index.php');
        exit;
    }
}

function login_user(array $userRow): void {
    start_secure_session();
    // Regenerate the session ID on privilege change to prevent session fixation.
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id'    => (int) $userRow['id'],
        'name'  => $userRow['full_name'],
        'email' => $userRow['email'],
        'role'  => $userRow['role'],
    ];
}

function logout_user(): void {
    start_secure_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie('PHPSESSID', '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

/** Simple one-shot flash-message system for post-redirect-get feedback. */
function set_flash(string $type, string $message): void {
    start_secure_session();
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array {
    start_secure_session();
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

/** CSRF token helpers,one token per session, checked on every state-changing form. */
function csrf_token(): string {
    start_secure_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_check(): bool {
    start_secure_session();
    $sent = $_POST['csrf_token'] ?? '';
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $sent);
}
