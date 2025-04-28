<?php
// Security helper functions

function initSecurity() {
    // Start secure session
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => true,
        'cookie_samesite' => 'Strict',
        'use_strict_mode' => true
    ]);

    // Set security headers
    $config = require __DIR__ . '/../private/config/config.php';
    foreach ($config['security_headers'] as $header => $value) {
        header("$header: $value");
    }
}

function checkSessionTimeout() {
    $config = require __DIR__ . '/../private/config/config.php';
    if (isset($_SESSION['last_activity']) && 
        (time() - $_SESSION['last_activity'] > $config['session_timeout'])) {
        session_unset();
        session_destroy();
        return false;
    }
    $_SESSION['last_activity'] = time();
    return true;
}

function checkBruteForce() {
    $config = require __DIR__ . '/../private/config/config.php';
    
    if (isset($_SESSION['login_attempts']) && 
        $_SESSION['login_attempts'] >= $config['max_login_attempts'] &&
        time() - $_SESSION['last_attempt'] < $config['lockout_time']) {
        return false;
    }
    return true;
}

function recordFailedAttempt() {
    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
    $_SESSION['last_attempt'] = time();
}

function resetLoginAttempts() {
    unset($_SESSION['login_attempts']);
    unset($_SESSION['last_attempt']);
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
} 