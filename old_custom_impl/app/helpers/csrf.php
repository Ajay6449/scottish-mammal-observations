<?php
/**
 * Cross-Site Request Forgery (CSRF) Protection Helper
 */

require_once __DIR__ . '/auth.php';

/**
 * Generates a cryptographically secure CSRF token, stores it in the session, and returns it.
 * 
 * @return string
 */
function generateCSRFToken(): string {
    secureSessionStart();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validates a submitted CSRF token against the token stored in the session.
 * 
 * @param string|null $token The submitted token
 * @return bool True if valid, false otherwise
 */
function validateCSRFToken(?string $token): bool {
    secureSessionStart();
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Outputs a hidden CSRF token input field for forms.
 * 
 * @return string
 */
function csrfField(): string {
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}
