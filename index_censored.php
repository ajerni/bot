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
        
        // Redirect to chat.php
        header("Location: chat.php");
        exit;
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
    <link rel="stylesheet" href="style.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background-color: #f5f5f5;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 20px;
        }
        .login-form input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .login-form button {
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .login-form button:hover {
            background-color: #45a049;
        }
        .error-message {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Andi's Bot</h1>
        <p>Please enter the password to access the chat</p>
        
        <?php if ($error_message): ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php endif; ?>
        
        <form class="login-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="password" name="password" placeholder="Enter password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html> 