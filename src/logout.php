<?php
/**
 * SSH Authentication Webapp - Logout Handler
 *
 * Handles user logout and session cleanup.
 */

// Include configuration and functions
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Start session
if (!isset($_SESSION)) {
    session_start();
}

// Check if this is a test authentication request
$testAuth = isset($_GET['test']) && $_GET['test'] === '1';

// Log out the user
logout();

// Set logout message
$message = $testAuth ?
    'Logged out for testing. You can now test authentication again.' :
    'You have been successfully logged out.';

// Redirect back to home page
redirect('index.php', $message);
?>
