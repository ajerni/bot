<?php
// Start session
session_start();

// Check if user is authenticated
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    // Redirect to login page if not authenticated
    header("Location: index.php");
    exit;
}

// Debug POST data
error_log('POST data: ' . print_r($_POST, true));
error_log('REQUEST_METHOD: ' . $_SERVER['REQUEST_METHOD']);

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
    
    // Execute the request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Close cURL session
    curl_close($ch);
    
    // Log the response
    error_log("getFavorites response code: $httpCode");
    error_log("getFavorites raw response: $response");
    
    // Check for successful response
    if ($httpCode != 200) {
        error_log("Error retrieving favorites: HTTP $httpCode");
        return [];
    }
    
    // Parse the JSON response
    $data = json_decode($response, true);
    
    // Check for JSON parsing error
    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON parsing error: " . json_last_error_msg());
        return [];
    }
    
    // Extract the favorites array from the response
    $favorites = [];
    
    if (isset($data['favoriten'])) {
        if (is_string($data['favoriten'])) {
            // If favoriten is a JSON string, parse it
            $favorites = json_decode($data['favoriten'], true);
            if ($favorites === null) {
                error_log("Error parsing favoriten JSON string");
                return [];
            }
        } elseif (is_array($data['favoriten'])) {
            // If favoriten is already an array, use it directly
            $favorites = $data['favoriten'];
        }
    }
    
    return is_array($favorites) ? $favorites : [];
}

// Function to save favorites to API
function saveFavorites($favorites) {
    global $saveFavoritesApiEndpoint;
    
    // Ensure we have an array
    if (!is_array($favorites)) {
        error_log('Error: favorites is not an array');
        return false;
    }
    
    // Convert the array to JSON
    $jsonData = json_encode($favorites);
    
    // Simple debugging
    error_log("Saving favorites to: $saveFavoritesApiEndpoint");
    error_log("JSON data: $jsonData");
    
    // Use cURL to send the data
    $ch = curl_init($saveFavoritesApiEndpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($jsonData)
    ));
    
    // Execute the request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Log the result
    error_log("API Response Code: $httpCode");
    error_log("API Response: $response");
    
    // Close cURL session
    curl_close($ch);
    
    // Return success based on HTTP code
    return ($httpCode >= 200 && $httpCode < 300);
}

// Handle form submission
$message = '';
$successMessage = '';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST request received: " . print_r($_POST, true));
    
    // Handle adding a new favorite
    if (isset($_POST['add_favorite']) && !empty($_POST['question'])) {
        $newQuestion = trim($_POST['question']);
        error_log("Adding new question: $newQuestion");
        
        // Get current favorites
        $favorites = getFavorites();
        error_log("Current favorites: " . print_r($favorites, true));
        
        // Check if the question already exists
        $questionExists = false;
        foreach ($favorites as $favorite) {
            if (isset($favorite['question']) && $favorite['question'] === $newQuestion) {
                $questionExists = true;
                break;
            }
        }
        
        if (!$questionExists) {
            // Find highest ID
            $maxId = 0;
            foreach ($favorites as $favorite) {
                if (isset($favorite['id']) && $favorite['id'] > $maxId) {
                    $maxId = $favorite['id'];
                }
            }
            
            // Add new favorite
            $favorites[] = [
                'id' => $maxId + 1,
                'question' => $newQuestion
            ];
            
            // Save the updated favorites
            if (saveFavorites($favorites)) {
                $successMessage = "New favorite question added successfully!";
                error_log("New favorite added successfully");
            } else {
                $message = "Error saving favorite. Please try again.";
                error_log("Failed to save favorites");
            }
        } else {
            $message = "This question already exists in favorites.";
            error_log("Question already exists");
        }
    }
    
    // Handle deleting a favorite
    if (isset($_POST['delete_favorite']) && isset($_POST['favorite_id'])) {
        $favoriteId = (int)$_POST['favorite_id'];
        error_log("Deleting favorite with ID: $favoriteId");
        
        // Get current favorites
        $favorites = getFavorites();
        
        // Find and remove the favorite
        $found = false;
        foreach ($favorites as $key => $favorite) {
            if (isset($favorite['id']) && $favorite['id'] === $favoriteId) {
                unset($favorites[$key]);
                $found = true;
                break;
            }
        }
        
        if ($found) {
            // Reindex array
            $favorites = array_values($favorites);
            
            // Save the updated favorites
            if (saveFavorites($favorites)) {
                $successMessage = "Favorite question removed successfully!";
                error_log("Favorite removed successfully");
            } else {
                $message = "Error removing favorite. Please try again.";
                error_log("Failed to save favorites after deletion");
            }
        } else {
            $message = "Favorite not found with ID: $favoriteId";
            error_log("Favorite not found");
        }
    }
}

// Get current favorites for display
$favorites = getFavorites();
error_log('Favorites for display: ' . print_r($favorites, true));
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
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="form-group">
                <label for="question">Add New Favorite Question:</label>
                <input type="text" id="question" name="question" required>
            </div>
            <input type="hidden" name="add_favorite" value="1">
            <button type="submit">Add Question</button>
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
                                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                                        <input type="hidden" name="favorite_id" value="<?= htmlspecialchars($favorite['id']) ?>">
                                        <input type="hidden" name="delete_favorite" value="1">
                                        <button type="submit" class="delete-btn">Delete</button>
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
        // Basic logging of form submissions
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Page loaded and ready');
            
            // Log form submissions
            document.querySelectorAll('form').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    console.log('Form submitting...');
                    document.querySelector('.loading').style.display = 'block';
                });
            });
        });
    </script>
</body>
</html> 