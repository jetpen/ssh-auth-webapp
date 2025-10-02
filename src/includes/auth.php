<?php
/**
 * SSH Authentication Webapp - Authentication Logic
 *
 * Core authentication functions for SSH key-based challenge-response protocol.
 */

/**
 * Generate a random authentication challenge
 * @return string Random challenge string
 */
function generateAuthChallenge() {
    // Generate a cryptographically secure random challenge
    // Challenge should be long enough to prevent brute force but not too long
    return generateRandomString(64);
}

/**
 * Encrypt challenge with user's SSH public key
 * @param string $challenge Raw challenge string
 * @param string $publicKey User's SSH public key
 * @return string Base64-encoded encrypted challenge
 * @throws Exception If encryption fails
 */
function encryptChallenge($challenge, $publicKey) {
    // Parse the SSH public key to extract the actual key material
    $parsedKey = parseSSHPublicKey($publicKey);
    if (!$parsedKey) {
        throw new Exception('Invalid SSH public key format');
    }

    // For RSA keys, we can use OpenSSL to encrypt
    if ($parsedKey['type'] === 'ssh-rsa') {
        $encrypted = openssl_public_encrypt($challenge, $encryptedData, $parsedKey['key']);
        if (!$encrypted) {
            throw new Exception('Failed to encrypt challenge with public key');
        }
        return base64_encode($encryptedData);
    }

    // For other key types, return base64-encoded challenge for now
    // TODO: Implement proper encryption for ECDSA and Ed25519 keys
    return base64_encode($challenge);
}

/**
 * Verify authentication response against original challenge
 * @param string $challenge Original challenge string
 * @param string $response User's response (signature)
 * @param string $publicKey User's SSH public key
 * @return bool True if response is valid
 */
function verifyAuthResponse($challenge, $response, $publicKey) {
    try {
        // Decode the response (base64 signature)
        $signature = base64_decode($response);
        if ($signature === false) {
            return false;
        }

        // Parse the SSH public key
        $parsedKey = parseSSHPublicKey($publicKey);
        if (!$parsedKey) {
            return false;
        }

        // For RSA keys, verify signature
        if ($parsedKey['type'] === 'ssh-rsa') {
            $verified = openssl_verify($challenge, $signature, $parsedKey['key'], OPENSSL_ALGO_SHA256);
            return $verified === 1;
        }

        // For other key types, placeholder verification
        // TODO: Implement proper signature verification for ECDSA and Ed25519
        return strlen($signature) > 0; // Basic check for now

    } catch (Exception $e) {
        error_log('Authentication verification error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Parse SSH public key and extract key material
 * @param string $publicKey SSH public key string
 * @return array|null Parsed key data or null if invalid
 */
function parseSSHPublicKey($publicKey) {
    $key = trim($publicKey);

    // Basic parsing for different SSH key types
    if (preg_match('/^ssh-rsa\s+([A-Za-z0-9+\/=]+)(\s+.*)?$/', $key, $matches)) {
        $keyData = base64_decode($matches[1]);
        if ($keyData === false) {
            return null;
        }

        // Convert binary key data to PEM format for OpenSSL
        $pemKey = "-----BEGIN PUBLIC KEY-----\n" .
                 chunk_split(base64_encode($keyData), 64, "\n") .
                 "-----END PUBLIC KEY-----";

        return [
            'type' => 'ssh-rsa',
            'key' => $pemKey,
            'raw' => $keyData
        ];
    }

    // For other key types, return basic info for now
    if (preg_match('/^(ssh-ed25519|ecdsa-sha2-nistp256|ssh-dss)\s+([A-Za-z0-9+\/=]+)/', $key, $matches)) {
        return [
            'type' => $matches[1],
            'key' => $key,
            'raw' => base64_decode($matches[2])
        ];
    }

    return null;
}

/**
 * Get user by ID from database
 * @param string $userId User ID
 * @return array|null User data or null if not found
 */
function getUserById($userId) {
    try {
        $db = getDbConnection();
        $stmt = $db->prepare('SELECT id, name, ssh_public_key, created_at FROM ' . TABLE_USERS . ' WHERE id = ?');
        $stmt->bind_param('s', $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $stmt->close();
        return $user;

    } catch (Exception $e) {
        error_log('Database error in getUserById: ' . $e->getMessage());
        return null;
    }
}

/**
 * Get user by SSH public key
 * @param string $publicKey SSH public key
 * @return array|null User data or null if not found
 */
function getUserByPublicKey($publicKey) {
    try {
        $db = getDbConnection();
        $stmt = $db->prepare('SELECT id, name, ssh_public_key, created_at FROM ' . TABLE_USERS . ' WHERE ssh_public_key = ?');
        $stmt->bind_param('s', $publicKey);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $stmt->close();
        return $user;

    } catch (Exception $e) {
        error_log('Database error in getUserByPublicKey: ' . $e->getMessage());
        return null;
    }
}

/**
 * Create authentication challenge for user
 * @param string $userId User ID
 * @return array|null Challenge data or null if user not found
 */
function createAuthChallenge($userId) {
    $user = getUserById($userId);
    if (!$user) {
        return null;
    }

    $challenge = generateAuthChallenge();

    try {
        $encryptedChallenge = encryptChallenge($challenge, $user['ssh_public_key']);

        return [
            'user_id' => $userId,
            'challenge' => $challenge,
            'encrypted_challenge' => $encryptedChallenge,
            'public_key' => $user['ssh_public_key'],
            'timestamp' => time()
        ];

    } catch (Exception $e) {
        error_log('Failed to create auth challenge: ' . $e->getMessage());
        return null;
    }
}

/**
 * Authenticate user with challenge response
 * @param string $userId User ID
 * @param string $response Authentication response
 * @return bool True if authentication successful
 */
function authenticateUser($userId, $response) {
    // Get challenge from session (should be stored during challenge creation)
    if (!isset($_SESSION)) {
        session_start();
    }

    $challengeData = $_SESSION['auth_challenge'] ?? null;
    if (!$challengeData || $challengeData['user_id'] !== $userId) {
        return false;
    }

    // Verify response against challenge
    $user = getUserById($userId);
    if (!$user) {
        return false;
    }

    $verified = verifyAuthResponse($challengeData['challenge'], $response, $user['ssh_public_key']);

    if ($verified) {
        // Set authenticated session
        $_SESSION['user_id'] = $userId;
        $_SESSION['authenticated'] = true;
        $_SESSION['auth_time'] = time();

        // Clear challenge from session
        unset($_SESSION['auth_challenge']);

        return true;
    }

    return false;
}

/**
 * Store authentication challenge in session
 * @param array $challengeData Challenge data from createAuthChallenge()
 */
function storeAuthChallenge($challengeData) {
    if (!isset($_SESSION)) {
        session_start();
    }

    $_SESSION['auth_challenge'] = $challengeData;
}

/**
 * Clear stored authentication challenge
 */
function clearAuthChallenge() {
    if (!isset($_SESSION)) {
        session_start();
    }

    unset($_SESSION['auth_challenge']);
}
?>
