<?php
// Start session
session_start();

// Define the password
$correct_password = "your_password";
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
    <link rel="stylesheet" href="animations/animations4.css">
    <link rel="stylesheet" href="styles/login-styles.css">
    <script>
        // Pass login success status to JavaScript
        var loginSuccess = <?php echo isset($login_success) && $login_success === true ? 'true' : 'false'; ?>;
    </script>
    <script src="scripts/stars.js"></script>
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
</body>
</html> 