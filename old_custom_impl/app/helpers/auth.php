<?php
/**
 * Authentication and Session Helper
 * Manages user sessions, login state, and role checks.
 */

// Start session securely
function secureSessionStart(): void {
    if (session_status() === PHP_SESSION_NONE) {
        // Enforce cookie security policies
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_only_cookies', '1');
        
        // If HTTPS is active, require secure cookies
        $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        
        session_start();
    }
}

/**
 * Log in a user and regenerate session ID to prevent session fixation.
 * 
 * @param array $user Database user row
 */
function loginUser(array $user): void {
    secureSessionStart();
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['last_activity'] = time();
}

/**
 * Log out the user and destroy the session.
 */
function logoutUser(): void {
    secureSessionStart();
    
    // Clear session variables
    $_SESSION = [];
    
    // Delete session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * Check if the user is authenticated.
 * 
 * @return bool
 */
function isLoggedIn(): bool {
    secureSessionStart();
    
    // Handle session timeout after 30 minutes of inactivity
    $timeout = 1800; // 30 minutes
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
        logoutUser();
        return false;
    }
    
    if (isset($_SESSION['user_id'])) {
        $_SESSION['last_activity'] = time(); // Refresh activity timestamp
        return true;
    }
    
    return false;
}

/**
 * Check if the logged-in user is an administrator.
 * 
 * @return bool
 */
function isAdmin(): bool {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Require administrator access or redirect to login.
 */
function requireAdmin(): void {
    if (!isAdmin()) {
        // Store requested URL to redirect back after login
        secureSessionStart();
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        
        // Redirect to login page
        // Use relative path back to login
        header("Location: /login.php");
        exit();
    }
}
