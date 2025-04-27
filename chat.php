<?php
// Start session
session_start();

// Check if user is authenticated
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    // Redirect to login page if not authenticated
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Andi's Bot</title>
    <!-- Add marked.js for Markdown parsing -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <!-- Link to external CSS file -->
    <link rel="stylesheet" href="styles/style.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <button id="clear-chat-btn" class="clear-chat-btn" title="Clear chat history">
                <i class="fas fa-trash"></i>
            </button>
            <button id="favorites-btn" class="favorites-btn" title="Favorite questions">
                <i class="fas fa-star"></i>
            </button>
            <button id="dark-mode-btn" class="dark-mode-btn" title="Toggle dark mode">
                <i class="fas fa-moon"></i>
            </button>
            Andi's Bot
            <button id="fullscreen-btn" class="fullscreen-btn" title="Toggle fullscreen">
                <i class="fas fa-expand"></i>
            </button>
        </div>
        <div class="chat-messages" id="chat-messages">
            <div class="message bot-message">
                Linked to n8n MPC server. Ready to help!
            </div>
        </div>
        <div class="chat-input">
            <input type="text" id="message-input" placeholder="Type your message here..." autocomplete="off">
            <button id="mic-button" title="Voice input">
                <i class="fas fa-microphone"></i>
            </button>
            <button id="send-button">Send</button>
        </div>
    </div>

    <!-- Favorites Modal -->
    <div id="favorites-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Favorite Questions</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <ul id="favorites-list">
                    <!-- Favorite questions will be loaded here dynamically -->
                </ul>
            </div>
        </div>
    </div>

    <!-- Link to external JavaScript files -->
    <script src="scripts/script.js"></script>
    <script src="scripts/favorites.js"></script>
</body>
</html> 