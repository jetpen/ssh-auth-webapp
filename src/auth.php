<?php
/**
 * SSH Authentication Webapp - Authentication Handler
 *
 * Handles the SSH key-based authentication challenge-response protocol.
 */

// Include configuration and functions
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Start session
if (!isset($_SESSION)) {
    session_start();
}

// Get redirect URL from query parameter
$redirectUrl = $_GET['redirect'] ?? 'account.php';

// Check if user is already authenticated
if (isAuthenticated()) {
    redirect($redirectUrl, 'You are already authenticated.');
}

// Handle form submission with authentication response
$message = null;
$userId = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = trim($_POST['user_id'] ?? '');
    $response = trim($_POST['ssh_auth_response'] ?? '');
    $challenge = trim($_POST['challenge'] ?? '');

    if (empty($userId) || empty($response)) {
        $message = 'Authentication data is missing.';
    } else {
        // Attempt authentication
        if (authenticateUser($userId, $response)) {
            // Success - redirect to intended page
            redirect($redirectUrl, 'Authentication successful!');
        } else {
            // Failed - clear any stored challenge
            clearAuthChallenge();
            $message = 'Authentication failed. Please try again.';
        }
    }
}

// If we have a user_id in GET parameters, try to create a challenge
$challengeData = null;
if (isset($_GET['user_id']) && empty($message)) {
    $requestedUserId = trim($_GET['user_id']);

    // Check if user exists
    $user = getUserById($requestedUserId);
    if ($user) {
        // Create authentication challenge
        $challengeData = createAuthChallenge($requestedUserId);
        if ($challengeData) {
            // Store challenge in session for later verification
            storeAuthChallenge($challengeData);
            $userId = $requestedUserId;
        } else {
            $message = 'Failed to create authentication challenge.';
        }
    } else {
        $message = 'User not found.';
    }
}

// If no specific user requested, show user selection form
$userSelectionMode = (!$challengeData && !$userId && empty($message));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authenticate - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><?php echo APP_NAME; ?></h1>
            <nav>
                <a href="index.php">Home</a>
            </nav>
        </header>

        <main>
            <div class="auth-container">
                <h2>SSH Key Authentication</h2>

                <?php if ($message): ?>
                    <div class="message error">
                        <?php echo sanitizeOutput($message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($userSelectionMode): ?>
                    <!-- User Selection Form -->
                    <div class="user-selection">
                        <p>Select your account to authenticate:</p>
                        <form method="get" action="auth.php" class="auth-form">
                            <input type="hidden" name="redirect" value="<?php echo sanitizeOutput($redirectUrl); ?>">

                            <div class="form-group">
                                <label for="user_id">User ID</label>
                                <input type="text" id="user_id" name="user_id" required
                                       placeholder="Enter your user ID">
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Continue</button>
                                <a href="signup.php" class="btn btn-secondary">Create Account</a>
                            </div>
                        </form>
                    </div>

                <?php elseif ($challengeData): ?>
                    <!-- Authentication Challenge Form -->
                    <div class="challenge-form">
                        <div class="challenge-info">
                            <h3>Authenticating as: <?php echo sanitizeOutput($challengeData['user_id']); ?></h3>
                            <p>
                                Your browser extension should automatically detect this authentication challenge
                                and handle the SSH key operations.
                            </p>
                        </div>

                        <form method="post" action="auth.php" class="auth-form" id="auth-challenge">
                            <input type="hidden" name="user_id" value="<?php echo sanitizeOutput($userId); ?>">
                            <input type="hidden" name="challenge" value="<?php echo sanitizeOutput($challengeData['challenge']); ?>">
                            <input type="hidden" name="redirect" value="<?php echo sanitizeOutput($redirectUrl); ?>">

                            <!-- Hidden inputs that extension will populate -->
                            <input type="hidden" name="ssh_auth_response" id="ssh_auth_response" value="">

                            <!-- Challenge data for extension (hidden but accessible via DOM) -->
                            <div style="display: none;" data-ssh-challenge='<?php
                                echo json_encode([
                                    "type" => "ssh",
                                    "challenge" => $challengeData['encrypted_challenge'],
                                    "algorithm" => "ssh-rsa", // TODO: detect from key
                                    "publicKey" => $challengeData['public_key']
                                ]);
                            ?>'></div>

                            <div class="challenge-display">
                                <h4>Authentication Challenge</h4>
                                <div class="challenge-code">
                                    <code><?php echo sanitizeOutput(substr($challengeData['challenge'], 0, 50)); ?>...</code>
                                </div>
                                <p class="challenge-note">
                                    This challenge has been encrypted with your SSH public key.
                                    Your browser extension will decrypt it and provide a response.
                                </p>
                            </div>

                            <div class="extension-status">
                                <div id="extension-waiting" class="status-message">
                                    <p>🔄 Waiting for browser extension to process authentication...</p>
                                </div>
                                <div id="extension-ready" class="status-message" style="display: none;">
                                    <p>✅ Extension ready. Submitting authentication...</p>
                                </div>
                                <div id="extension-error" class="status-message error" style="display: none;">
                                    <p>❌ Extension error. Please check that ssh-auth-extension is installed and configured.</p>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                                    Complete Authentication
                                </button>
                                <a href="auth.php" class="btn btn-secondary">Try Different Account</a>
                            </div>
                        </form>
                    </div>

                <?php endif; ?>

                <div class="auth-help">
                    <h3>Authentication Process</h3>
                    <ol>
                        <li>Enter your User ID</li>
                        <li>Server generates encrypted challenge</li>
                        <li>Browser extension decrypts with your SSH private key</li>
                        <li>Extension signs response and submits</li>
                        <li>Server verifies signature and authenticates you</li>
                    </ol>

                    <div class="troubleshooting">
                        <h4>Troubleshooting</h4>
                        <ul>
                            <li>Make sure ssh-auth-extension is installed</li>
                            <li>Verify extension is configured with your SSH private key</li>
                            <li>Check that you're using a Chromium-based browser</li>
                            <li>Ensure HTTPS is enabled for security</li>
                        </ul>
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
        // Handle extension communication for authentication
        document.addEventListener('DOMContentLoaded', function() {
            const authForm = document.getElementById('auth-challenge');
            const submitBtn = document.getElementById('submitBtn');
            const responseInput = document.getElementById('ssh_auth_response');

            if (authForm && responseInput) {
                // Check for extension response every second
                const checkInterval = setInterval(function() {
                    if (responseInput.value) {
                        // Extension has provided response
                        clearInterval(checkInterval);
                        document.getElementById('extension-waiting').style.display = 'none';
                        document.getElementById('extension-ready').style.display = 'block';
                        submitBtn.disabled = false;

                        // Auto-submit after short delay
                        setTimeout(function() {
                            authForm.submit();
                        }, 1000);
                    }
                }, 1000);

                // Timeout after 30 seconds
                setTimeout(function() {
                    clearInterval(checkInterval);
                    if (!responseInput.value) {
                        document.getElementById('extension-waiting').style.display = 'none';
                        document.getElementById('extension-error').style.display = 'block';
                    }
                }, 30000);
            }
        });
    </script>
</body>
</html>
