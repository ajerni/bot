<?php
// Start session
session_start();

// Check if user is authenticated
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    // Redirect to login page if not authenticated
    header("Location: index.php");
    exit;
}

// API endpoints for favorites
$getFavoritesApiEndpoint = 'https://n8n.ernilabs.com/webhook/getfavorites';
$saveFavoritesApiEndpoint = 'https://n8n.ernilabs.com/webhook/savefavorites';

// Function to get current favorites from API
function getFavorites() {
    global $getFavoritesApiEndpoint;
    
    // Initialize cURL session
    $ch = curl_init($getFavoritesApiEndpoint);
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Execute cURL session and get the response
    $response = curl_exec($ch);
    
    // Check for errors
    if(curl_errno($ch)) {
        error_log('Error fetching favorites: ' . curl_error($ch));
        return [];
    }
    
    // Check HTTP response code
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode >= 400) {
        error_log('API returned error code: ' . $httpCode . ', Response: ' . $response);
        return [];
    }
    
    // Close cURL session
    curl_close($ch);
    
    // Parse JSON response
    $data = json_decode($response, true);
    
    // If JSON decoding failed
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('JSON decode error: ' . json_last_error_msg());
        return [];
    }
    
    // Initialize empty favorites array
    $favorites = [];
    
    // Handle the specific format from n8n
    if (isset($data['favoriten'])) {
        // The favoriten field is a JSON string, parse it
        if (is_string($data['favoriten'])) {
            $favorites = json_decode($data['favoriten'], true);
            
            // Check for JSON decode error
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('Error decoding favoriten JSON string: ' . json_last_error_msg());
                return [];
            }
        } 
        // If it's already an array, use it directly
        else if (is_array($data['favoriten'])) {
            $favorites = $data['favoriten'];
        }
    }
    
    return is_array($favorites) ? $favorites : [];
}

// Function to save favorites to API
function saveFavorites($favorites) {
    global $saveFavoritesApiEndpoint;
    
    // Encode the favorites array as JSON
    $favoritesJson = json_encode($favorites);
    
    // Initialize cURL session
    $ch = curl_init($saveFavoritesApiEndpoint);
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $favoritesJson);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($favoritesJson)
    ]);
    
    // Execute cURL session and get the response
    $response = curl_exec($ch);
    
    // Check for errors
    if(curl_errno($ch)) {
        error_log('Error saving favorites: ' . curl_error($ch));
        return false;
    }
    
    // Check HTTP response code
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode >= 400) {
        error_log('API returned error code: ' . $httpCode . ', Response: ' . $response);
        return false;
    }
    
    // Close cURL session
    curl_close($ch);
    
    return true;
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
            $saveResult = saveFavorites($favorites);
            if ($saveResult) {
                $successMessage = 'New favorite question added successfully!';
            } else {
                $message = 'Error saving favorite. Please try again.';
            }
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
        $saveResult = saveFavorites($favorites);
        if ($saveResult) {
            $successMessage = 'Favorite question removed successfully!';
        } else {
            $message = 'Error removing favorite. Please try again.';
        }
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
        
        .back-button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-top: 20px;
            transition: background-color 0.2s;
        }
        
        .back-button i {
            margin-right: 8px;
        }
        
        .back-button:hover {
            background-color: #0069d9;
            text-decoration: none;
        }
        
        .loading {
            display: none;
            text-align: center;
            margin: 20px 0;
        }
        
        .loading i {
            color: #007bff;
            font-size: 24px;
            animation: spin 1s infinite linear;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
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
            
            <div class="loading">
                <i class="fas fa-spinner"></i> Loading favorites...
            </div>
            
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
        
        <a href="chat.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Chat
        </a>
    </div>
    
    <script>
        // Show loading indicator during form submissions
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            const loading = document.querySelector('.loading');
            
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    loading.style.display = 'block';
                });
            });
        });
    </script>
</body>
</html> 