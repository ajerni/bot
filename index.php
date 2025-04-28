<?php

// Include security functions
require_once 'includes/security.php';

// Initialize security
initSecurity();

// Load configuration
$config = require __DIR__ . '/private/config/config.php';

// Check if user is already authenticated
if (isset($_SESSION['authenticated']) && checkSessionTimeout()) {
    // User is already logged in, redirect to chat
    header('Location: chat.php');
    exit;
}

$error_message = "";
$show_access_denied = false;

// Check if form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CSRF check
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error_message = "Invalid request. Please try again.";
    }
    // Check for brute force
    elseif (!checkBruteForce()) {
        $error_message = "Too many login attempts. Please try again later.";
    }
    // Check password
    elseif (isset($_POST['password']) && password_verify($_POST['password'], $config['password_hash'])) {
        // Reset login attempts
        resetLoginAttempts();
        
        // Regenerate session ID
        session_regenerate_id(true);
        
        // Set session variables
        $_SESSION['authenticated'] = true;
        $_SESSION['last_activity'] = time();
        
        // Set success flag for JavaScript (used in stars.js to trigger the animation and redirect to chat.php)
        $login_success = true;
    } else {
        // Record failed attempt
        recordFailedAttempt();
        $error_message = "Incorrect password. Please try again.";
        $show_access_denied = true;
        
        // Store in session to persist through redirect
        $_SESSION['show_access_denied'] = true;
    }
}

// Check if we need to show access denied from session (from a previous POST request)
if (isset($_SESSION['show_access_denied']) && $_SESSION['show_access_denied']) {
    $show_access_denied = true;
    // Clear the flag so it doesn't show again on refresh
    unset($_SESSION['show_access_denied']);
}

// Generate new CSRF token for the form
$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Andi's Bot</title>
    <link rel="stylesheet" href="animations/nebula-animation.css">
    <link rel="stylesheet" href="/styles/login-styles.css">
    <link rel="stylesheet" href="/styles/denied.css">
    <style>
        /* Display conditionally based on PHP value */
        <?php if (!$show_access_denied): ?>#access-denied-overlay { display: none; }<?php endif; ?>
    </style>
    <script>
        // Pass PHP variables to JavaScript
        var loginSuccess = <?php echo isset($login_success) && $login_success === true ? 'true' : 'false'; ?>;
        var showAccessDenied = <?php echo $show_access_denied ? 'true' : 'false'; ?>;
    </script>
    <script src="/scripts/stars.js"></script>
    <script src="/scripts/login.js"></script>
</head>
<body>
    <!-- Simple, direct access denied overlay that doesn't rely on JS for initial showing -->
    <?php if ($show_access_denied): ?>
    <div id="access-denied-overlay">
        <div id="access-denied-message">
            ACCESS DENIED
            <div id="scanline"></div>
        </div>
    </div>
    <?php endif; ?>

    <div class="stars" id="stars"></div>
    <div class="warp-transition" id="warp-transition"></div>
    <div class="flash" id="flash"></div>
    
    <div class="content-container" id="content">
        <div class="title">Andi's personal bot</div>
        
        <div class="login-container <?php if (isset($login_success) && $login_success === true) echo 'instant-hide'; ?>" id="login-container">
            <?php if ($error_message): ?>
                <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>
            
            <form class="login-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="password" name="password" placeholder="Enter password" required autofocus>
                <button type="submit">Access</button>
            </form>
        </div>
    </div>
</body>
</html> 