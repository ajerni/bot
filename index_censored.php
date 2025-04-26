<?php
// Start session
session_start();

// Define the password
$correct_password = "your_password_here";
$error_message = "";

// Check if form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if password is correct
    if (isset($_POST['password']) && $_POST['password'] === $correct_password) {
        // Set session variable
        $_SESSION['authenticated'] = true;
        
        // We'll handle the redirect via JavaScript for the transition effect
        $login_success = true;
    } else {
        $error_message = "Incorrect password. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Andi's Bot</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-color: #000;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
            overflow: hidden;
            position: relative;
        }
        
        .stars {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }
        
        .star {
            position: absolute;
            background-color: #fff;
            border-radius: 50%;
            z-index: 1;
        }
        
        .shining-star {
            position: absolute;
            color: white;
            font-size: 20px;
            z-index: 1;
            opacity: 0.9;
        }
        
        .twinkle {
            animation: twinkle 4s infinite;
        }
        
        @keyframes twinkle {
            0% { opacity: 0.2; }
            50% { opacity: 1; }
            100% { opacity: 0.2; }
        }
        
        .content-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 40px;
            z-index: 10;
            width: 100%;
            padding: 20px;
            position: relative;
            min-height: 100vh;
        }
        
        .title {
            color: white;
            font-size: 80px;
            font-weight: 900;
            text-align: center;
            letter-spacing: 2px;
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.7);
            font-family: Arial, Helvetica, sans-serif;
            text-transform: none;
            margin: 0;
            line-height: 1;
            white-space: nowrap;
            position: absolute;
            top: 25%;
            transform: translateY(-50%);
        }
        
        .login-container {
            position: relative;
            z-index: 10;
            background-color: rgba(0, 0, 0, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 30px;
            width: 350px;
            text-align: center;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
        }
        
        h1 {
            color: #fff;
            margin-bottom: 20px;
            font-size: 28px;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
        }
        
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .login-form input {
            padding: 12px;
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 5px;
            color: #fff;
            font-size: 16px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .login-form input:focus {
            outline: none;
            background-color: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.2);
        }
        
        .login-form input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .login-form button {
            padding: 12px;
            background-color: transparent;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .login-form button:hover {
            background-color: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.3);
        }
        
        .error-message {
            color: #ff5555;
            margin-top: 15px;
            font-size: 14px;
            text-shadow: 0 0 5px rgba(255, 0, 0, 0.3);
        }
        
        /* Transition effects */
        .warp-transition {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1000;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .warp-transition.active {
            opacity: 1;
        }
        
        .warp-star {
            position: absolute;
            background-color: #fff;
            height: 2px;
            transform-origin: left center;
            transition: all 1.5s cubic-bezier(0.645, 0.045, 0.355, 1);
        }
        
        .flash {
            position: fixed;
            top: 0;
            left:, 0;
            width: 100%;
            height: 100%;
            background-color: white;
            z-index: 2000;
            opacity: 0;
            pointer-events: none;
        }
        
        @keyframes hyperspace {
            0% {
                transform: translateX(0) scaleX(1);
                opacity: 1;
            }
            100% {
                transform: translateX(100vw) scaleX(20);
                opacity: 0;
            }
        }
        
        @keyframes flash {
            0% { opacity: 0; }
            50% { opacity: 1; }
            100% { opacity: 0; }
        }
        
        .fade-out {
            animation: fadeOut 1s forwards;
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        
        /* Hide form on success to show animation better */
        .hidden {
            opacity: 0;
            transition: opacity 0.5s ease;
        }
        
        /* For instant hiding of login container */
        .instant-hide {
            display: none;
        }
    </style>
</head>
<body>
    <div class="stars" id="stars"></div>
    <div class="warp-transition" id="warp-transition"></div>
    <div class="flash" id="flash"></div>
    
    <div class="content-container" id="content">
        <div class="title">Andi's personal bot</div>
        
        <div class="login-container <?php if (isset($login_success) && $login_success === true) echo 'instant-hide'; ?>" id="login-container">
            <?php if ($error_message): ?>
                <p class="error-message"><?php echo $error_message; ?></p>
            <?php endif; ?>
            
            <form class="login-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="password" name="password" placeholder="Enter password" required autofocus>
                <button type="submit">Access</button>
            </form>
        </div>
    </div>
    
    <script>
        // Create stars
        function createStars() {
            const starsContainer = document.getElementById('stars');
            const numberOfStars = 150; // Regular stars
            const numberOfShiningStars = 20; // Special 4-point stars
            
            // Create regular stars
            for (let i = 0; i < numberOfStars; i++) {
                const star = document.createElement('div');
                star.className = 'star';
                
                // Random position
                const x = Math.random() * 100;
                const y = Math.random() * 100;
                
                // Random size (0.5px to 3px)
                const size = Math.random() * 2.5 + 0.5;
                
                star.style.left = `${x}%`;
                star.style.top = `${y}%`;
                star.style.width = `${size}px`;
                star.style.height = `${size}px`;
                
                // Make some stars twinkle with random delay
                if (Math.random() > 0.7) {
                    star.classList.add('twinkle');
                    star.style.animationDelay = `${Math.random() * 4}s`;
                }
                
                starsContainer.appendChild(star);
            }
            
            // Create shining 4-point stars (✦ or ✧)
            for (let i = 0; i < numberOfShiningStars; i++) {
                const shiningStar = document.createElement('div');
                shiningStar.className = 'shining-star';
                shiningStar.innerHTML = '✧';
                
                // Random position
                const x = Math.random() * 100;
                const y = Math.random() * 100;
                
                // Random size
                const size = Math.random() * 20 + 10;
                
                shiningStar.style.left = `${x}%`;
                shiningStar.style.top = `${y}%`;
                shiningStar.style.fontSize = `${size}px`;
                
                // Make some stars twinkle with random delay
                if (Math.random() > 0.5) {
                    shiningStar.classList.add('twinkle');
                    shiningStar.style.animationDelay = `${Math.random() * 4}s`;
                }
                
                starsContainer.appendChild(shiningStar);
            }
        }
        
        // Initialize warp transition
        function initWarpTransition() {
            const warpTransition = document.getElementById('warp-transition');
            const numLines = 100;
            
            for (let i = 0; i < numLines; i++) {
                const line = document.createElement('div');
                line.className = 'warp-star';
                
                // Random position
                const y = Math.random() * 100;
                const width = Math.random() * 50 + 10;
                const delay = Math.random() * 0.5;
                
                line.style.top = `${y}%`;
                line.style.left = `${Math.random() * 30}%`;
                line.style.width = `${width}px`;
                line.style.opacity = Math.random() * 0.7 + 0.3;
                line.style.transitionDelay = `${delay}s`;
                
                warpTransition.appendChild(line);
            }
        }
        
        // Activate warp transition effect
        function activateWarpEffect() {
            const content = document.getElementById('content');
            const loginContainer = document.getElementById('login-container');
            const warpTransition = document.getElementById('warp-transition');
            const flash = document.getElementById('flash');
            const stars = document.querySelectorAll('.star, .shining-star');
            const warpStars = document.querySelectorAll('.warp-star');
            
            // Login container is already hidden with instant-hide class from PHP
            
            // Hide content with fade
            content.classList.add('fade-out');
            
            // Activate warp transition
            warpTransition.classList.add('active');
            
            // Animate all warp stars
            warpStars.forEach(star => {
                setTimeout(() => {
                    star.style.transform = `scaleX(${20 + Math.random() * 30}) translateX(${window.innerWidth}px)`;
                    star.style.opacity = '0';
                }, Math.random() * 200);
            });
            
            // Hide regular stars
            stars.forEach(star => {
                star.style.animation = 'none';
                star.style.opacity = '0';
                star.style.transition = 'opacity 1s ease';
            });
            
            // Flash effect
            setTimeout(() => {
                flash.style.animation = 'flash 1.5s forwards';
                
                // Redirect after animation
                setTimeout(() => {
                    window.location.href = 'chat.php';
                }, 1500);
            }, 1200);
        }
        
        // Call when page loads
        window.addEventListener('load', function() {
            createStars();
            initWarpTransition();
            
            <?php if (isset($login_success) && $login_success === true): ?>
            // If login successful, activate transition
            setTimeout(activateWarpEffect, 500);
            <?php endif; ?>
        });
    </script>
</body>
</html> 