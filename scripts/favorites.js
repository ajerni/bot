// Favorites functionality
document.addEventListener('DOMContentLoaded', function() {
    // API endpoints for favorites
    const getFavoritesEndpoint = 'https://n8n.ernilabs.com/webhook/getfavorites';
    const saveFavoritesEndpoint = 'https://n8n.ernilabs.com/webhook/savefavorites';
    
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