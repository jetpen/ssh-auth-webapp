<?php
/**
 * SSH Authentication Webapp - Account Creation
 *
 * Form for creating new user accounts with SSH public keys.
 */

// Include configuration and functions
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Start session
if (!isset($_SESSION)) {
    session_start();
}

// Redirect if already authenticated
if (isAuthenticated()) {
    redirect('account.php', 'You are already logged in.');
}

// Handle form submission
$message = null;
$messageType = 'error';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = trim($_POST['user_id'] ?? '');
    $userName = trim($_POST['user_name'] ?? '');
    $sshPublicKey = trim($_POST['ssh_public_key'] ?? '');

    // Validate input
    $errors = [];

    if (empty($userId)) {
        $errors[] = 'User ID is required';
    } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $userId)) {
        $errors[] = 'User ID must contain only letters, numbers, underscores, and hyphens';
    }

    if (empty($userName)) {
        $errors[] = 'User name is required';
    }

    if (empty($sshPublicKey)) {
        $errors[] = 'SSH public key is required';
    } elseif (!isValidSSHPublicKey($sshPublicKey)) {
        $errors[] = 'Invalid SSH public key format';
    }

    if (empty($errors)) {
        try {
            $db = getDbConnection();

            // Check for existing user ID
            $stmt = $db->prepare('SELECT id FROM ' . TABLE_USERS . ' WHERE id = ?');
            $stmt->bind_param('s', $userId);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $errors[] = 'User ID already exists';
            }
            $stmt->close();

            // Check for existing user name
            $stmt = $db->prepare('SELECT id FROM ' . TABLE_USERS . ' WHERE name = ?');
            $stmt->bind_param('s', $userName);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $errors[] = 'User name already exists';
            }
            $stmt->close();

            // Check for existing SSH key
            $stmt = $db->prepare('SELECT id FROM ' . TABLE_USERS . ' WHERE ssh_public_key = ?');
            $stmt->bind_param('s', $sshPublicKey);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $errors[] = 'SSH public key is already registered';
            }
            $stmt->close();

            if (empty($errors)) {
                // Create the account
                $stmt = $db->prepare('INSERT INTO ' . TABLE_USERS . ' (id, name, ssh_public_key) VALUES (?, ?, ?)');
                $stmt->bind_param('sss', $userId, $userName, $sshPublicKey);

                if ($stmt->execute()) {
                    $message = 'Account created successfully! You can now authenticate using your SSH key.';
                    $messageType = 'success';

                    // Clear form data
                    $userId = $userName = $sshPublicKey = '';
                } else {
                    $errors[] = 'Failed to create account. Please try again.';
                }

                $stmt->close();
            }

        } catch (Exception $e) {
            error_log('Signup error: ' . $e->getMessage());
            $errors[] = 'An error occurred. Please try again.';
        }
    }

    if (!empty($errors)) {
        $message = implode('<br>', $errors);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - <?php echo APP_NAME; ?></title>
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
            <div class="form-container">
                <h2>Create Your Account</h2>

                <?php if ($message): ?>
                    <div class="message <?php echo $messageType; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="signup.php" class="auth-form">
                    <div class="form-group">
                        <label for="user_id">User ID</label>
                        <input type="text" id="user_id" name="user_id"
                               value="<?php echo sanitizeOutput($userId ?? ''); ?>" required>
                        <small>Unique identifier (letters, numbers, underscores, hyphens only)</small>
                    </div>

                    <div class="form-group">
                        <label for="user_name">Display Name</label>
                        <input type="text" id="user_name" name="user_name"
                               value="<?php echo sanitizeOutput($userName ?? ''); ?>" required>
                        <small>Your display name (must be unique)</small>
                    </div>

                    <div class="form-group">
                        <label for="ssh_public_key">SSH Public Key</label>
                        <textarea id="ssh_public_key" name="ssh_public_key" rows="3" required
                                  placeholder="ssh-rsa AAAAB3NzaC1yc2EAAAA..."><?php echo sanitizeOutput($sshPublicKey ?? ''); ?></textarea>
                        <small>Paste your SSH public key (starts with ssh-rsa, ssh-ed25519, etc.)</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Create Account</button>
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>

                <div class="help-section">
                    <h3>How to get your SSH public key:</h3>
                    <div class="code-block">
                        <strong>On Linux/Mac:</strong><br>
                        <code>cat ~/.ssh/id_rsa.pub</code><br><br>

                        <strong>On Windows (PowerShell):</strong><br>
                        <code>Get-Content ~/.ssh/id_rsa.pub</code><br><br>

                        <strong>If you don't have SSH keys:</strong><br>
                        <code>ssh-keygen -t rsa -b 4096 -C "your-email@example.com"</code>
                    </div>

                    <p><strong>Important:</strong> Never share your private key file (usually id_rsa). Only paste the .pub file contents.</p>
                </div>
            </div>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> SSH Authentication Webapp. Built with PHP and OpenSSL.</p>
        </footer>
    </div>

    <script src="js/app.js"></script>
</body>
</html>
