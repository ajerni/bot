// Favorites functionality
document.addEventListener('DOMContentLoaded', function() {
    // API endpoints for favorites
    const getFavoritesEndpoint = 'https://n8n.ernilabs.com/webhook/getfavorites';
    const saveFavoritesEndpoint = 'https://n8n.ernilabs.com/webhook/savefavorites';
    
    // Function to save a question as favorite
    function saveAsFavorite(question) {
        console.log('Attempting to save as favorite:', question);
        
        // First, get current favorites
        fetch(getFavoritesEndpoint)
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('Failed to fetch current favorites: ' + response.status);
                }
                return response.json();
            })
            .then(function(data) {
                // Parse the favoriten field
                let favorites = [];
                
                try {
                    if (data && data.favoriten) {
                        if (typeof data.favoriten === 'string') {
                            favorites = JSON.parse(data.favoriten);
                        } else if (Array.isArray(data.favoriten)) {
                            favorites = data.favoriten;
                        }
                    }
                } catch (e) {
                    console.error('Error parsing favorites:', e);
                }
                
                if (!Array.isArray(favorites)) {
                    favorites = [];
                }
                
                // Check if question already exists
                const questionExists = favorites.some(function(favorite) {
                    return favorite.question === question;
                });
                
                if (questionExists) {
                    console.log('Question already exists in favorites');
                    alert('This question is already in your favorites!');
                    return;
                }
                
                // Find the highest ID
                let maxId = 0;
                favorites.forEach(function(favorite) {
                    if (favorite.id > maxId) {
                        maxId = favorite.id;
                    }
                });
                
                // Add new favorite
                favorites.push({
                    id: maxId + 1,
                    question: question
                });
                
                console.log('Saving favorites:', favorites);
                
                // Save the updated favorites array
                return fetch(saveFavoritesEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(favorites) // Send the array directly as JSON in the body
                });
            })
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('Failed to save favorite: ' + response.status);
                }
                return response.text();
            })
            .then(function(result) {
                console.log('Favorite saved successfully:', result);
                alert('Question added to favorites!');
            })
            .catch(function(error) {
                console.error('Error saving favorite:', error);
                alert('Error saving favorite: ' + error.message);
            });
    }
    
    // Simple function to load favorites from API
    function loadFavorites() {
        var favoritesList = document.getElementById('favorites-list');
        var modal = document.getElementById('favorites-modal');
        
        if (!favoritesList || !modal) {
            console.error('Required elements not found', {favoritesList, modal});
            return;
        }
        
        // Show loading state
        favoritesList.innerHTML = '<li class="loading-item"><i class="fas fa-spinner fa-spin"></i> Loading favorites...</li>';
        
        // Fetch data from API endpoint
        fetch(getFavoritesEndpoint)
            .then(function(response) { 
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json(); 
            })
            .then(function(data) {
                // Clear existing list
                favoritesList.innerHTML = '';
                
                console.log('Raw API Response:', data);
                
                // Parse the favoriten field which is a JSON string
                let favorites = [];
                
                try {
                    if (data && data.favoriten) {
                        console.log('Data type of favoriten:', typeof data.favoriten);
                        
                        // If favoriten is a string, try to parse it
                        if (typeof data.favoriten === 'string') {
                            try {
                                // Try to parse the JSON string
                                favorites = JSON.parse(data.favoriten);
                                console.log('Parsed favorites from string:', favorites);
                            } catch (parseError) {
                                console.error('Error parsing favoriten as JSON:', parseError);
                                // If parsing fails, try cleaning it first
                                const cleanedJson = data.favoriten
                                    .replace(/\\"/g, '"') // Replace escaped quotes
                                    .replace(/\\n/g, '') // Remove escaped newlines
                                    .replace(/\\\\/g, '\\') // Fix double escaping
                                    .trim();
                                
                                console.log('Cleaned JSON:', cleanedJson);
                                
                                try {
                                    favorites = JSON.parse(cleanedJson);
                                    console.log('Parsed from cleaned JSON:', favorites);
                                } catch (secondError) {
                                    console.error('Second parse attempt failed:', secondError);
                                }
                            }
                        } 
                        // If favoriten is already an array, use it directly
                        else if (Array.isArray(data.favoriten)) {
                            favorites = data.favoriten;
                            console.log('Using direct array:', favorites);
                        }
                    } else {
                        console.warn('No favoriten field found in response');
                    }
                } catch (e) {
                    console.error('Error processing favorites:', e);
                }
                
                console.log('Final favorites array:', favorites);
                
                if (!Array.isArray(favorites) || favorites.length === 0) {
                    console.warn('No favorites to display');
                    favoritesList.innerHTML = '<li class="empty-item">No favorite questions found</li>';
                    return;
                }
                
                console.log('Adding', favorites.length, 'favorites to list');
                
                // Add each favorite to the list
                favorites.forEach(function(favorite, index) {
                    console.log(`Processing favorite ${index}:`, favorite);
                    
                    var li = document.createElement('li');
                    if (favorite && typeof favorite === 'object' && favorite.question) {
                        li.textContent = favorite.question;
                    } else if (favorite && typeof favorite === 'string') {
                        li.textContent = favorite;
                    } else {
                        li.textContent = 'Unknown question';
                        console.warn('Invalid favorite format:', favorite);
                    }
                    
                    li.onclick = function() {
                        // Get the input field and send button
                        var input = document.getElementById('message-input');
                        var sendButton = document.getElementById('send-button');
                        
                        // Set the input value to the selected question
                        if (input) {
                            input.value = li.textContent;
                        }
                        
                        // Close the modal
                        modal.style.display = 'none';
                        modal.classList.remove('show');
                        
                        // Trigger the send button click to automatically send the message
                        if (sendButton) {
                            setTimeout(function() {
                                sendButton.click();
                            }, 100); // Small delay to ensure the input value is set
                        }
                    };
                    favoritesList.appendChild(li);
                    console.log('Added favorite to list:', li.textContent);
                });
                
                // Add manage link
                var manageLi = document.createElement('li');
                manageLi.className = 'manage-favorites';
                manageLi.innerHTML = '<i class="fas fa-cog"></i> Manage Favorites';
                manageLi.onclick = function() {
                    window.location.href = 'manage-favorites.php';
                };
                favoritesList.appendChild(manageLi);
            })
            .catch(function(error) {
                console.error('Error loading favorites:', error);
                favoritesList.innerHTML = '<li class="error">Failed to load favorites: ' + error.message + '</li>';
            });
    }
    
    // Set up event listeners
    var favoritesBtn = document.getElementById('favorites-btn');
    var modal = document.getElementById('favorites-modal');
    var closeBtn = document.querySelector('.close');
    
    // Add context menu for user messages
    document.addEventListener('contextmenu', function(e) {
        // Check if the clicked element is a user message
        let target = e.target;
        while (target && !target.classList.contains('user-message')) {
            target = target.parentElement;
        }
        
        if (target && target.classList.contains('user-message')) {
            e.preventDefault(); // Prevent default context menu
            
            // Get the message text
            const messageText = target.textContent.trim();
            
            // Create a simple context menu
            const contextMenu = document.createElement('div');
            contextMenu.className = 'message-context-menu';
            contextMenu.innerHTML = '<div class="context-menu-item">Add to Favorites</div>';
            contextMenu.style.position = 'absolute';
            contextMenu.style.left = e.pageX + 'px';
            contextMenu.style.top = e.pageY + 'px';
            contextMenu.style.backgroundColor = '#fff';
            contextMenu.style.border = '1px solid #ccc';
            contextMenu.style.boxShadow = '0 2px 5px rgba(0,0,0,0.2)';
            contextMenu.style.borderRadius = '3px';
            contextMenu.style.padding = '5px 0';
            contextMenu.style.zIndex = '1000';
            
            // Add click handler for the menu item
            const menuItem = contextMenu.querySelector('.context-menu-item');
            menuItem.style.padding = '8px 12px';
            menuItem.style.cursor = 'pointer';
            menuItem.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#f0f0f0';
            });
            menuItem.addEventListener('mouseleave', function() {
                this.style.backgroundColor = 'transparent';
            });
            menuItem.addEventListener('click', function() {
                saveAsFavorite(messageText);
                document.body.removeChild(contextMenu);
            });
            
            // Add the menu to the document
            document.body.appendChild(contextMenu);
            
            // Remove the menu when clicking elsewhere
            function removeMenu(e) {
                if (!contextMenu.contains(e.target)) {
                    document.body.removeChild(contextMenu);
                    document.removeEventListener('click', removeMenu);
                }
            }
            
            setTimeout(function() {
                document.addEventListener('click', removeMenu);
            }, 0);
        }
    });
    
    if (favoritesBtn && modal) {
        console.log('Favorites button and modal found, setting up listeners');
        
        // Button click handler
        favoritesBtn.addEventListener('click', function(e) {
            console.log('Favorites button clicked');
            // Force modal display
            modal.style.display = 'block';
            modal.classList.add('show');
            loadFavorites();
        });
        
        // Close button
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                console.log('Close button clicked');
                modal.style.display = 'none';
                modal.classList.remove('show');
            });
        } else {
            console.error('Close button not found');
        }
        
        // Close when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                console.log('Clicked outside modal');
                modal.style.display = 'none';
                modal.classList.remove('show');
            }
        });
    } else {
        console.error('Favorites button or modal not found', {favoritesBtn, modal});
    }
}); 