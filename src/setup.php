<?php
/**
 * SSH Authentication Webapp - Database Setup
 *
 * Administrative interface for setting up the database schema.
 */

// Include configuration
require_once 'includes/config.php';

// Start session for status messages
if (!isset($_SESSION)) {
    session_start();
}

// Check if setup is already complete
$setupComplete = false;
$message = null;
$messageType = 'info';

try {
    $db = getDbConnection();

    // Check if table exists
    $result = $db->query("SHOW TABLES LIKE '" . TABLE_USERS . "'");
    $setupComplete = $result->num_rows > 0;

} catch (Exception $e) {
    $message = 'Database connection error: ' . $e->getMessage();
    $messageType = 'error';
}

// Handle setup request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup_database'])) {
    if ($setupComplete) {
        $message = 'Database is already set up.';
        $messageType = 'info';
    } else {
        try {
            $db = getDbConnection();

            // Create user_accounts table
            $createTableSQL = "
                CREATE TABLE IF NOT EXISTS " . TABLE_USERS . " (
                    id VARCHAR(255) PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    ssh_public_key TEXT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_name (name),
                    UNIQUE KEY unique_ssh_key (ssh_public_key(255))
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ";

            if ($db->query($createTableSQL)) {
                $message = 'Database setup completed successfully!';
                $messageType = 'success';
                $setupComplete = true;
            } else {
                $message = 'Failed to create database table: ' . $db->error;
                $messageType = 'error';
            }

        } catch (Exception $e) {
            $message = 'Setup error: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get database info for display
$dbInfo = [
    'host' => DB_HOST,
    'database' => DB_NAME,
    'table' => TABLE_USERS,
    'connection' => 'Unknown'
];

try {
    $db = getDbConnection();
    $dbInfo['connection'] = 'Connected';
} catch (Exception $e) {
    $dbInfo['connection'] = 'Failed: ' . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .setup-status {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .status-item {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 10px;
            background: white;
            border-radius: 3px;
        }
        .status-label { font-weight: bold; }
        .status-value {
            font-family: monospace;
            padding: 2px 8px;
            border-radius: 3px;
        }
        .status-connected { background: #d4edda; color: #155724; }
        .status-failed { background: #f8d7da; color: #721c24; }
        .setup-actions { margin-top: 30px; }
        .setup-note {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><?php echo APP_NAME; ?> - Database Setup</h1>
            <nav>
                <a href="index.php">Home</a>
            </nav>
        </header>

        <main>
            <div class="setup-container">
                <h2>Database Setup</h2>

                <?php if ($message): ?>
                    <div class="message <?php echo $messageType; ?>">
                        <?php echo sanitizeOutput($message); ?>
                    </div>
                <?php endif; ?>

                <div class="setup-status">
                    <h3>Current Status</h3>

                    <div class="status-item">
                        <span class="status-label">Database Connection:</span>
                        <span class="status-value <?php echo strpos($dbInfo['connection'], 'Connected') === 0 ? 'status-connected' : 'status-failed'; ?>">
                            <?php echo sanitizeOutput($dbInfo['connection']); ?>
                        </span>
                    </div>

                    <div class="status-item">
                        <span class="status-label">Database Host:</span>
                        <span class="status-value"><?php echo sanitizeOutput($dbInfo['host']); ?></span>
                    </div>

                    <div class="status-item">
                        <span class="status-label">Database Name:</span>
                        <span class="status-value"><?php echo sanitizeOutput($dbInfo['database']); ?></span>
                    </div>

                    <div class="status-item">
                        <span class="status-label">Users Table:</span>
                        <span class="status-value <?php echo $setupComplete ? 'status-connected' : 'status-failed'; ?>">
                            <?php echo $setupComplete ? 'Created (' . sanitizeOutput($dbInfo['table']) . ')' : 'Not Created'; ?>
                        </span>
                    </div>

                    <div class="status-item">
                        <span class="status-label">Setup Status:</span>
                        <span class="status-value <?php echo $setupComplete ? 'status-connected' : 'status-failed'; ?>">
                            <?php echo $setupComplete ? 'Complete' : 'Required'; ?>
                        </span>
                    </div>
                </div>

                <?php if (!$setupComplete): ?>
                    <div class="setup-note">
                        <h4>⚠️ Database Setup Required</h4>
                        <p>
                            The application database table needs to be created before users can register
                            or authenticate. Click the button below to set up the database schema.
                        </p>
                        <p><strong>Note:</strong> This will create the user accounts table with proper indexes and constraints.</p>
                    </div>

                    <div class="setup-actions">
                        <form method="post" action="setup.php">
                            <button type="submit" name="setup_database" class="btn btn-primary">
                                Set Up Database
                            </button>
                        </form>
                    </div>

                <?php else: ?>
                    <div class="message success">
                        <h4>✅ Database Setup Complete</h4>
                        <p>The database is ready for use. You can now:</p>
                        <ul>
                            <li><a href="signup.php">Create user accounts</a></li>
                            <li><a href="index.php">Return to the home page</a></li>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="setup-info">
                    <h3>Database Schema</h3>
                    <p>The setup will create the following table structure:</p>

                    <div class="code-block">
                        <strong>Table: user_accounts</strong><br>
                        <code>
CREATE TABLE user_accounts (<br>
&nbsp;&nbsp;&nbsp;&nbsp;id VARCHAR(255) PRIMARY KEY,<br>
&nbsp;&nbsp;&nbsp;&nbsp;name VARCHAR(255) UNIQUE NOT NULL,<br>
&nbsp;&nbsp;&nbsp;&nbsp;ssh_public_key TEXT NOT NULL,<br>
&nbsp;&nbsp;&nbsp;&nbsp;created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,<br>
&nbsp;&nbsp;&nbsp;&nbsp;UNIQUE KEY unique_ssh_key (ssh_public_key(255))<br>
);
                        </code>
                    </div>

                    <h4>Table Fields</h4>
                    <ul>
                        <li><strong>id</strong>: Unique user identifier</li>
                        <li><strong>name</strong>: Display name (must be unique)</li>
                        <li><strong>ssh_public_key</strong>: SSH public key for authentication</li>
                        <li><strong>created_at</strong>: Account creation timestamp</li>
                    </ul>

                    <h4>Constraints</h4>
                    <ul>
                        <li>Primary key on user ID</li>
                        <li>Unique constraint on display name</li>
                        <li>Unique constraint on SSH public key (first 255 characters)</li>
                    </ul>
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
