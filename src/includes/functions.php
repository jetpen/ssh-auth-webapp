<?php
/**
 * SSH Authentication Webapp - Utility Functions
 *
 * Common utility functions used throughout the application.
 */

/**
 * Sanitize user input for safe display
 * @param string $input Input string to sanitize
 * @return string Sanitized string
 */
function sanitizeOutput($input) {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format (basic validation)
 * @param string $email Email address to validate
 * @return bool True if valid email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate a cryptographically secure random string
 * @param int $length Length of the random string
 * @return string Random string
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Check if current request is over HTTPS
 * @return bool True if HTTPS
 */
function isHttps() {
    return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
}

/**
 * Redirect to a URL with optional message
 * @param string $url URL to redirect to
 * @param string $message Optional message to display
 */
function redirect($url, $message = null) {
    if ($message) {
        // Store message in session for display after redirect
        if (!isset($_SESSION)) {
            session_start();
        }
        $_SESSION['flash_message'] = $message;
    }

    header("Location: $url");
    exit;
}

/**
 * Get flash message from session
 * @return string|null Flash message or null
 */
function getFlashMessage() {
    if (!isset($_SESSION)) {
        session_start();
    }

    $message = $_SESSION['flash_message'] ?? null;
    unset($_SESSION['flash_message']);
    return $message;
}

/**
 * Check if user is authenticated
 * @return bool True if user has valid session
 */
function isAuthenticated() {
    if (!isset($_SESSION)) {
        session_start();
    }

    return isset($_SESSION['user_id']) &&
           isset($_SESSION['authenticated']) &&
           $_SESSION['authenticated'] === true;
}

/**
 * Get current authenticated user ID
 * @return string|null User ID or null if not authenticated
 */
function getCurrentUserId() {
    if (!isAuthenticated()) {
        return null;
    }

    return $_SESSION['user_id'];
}

/**
 * Require authentication - redirect to login if not authenticated
 */
function requireAuth() {
    if (!isAuthenticated()) {
        // Store current URL for post-login redirect
        $currentUrl = $_SERVER['REQUEST_URI'];
        redirect('auth.php?redirect=' . urlencode($currentUrl));
    }
}



/**
 * Log out current user
 */
function logout() {
    if (!isset($_SESSION)) {
        session_start();
    }

    // Clear session data
    $_SESSION = [];

    // Destroy session
    session_destroy();

    // Clear session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
}

/**
 * Validate SSH public key format (basic validation)
 * @param string $publicKey SSH public key to validate
 * @return bool True if valid format
 */
function isValidSSHPublicKey($publicKey) {
    $key = trim($publicKey);

    // Check for basic SSH key format patterns
    $patterns = [
        '/^ssh-rsa\s+[A-Za-z0-9+\/=]+\s*/',
        '/^ssh-ed25519\s+[A-Za-z0-9+\/=]+\s*/',
        '/^ecdsa-sha2-nistp256\s+[A-Za-z0-9+\/=]+\s*/',
        '/^ssh-dss\s+[A-Za-z0-9+\/=]+\s*/'
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $key)) {
            return true;
        }
    }

    return false;
}

/**
 * Get user agent string for logging
 * @return string User agent string
 */
function getUserAgent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
}

/**
 * Get client IP address
 * @return string Client IP address
 */
function getClientIP() {
    $ipHeaders = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];

    foreach ($ipHeaders as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = $_SERVER[$header];

            // Handle comma-separated IPs (take first one)
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }

            // Validate IP address
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }

    return '127.0.0.1'; // fallback
}
?>
