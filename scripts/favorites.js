// Favorites functionality
document.addEventListener('DOMContentLoaded', function() {
    // Simple function to load favorites from JSON
    function loadFavorites() {
        var favoritesList = document.getElementById('favorites-list');
        var modal = document.getElementById('favorites-modal');
        
        if (!favoritesList || !modal) {
            console.error('Required elements not found');
            return;
        }
        
        // Clear existing list
        favoritesList.innerHTML = '';
        
        // Fetch data from JSON file
        fetch('favorites.json')
            .then(function(response) { 
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json(); 
            })
            .then(function(data) {
                // Add each favorite to the list
                data.forEach(function(favorite) {
                    var li = document.createElement('li');
                    li.textContent = favorite.question;
                    li.onclick = function() {
                        // Get the input field and send button
                        var input = document.getElementById('message-input');
                        var sendButton = document.getElementById('send-button');
                        
                        // Set the input value to the selected question
                        if (input) input.value = favorite.question;
                        
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
                favoritesList.innerHTML = '<li class="error">Failed to load favorites. Please try again.</li>';
            });
    }
    
    // Set up event listeners
    var favoritesBtn = document.getElementById('favorites-btn');
    var modal = document.getElementById('favorites-modal');
    var closeBtn = document.querySelector('.close');
    
    if (favoritesBtn && modal) {
        // Button click handler
        favoritesBtn.addEventListener('click', function(e) {
            modal.style.display = 'block';
            loadFavorites();
        });
        
        // Close button
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        }
        
        // Close when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });
    }
}); 