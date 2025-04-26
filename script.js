document.addEventListener('DOMContentLoaded', function() {
    const messageInput = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');
    const chatMessages = document.getElementById('chat-messages');
    const fullscreenBtn = document.getElementById('fullscreen-btn');
    const clearChatBtn = document.getElementById('clear-chat-btn');
    const chatContainer = document.querySelector('.chat-container');
    const fullscreenIcon = fullscreenBtn.querySelector('i');
    const micButton = document.getElementById('mic-button');
    const micIcon = micButton.querySelector('i');
    
    // Speech recognition setup
    let recognition = null;
    let isListening = false;
    
    // Check if browser supports speech recognition
    if ('SpeechRecognition' in window || 'webkitSpeechRecognition' in window) {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        recognition = new SpeechRecognition();
        recognition.continuous = false;
        recognition.interimResults = false;
        recognition.lang = 'en-US';
        
        recognition.onresult = function(event) {
            const transcript = event.results[0][0].transcript;
            messageInput.value = transcript;
            stopSpeechRecognition();
        };
        
        recognition.onerror = function(event) {
            console.error('Speech recognition error', event.error);
            stopSpeechRecognition();
        };
        
        recognition.onend = function() {
            stopSpeechRecognition();
        };
    } else {
        console.warn('Speech recognition not supported in this browser');
        micButton.style.display = 'none';
    }
    
    // Function to start speech recognition
    function startSpeechRecognition() {
        if (recognition) {
            recognition.start();
            isListening = true;
            micIcon.classList.remove('fa-microphone');
            micIcon.classList.add('fa-microphone-slash');
            micButton.classList.add('listening');
            messageInput.placeholder = "Listening...";
        }
    }
    
    // Function to stop speech recognition
    function stopSpeechRecognition() {
        if (recognition) {
            recognition.stop();
            isListening = false;
            micIcon.classList.remove('fa-microphone-slash');
            micIcon.classList.add('fa-microphone');
            micButton.classList.remove('listening');
            messageInput.placeholder = "Type your message here...";
        }
    }
    
    // Toggle speech recognition when mic button is clicked
    micButton.addEventListener('click', function() {
        if (isListening) {
            stopSpeechRecognition();
        } else {
            startSpeechRecognition();
        }
    });
    
    // Clear chat functionality
    clearChatBtn.addEventListener('click', function() {
        // Remove all messages except the first welcome message
        while (chatMessages.childNodes.length > 1) {
            chatMessages.removeChild(chatMessages.lastChild);
        }
        
        // Focus on the input field after clearing
        messageInput.focus();
    });
    
    // Set fullscreen on load
    let isFullscreen = true;
    
    // Apply fullscreen on initial load
    chatContainer.classList.add('fullscreen-chat');
    fullscreenIcon.classList.remove('fa-expand');
    fullscreenIcon.classList.add('fa-compress');
    fullscreenBtn.title = "Exit fullscreen";
    
    fullscreenBtn.addEventListener('click', function() {
        isFullscreen = !isFullscreen;
        
        if (isFullscreen) {
            // Enter fullscreen mode
            chatContainer.classList.add('fullscreen-chat');
            fullscreenIcon.classList.remove('fa-expand');
            fullscreenIcon.classList.add('fa-compress');
            fullscreenBtn.title = "Exit fullscreen";
        } else {
            // Exit fullscreen mode
            chatContainer.classList.remove('fullscreen-chat');
            fullscreenIcon.classList.remove('fa-compress');
            fullscreenIcon.classList.add('fa-expand');
            fullscreenBtn.title = "Enter fullscreen";
        }
        
        // Focus back on input after toggling
        setTimeout(() => messageInput.focus(), 100);
    });
    
    // Listen for Escape key to exit fullscreen
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && isFullscreen) {
            fullscreenBtn.click();
        }
    });
    
    // Generate a unique sessionId for this chat session
    const sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    console.log('Generated sessionId:', sessionId);
    
    // Function to add a message to the chat
    function addMessage(message, isUser) {
        const messageElement = document.createElement('div');
        messageElement.classList.add('message');
        messageElement.classList.add(isUser ? 'user-message' : 'bot-message');
        
        if (isUser) {
            // User messages are displayed as plain text
            messageElement.textContent = message;
        } else {
            // Bot messages are rendered as Markdown
            try {
                // Use marked.js to parse markdown
                messageElement.innerHTML = marked.parse(message);
            } catch (e) {
                console.error('Error parsing markdown:', e);
                // Fallback to simple newline handling if markdown parsing fails
                messageElement.innerHTML = message.replace(/\n/g, '<br>');
            }
        }
        
        chatMessages.appendChild(messageElement);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        return messageElement;
    }
    
    // Function to show typing animation in a bot message bubble
    function showTypingAnimation() {
        // Create typing animation message
        const typingElement = document.createElement('div');
        typingElement.classList.add('message', 'bot-message', 'typing-message');
        typingElement.innerHTML = '<div class="typing-dots"><span></span><span></span><span></span></div>';
        chatMessages.appendChild(typingElement);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        return typingElement;
    }
    
    // Function to send a message to the bot
    async function sendMessage(message) {
        // Add user message to chat
        addMessage(message, true);
        
        // Clear input field
        messageInput.value = '';
        
        // Show typing animation in a message bubble
        const typingElement = showTypingAnimation();
        
        try {
            // Include the sessionId in the payload
            const payload = { 
                chatInput: message,
                sessionId: sessionId
            };
            
            console.log('Sending payload:', payload);
            
            // Send message to PHP proxy instead of directly to n8n
            // The proxy handles authentication securely
            const response = await fetch('proxy.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            
            // Get the raw text response first
            const responseText = await response.text();
            console.log('API Raw Response:', responseText);
            
            let data;
            // Try to parse as JSON, if not, use as plain text
            try {
                data = JSON.parse(responseText);
                console.log('API Parsed Response:', data);
            } catch (e) {
                console.log('Response is not JSON, using as plain text');
                data = { text: responseText };
            }
            
            // Remove typing animation
            chatMessages.removeChild(typingElement);
            
            // Process the response
            if (data && data.output) {
                // The webhook returns JSON with an 'output' field
                addMessage(data.output, false);
            } else if (data && data.text) {
                addMessage(data.text, false);
            } else if (data && data.response) {
                addMessage(data.response, false);
            } else if (typeof responseText === 'string' && responseText.trim() !== '') {
                // Try to parse the raw text as JSON one more time
                // (in case we're getting double-encoded JSON)
                try {
                    const jsonAttempt = JSON.parse(responseText);
                    if (jsonAttempt && jsonAttempt.output) {
                        addMessage(jsonAttempt.output, false);
                    } else {
                        addMessage(responseText, false);
                    }
                } catch (e) {
                    addMessage(responseText, false);
                }
            } else {
                addMessage('Received an empty or invalid response from the webhook.', false);
            }
            
        } catch (error) {
            console.error('Error:', error);
            
            // Remove typing animation
            chatMessages.removeChild(typingElement);
            
            // Add more detailed error message
            addMessage(`Error communicating with the webhook: ${error.message}`, false);
        }
    }
    
    // Event listeners
    sendButton.addEventListener('click', function() {
        const message = messageInput.value.trim();
        if (message) {
            sendMessage(message);
        }
    });
    
    messageInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const message = messageInput.value.trim();
            if (message) {
                sendMessage(message);
            }
        }
    });
    
    // Focus input field when page loads
    messageInput.focus();
}); 