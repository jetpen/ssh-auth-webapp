<?php
// Include configuration and functions
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$sshPublicKey = "ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABgQDLE4etSd4xZX1EHzMqQ0+9R1BShm8N+BPTgAEthU9JAp4ZS9DaebQ+/e5BMJSB2YVGqW2TAbrpl1wYgZsIVDFCRjavOnZs4HSY4zIckdIV4NmJQC/iuJz8a653ewUl+/+NHQYd/suRrd/+/YbDabMhomg1+74azHfA7EeJgOKVcHc/xI10MHS9DKfPZRiiopw+I0HI894LtzC0r5pgFbsNAhnehwkE2y0rvZ+yCRtJlHmw+YCRhkrMaCOXwSr48zDk3gR0uEBxg+DBKBkyBk0gIl1OQ+pTVc1Rjg7V4zEBNLatWl32kNkl9DYgJRxO9Alh+RMmh2KT3cnzr/11eIdOr8xvl50cvwK1ec8cZw2VncjeO/V1YEJcxsvUVpiRtIT9fzaYvsk0j8a0RVYhxPS+R4QcGceNwJuzIi3Y+UZKHXTqkdja6VhhaxqU9F3y8dcJ9b4ax/LNYNNrvUEfwFgipF8qswMSd9v/M5JUO+kAhghQXHN1v4CfviQx7fRtNDk= BENENG@BENENG-LAP
";

$parsedKey = parseSSHPublicKey($sshPublicKey);
echo "type: " . $parsedKey["type"] . "\n";
echo "key: " . $parsedKey["key"] . "\n";
echo "raw: " . $parsedKey["raw"] . "\n";

$key = $parsedKey["key"];

/*
$key = "-----BEGIN PUBLIC KEY-----\n" .
"MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAoKQH6XtTUYPSIWPjtcA3I6VBF3F3TZMd9RImq0YG55qGIJvOOP0MeVib\n" .
"D7MFtN4hv6ke3NyYaaUfRaxQ6mrDGzd\n" .
"YOzdkqebjUzSNnwd8eQCRL2rvOsgUhf2yghLBlxq+9yfpzDV3KQ58JkCqvV1trBt/ISjPtgbK24V3v55z+cN558DMgyQmV\n" .
"8pYrTFzktFVlJP20DR08HzIGimlWq/ixUfY4K\n" .
"rznqapnKMw1u6SVVgGem67LC8HO9Mfx3KDseJaG7oUbSWq8vaTW2ewjEfs5JRt1OMUol7CHHtqVprcMizclqCO9Kh\n" .
"Dmpussq19l0LbKbGkC73uK0Nm8RyfGhiWCQIDAQAB\n" .
"-----END PUBLIC KEY-----";
*/

$challenge = "test challenge";

$publicKey = openssl_get_publickey($key);

if (!$publicKey) {
    die("Failed to load public key\n");
}

$encrypted = openssl_public_encrypt($challenge, $encryptedData, $publicKey);

if (!$encrypted) {
    die("Encryption failed\n");
}

echo "Encrypted challenge: " . base64_encode($encryptedData) . "\n";
?>
