<?php
/**
 * SSH Authentication Webapp - Account Summary
 *
 * Protected page showing authenticated user account information.
 */

// Include configuration and functions
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Require authentication
requireAuth();

// Get user information
$userId = getCurrentUserId();
$user = getUserById($userId);

if (!$user) {
    logout();
    redirect('index.php', 'Account not found. Please log in again.');
}

// Get flash message if any
$flashMessage = getFlashMessage();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><?php echo APP_NAME; ?></h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="logout.php">Logout</a>
            </nav>
        </header>

        <main>
            <?php if ($flashMessage): ?>
                <div class="message info">
                    <?php echo sanitizeOutput($flashMessage); ?>
                </div>
            <?php endif; ?>

            <div class="account-container">
                <h2>Your Account</h2>

                <div class="account-info">
                    <div class="info-section">
                        <h3>Account Details</h3>
                        <dl class="account-details">
                            <dt>User ID:</dt>
                            <dd><?php echo sanitizeOutput($user['id']); ?></dd>

                            <dt>Display Name:</dt>
                            <dd><?php echo sanitizeOutput($user['name']); ?></dd>

                            <dt>Account Created:</dt>
                            <dd><?php echo date('F j, Y \a\t g:i A', strtotime($user['created_at'])); ?></dd>

                            <dt>SSH Key Type:</dt>
                            <dd><?php
                                $keyType = 'Unknown';
                                $publicKey = trim($user['ssh_public_key']);
                                if (strpos($publicKey, 'ssh-rsa') === 0) {
                                    $keyType = 'RSA';
                                } elseif (strpos($publicKey, 'ssh-ed25519') === 0) {
                                    $keyType = 'Ed25519';
                                } elseif (strpos($publicKey, 'ecdsa-sha2-nistp256') === 0) {
                                    $keyType = 'ECDSA P-256';
                                } elseif (strpos($publicKey, 'ssh-dss') === 0) {
                                    $keyType = 'DSA';
                                }
                                echo $keyType;
                            ?></dd>
                        </dl>
                    </div>

                    <div class="info-section">
                        <h3>SSH Public Key</h3>
                        <div class="key-display">
                            <code><?php echo sanitizeOutput($user['ssh_public_key']); ?></code>
                        </div>
                        <p class="key-note">
                            <strong>Security Note:</strong> This is your public key, which is safe to share.
                            Your private key remains secure in your SSH client and browser extension.
                        </p>
                    </div>

                    <div class="info-section">
                        <h3>Session Information</h3>
                        <dl class="session-details">
                            <dt>Session Started:</dt>
                            <dd><?php echo date('F j, Y \a\t g:i A', $_SESSION['auth_time'] ?? time()); ?></dd>

                            <dt>IP Address:</dt>
                            <dd><?php echo sanitizeOutput(getClientIP()); ?></dd>

                            <dt>User Agent:</dt>
                            <dd><?php echo sanitizeOutput(substr(getUserAgent(), 0, 100)); ?><?php echo strlen(getUserAgent()) > 100 ? '...' : ''; ?></dd>
                        </dl>
                    </div>
                </div>

                <div class="account-actions">
                    <h3>Account Actions</h3>
                    <div class="action-buttons">
                        <a href="logout.php" class="btn btn-primary">Logout</a>
                        <button onclick="testAuthentication()" class="btn btn-secondary">Test Authentication</button>
                    </div>
                </div>

                <div class="security-info">
                    <h3>Security Information</h3>
                    <div class="security-notes">
                        <div class="security-note">
                            <h4>🔐 How Authentication Works</h4>
                            <p>
                                When you access protected pages, the server generates a random challenge
                                encrypted with your SSH public key. Your browser extension decrypts this
                                challenge using your private key and sends back a signature for verification.
                            </p>
                        </div>

                        <div class="security-note">
                            <h4>🛡️ Your Security</h4>
                            <ul>
                                <li>Private keys never leave your browser</li>
                                <li>All communications are encrypted (HTTPS)</li>
                                <li>Sessions expire after <?php echo SESSION_LIFETIME / 3600; ?> hours</li>
                                <li>Server only stores your public key</li>
                            </ul>
                        </div>

                        <div class="security-note">
                            <h4>📱 Browser Extension</h4>
                            <p>
                                Authentication requires the ssh-auth-extension. Make sure it's installed
                                and configured with your SSH private key for seamless login.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> SSH Authentication Webapp. Built with PHP and OpenSSL.</p>
        </footer>
    </div>

    <script src="js/app.js"></script>
    <script>
        function testAuthentication() {
            // Simple test to verify authentication is working
            if (confirm('This will log you out and redirect to test authentication. Continue?')) {
                window.location.href = 'logout.php?test=1';
            }
        }
    </script>
</body>
</html>
