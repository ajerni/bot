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
        
        // Set success flag for JavaScript
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
    <style>
        /* Direct Access Denied styles */
        #access-denied-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            <?php if (!$show_access_denied): ?>display: none;<?php endif; ?>
        }
        
        #access-denied-message {
            font-family: monospace, 'Courier New', Courier;
            color: #ff0000;
            font-size: 6rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5rem;
            text-shadow: 0 0 30px rgba(255, 0, 0, 0.9);
            padding: 3rem;
            border: 4px solid #ff0000;
            background-color: rgba(0, 0, 0, 0.8);
            position: relative;
            animation: glitch 0.5s infinite alternate, pulse 2s infinite;
        }
        
        /* Error message styling */
        .error-message {
            color: #ff0000;
            margin-bottom: 15px;
        }
        
        /* Flash animation */
        .flash.active {
            animation: flash 1.5s forwards;
        }
        
        @keyframes flash {
            0% { opacity: 0; }
            50% { opacity: 1; }
            100% { opacity: 0; }
        }
        
        @keyframes glitch {
            0%, 5%, 10% {
                transform: translate(0);
                text-shadow: 0 0 30px rgba(255, 0, 0, 0.9);
            }
            1% {
                transform: translate(-5px, 2px);
                text-shadow: -5px 0 10px rgba(255, 0, 0, 0.9);
            }
            6% {
                transform: translate(5px, -2px);
                text-shadow: 5px 0 10px rgba(255, 0, 0, 0.9);
            }
        }
        
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 30px rgba(255, 0, 0, 0.7); }
            50% { box-shadow: 0 0 60px rgba(255, 0, 0, 0.9); }
        }
        
        #scanline {
            position: absolute;
            top: 0;
            left: -100%;
            width: 300%;
            height: 5px;
            background: rgba(255, 0, 0, 0.7);
            animation: scan 2s linear infinite;
        }
        
        @keyframes scan {
            0% { left: -100%; }
            100% { left: 100%; }
        }
    </style>
    <script>
        // Pass login success status to JavaScript
        var loginSuccess = <?php echo isset($login_success) && $login_success === true ? 'true' : 'false'; ?>;
        
        // Handle form submission to prevent content shift
        document.addEventListener('DOMContentLoaded', function() {
            // If login was successful, trigger the warp animation before redirecting
            if (loginSuccess) {
                var title = document.querySelector('.title');
                if (title) {
                    title.style.display = 'none';
                }
                
                var loginContainer = document.getElementById('login-container');
                if (loginContainer) {
                    loginContainer.style.display = 'none';
                }
                
                // Trigger warp animation
                var warpTransition = document.getElementById('warp-transition');
                if (warpTransition) {
                    warpTransition.classList.add('active');
                }
                
                // Show flash effect after short delay
                setTimeout(function() {
                    var flash = document.getElementById('flash');
                    if (flash) {
                        flash.classList.add('active');
                    }
                    
                    // Redirect to chat.php after animation completes
                    setTimeout(function() {
                        window.location.href = 'chat.php';
                    }, 1500); // Timing for flash animation
                }, 1500); // Adjusted to show flash earlier
                
                return;
            }
            
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
                    
                    // Submit form
                    form.submit();
                });
            }
            
            // Handle access denied overlay hiding after delay
            <?php if ($show_access_denied): ?>
            setTimeout(function() {
                var overlay = document.getElementById('access-denied-overlay');
                if (overlay) {
                    overlay.style.opacity = '0';
                    setTimeout(function() {
                        overlay.style.display = 'none';
                    }, 500);
                }
            }, 3000);
            <?php endif; ?>
        });
    </script>
    <script src="/scripts/stars.js"></script>
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