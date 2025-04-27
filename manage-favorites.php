<?php
// Start session
session_start();

// Check if user is authenticated
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    // Redirect to login page if not authenticated
    header("Location: index.php");
    exit;
}

// File path for the favorites JSON file
$favoritesFilePath = 'favorites.json';

// Function to get current favorites
function getFavorites() {
    global $favoritesFilePath;
    
    if (file_exists($favoritesFilePath)) {
        $favoritesJson = file_get_contents($favoritesFilePath);
        return json_decode($favoritesJson, true);
    }
    
    return [];
}

// Function to save favorites
function saveFavorites($favorites) {
    global $favoritesFilePath;
    
    $favoritesJson = json_encode($favorites, JSON_PRETTY_PRINT);
    file_put_contents($favoritesFilePath, $favoritesJson);
}

// Handle form submission
$message = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get current favorites
    $favorites = getFavorites();
    
    // Add new favorite
    if (isset($_POST['add_favorite']) && !empty($_POST['question'])) {
        $newQuestion = trim($_POST['question']);
        
        // Check if this question already exists
        $questionExists = false;
        foreach ($favorites as $favorite) {
            if ($favorite['question'] === $newQuestion) {
                $questionExists = true;
                break;
            }
        }
        
        if (!$questionExists) {
            // Find the highest ID
            $maxId = 0;
            foreach ($favorites as $favorite) {
                if ($favorite['id'] > $maxId) {
                    $maxId = $favorite['id'];
                }
            }
            
            // Add new favorite
            $favorites[] = [
                'id' => $maxId + 1,
                'question' => $newQuestion
            ];
            
            // Save favorites
            saveFavorites($favorites);
            $successMessage = 'New favorite question added successfully!';
        } else {
            $message = 'This question already exists in favorites.';
        }
    }
    
    // Delete favorite
    if (isset($_POST['delete_favorite']) && isset($_POST['favorite_id'])) {
        $favoriteId = (int)$_POST['favorite_id'];
        
        // Find and remove the favorite
        foreach ($favorites as $key => $favorite) {
            if ($favorite['id'] === $favoriteId) {
                unset($favorites[$key]);
                break;
            }
        }
        
        // Reindex array
        $favorites = array_values($favorites);
        
        // Save favorites
        saveFavorites($favorites);
        $successMessage = 'Favorite question removed successfully!';
    }
}

// Get current favorites for display
$favorites = getFavorites();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Favorite Questions</title>
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f5f5f5;
            font-family: Arial, sans-serif;
            display: block;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            color: #007bff;
            margin-top: 0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        
        button:hover {
            background-color: #0069d9;
        }
        
        .favorites-list {
            margin-top: 30px;
        }
        
        .favorites-list table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .favorites-list th, 
        .favorites-list td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .favorites-list th {
            background-color: #f2f2f2;
        }
        
        .delete-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }
        
        .delete-btn:hover {
            background-color: #c82333;
        }
        
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .back-link {
            margin-top: 20px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Manage Favorite Questions</h1>
        
        <?php if (!empty($message)): ?>
            <div class="message error"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <?php if (!empty($successMessage)): ?>
            <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label for="question">Add New Favorite Question:</label>
                <input type="text" id="question" name="question" required>
            </div>
            <button type="submit" name="add_favorite">Add Question</button>
        </form>
        
        <div class="favorites-list">
            <h2>Current Favorite Questions</h2>
            
            <?php if (empty($favorites)): ?>
                <p>No favorite questions added yet.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Question</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($favorites as $favorite): ?>
                            <tr>
                                <td><?= htmlspecialchars($favorite['id']) ?></td>
                                <td><?= htmlspecialchars($favorite['question']) ?></td>
                                <td>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="favorite_id" value="<?= htmlspecialchars($favorite['id']) ?>">
                                        <button type="submit" name="delete_favorite" class="delete-btn">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <a href="chat.php" class="back-link">← Back to Chat</a>
    </div>
</body>
</html> 