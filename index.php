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

// Check if form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Debug output
    var_dump('Posted password:', $_POST['password'] ?? null);
    var_dump('Config hash:', $config['password_hash'] ?? null);
    var_dump('password_verify:', isset($_POST['password']) ? password_verify($_POST['password'], $config['password_hash']) : null);
    // Re-enable CSRF check
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
        
        // Set success flag for JavaScript
        $login_success = true;
    } else {
        // Record failed attempt
        recordFailedAttempt();
        $error_message = "Incorrect password. Please try again.";
    }
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
    <script>
        // Pass login success status to JavaScript
        var loginSuccess = <?php echo isset($login_success) && $login_success === true ? 'true' : 'false'; ?>;
        
        // Handle form submission to prevent content shift
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.querySelector('.login-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Prevent default form submission - we'll handle it via AJAX
                    e.preventDefault();
                    
                    // Fix positions immediately
                    document.body.style.overflow = 'hidden'; // Prevent scrolling
                    
                    // Fix and fade out title
                    var title = document.querySelector('.title');
                    if (title) {
                        var titleRect = title.getBoundingClientRect();
                        title.style.position = 'fixed';
                        title.style.top = titleRect.top + 'px';
                        title.style.left = titleRect.left + 'px';
                        title.style.width = titleRect.width + 'px';
                        title.style.height = titleRect.height + 'px';
                        title.classList.add('fade-out');
                    }
                    
                    // Fix and fade out login container
                    var loginContainer = document.getElementById('login-container');
                    if (loginContainer) {
                        var rect = loginContainer.getBoundingClientRect();
                        loginContainer.style.position = 'fixed';
                        loginContainer.style.top = rect.top + 'px';
                        loginContainer.style.left = rect.left + 'px';
                        loginContainer.style.width = rect.width + 'px';
                        loginContainer.style.height = rect.height + 'px';
                        loginContainer.style.margin = '0';
                        loginContainer.style.transform = 'none';
                        loginContainer.classList.add('submitting');
                    }
                    
                    // Fix content position
                    var content = document.getElementById('content');
                    if (content) {
                        content.style.position = 'fixed';
                        content.style.top = '0';
                        content.style.left = '0';
                        content.style.width = '100%';
                        content.style.height = '100%';
                        content.style.transform = 'none';
                    }
                    
                    // Submit form via AJAX
                    var formData = new FormData(form);
                    fetch(form.action, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(html => {
                        // Check if login was successful
                        if (html.includes('var loginSuccess = true')) {
                            // Trigger animation
                            setTimeout(function() {
                                if (typeof activateWarpEffect === 'function') {
                                    activateWarpEffect();
                                }
                            }, 100);
                        } else {
                            // Login failed, reload to show error
                            location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        location.reload();
                    });
                });
            }
        });
    </script>
    <script src="/scripts/stars.js"></script>
</head>
<body>
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