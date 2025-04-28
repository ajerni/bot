<?php
// This is a one-time script to generate a secure password hash
// Access it at https://your-domain.com/utils/generate_hash.php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    echo "Generated hash: " . $hash . "\n";
    echo "Copy this hash into your private/config/config.php file\n";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Generate Password Hash</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { max-width: 400px; margin: 0 auto; }
        input[type="password"] { width: 100%; padding: 8px; margin: 10px 0; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <form method="POST">
        <h2>Generate Password Hash</h2>
        <p>Enter your desired password:</p>
        <input type="password" name="password" required>
        <button type="submit">Generate Hash</button>
    </form>
</body>
</html> 