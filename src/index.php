<?php
/**
 * SSH Authentication Webapp - Landing Page
 *
 * Welcome page with links to sign up and account access.
 */

// Include configuration and functions
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Start session for flash messages
if (!isset($_SESSION)) {
    session_start();
}

// Check if user is already authenticated
$authenticated = isAuthenticated();
$userName = null;

if ($authenticated) {
    $userId = getCurrentUserId();
    $user = getUserById($userId);
    $userName = $user ? sanitizeOutput($user['name']) : null;
}

// Get any flash message
$flashMessage = getFlashMessage();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Secure SSH Authentication</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><?php echo APP_NAME; ?></h1>
            <p class="version">Version <?php echo APP_VERSION; ?></p>
        </header>

        <main>
            <?php if ($flashMessage): ?>
                <div class="message info">
                    <?php echo sanitizeOutput($flashMessage); ?>
                </div>
            <?php endif; ?>

            <div class="hero">
                <h2>Secure Authentication with SSH Keys</h2>
                <p>
                    Experience passwordless authentication using your SSH keys.
                    No more remembering complex passwords - authenticate securely
                    with cryptographic key pairs.
                </p>

                <div class="features">
                    <div class="feature">
                        <h3>🔐 SSH Key Security</h3>
                        <p>Use industry-standard SSH keys for authentication</p>
                    </div>
                    <div class="feature">
                        <h3>🚀 Zero-Interaction Login</h3>
                        <p>Automatic authentication once configured</p>
                    </div>
                    <div class="feature">
                        <h3>🛡️ Browser Extension</h3>
                        <p>Seamless integration with ssh-auth-extension</p>
                    </div>
                </div>
            </div>

            <div class="actions">
                <?php if ($authenticated): ?>
                    <div class="welcome-back">
                        <h3>Welcome back, <?php echo $userName; ?>!</h3>
                        <a href="account.php" class="btn btn-primary">View Account</a>
                        <a href="logout.php" class="btn btn-secondary">Logout</a>
                    </div>
                <?php else: ?>
                    <div class="get-started">
                        <h3>Get Started</h3>
                        <p>Create your account with an SSH public key to begin secure authentication.</p>
                        <a href="signup.php" class="btn btn-primary">Create Account</a>
                        <p class="note">
                            <strong>Note:</strong> You must have the ssh-auth-extension installed
                            in your browser to authenticate. <a href="#extension-info">Learn more</a>
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <div id="extension-info" class="extension-info">
                <h3>Browser Extension Required</h3>
                <p>
                    This web application requires the <strong>ssh-auth-extension</strong> browser extension
                    to handle SSH key operations securely. The extension works with:
                </p>
                <ul>
                    <li>Google Chrome</li>
                    <li>Microsoft Edge</li>
                    <li>Brave Browser</li>
                    <li>Other Chromium-based browsers</li>
                </ul>
                <p>
                    <strong>Privacy:</strong> SSH private keys never leave your browser.
                    All cryptographic operations happen locally within the extension.
                </p>
            </div>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> SSH Authentication Webapp. Built with PHP and OpenSSL.</p>
        </footer>
    </div>

    <script src="js/app.js"></script>
</body>
</html>
