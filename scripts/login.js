// Handle form submission to prevent content shift
document.addEventListener('DOMContentLoaded', function() {
    // If login was successful, trigger the warp animation before redirecting
    if (loginSuccess) {
        var title = document.querySelector('.title');
        if (title) {
            title.style.display = 'none';
        }
        
        var loginContainer = document.getElementById('login-container');
        if (loginContainer) {
            loginContainer.style.display = 'none';
        }
        
        // Trigger warp animation
        var warpTransition = document.getElementById('warp-transition');
        if (warpTransition) {
            warpTransition.classList.add('active');
        }
        
        // Show flash effect after short delay
        setTimeout(function() {
            var flash = document.getElementById('flash');
            if (flash) {
                flash.classList.add('active');
            }
            
            // Redirect to chat.php after animation completes
            setTimeout(function() {
                window.location.href = 'chat.php';
            }, 1500); // Timing for flash animation
        }, 1500); // Adjusted to show flash earlier
        
        return;
    }
    
    var form = document.querySelector('.login-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Prevent default form submission - we'll handle it via AJAX
            e.preventDefault();
            
            // Fix positions immediately
            document.body.style.overflow = 'hidden'; // Prevent scrolling
            
            // Fix and fade out title
            var title = document.querySelector('.title');
            if (title) {
                var titleRect = title.getBoundingClientRect();
                title.style.position = 'fixed';
                title.style.top = titleRect.top + 'px';
                title.style.left = titleRect.left + 'px';
                title.style.width = titleRect.width + 'px';
                title.style.height = titleRect.height + 'px';
                title.classList.add('fade-out');
            }
            
            // Fix and fade out login container
            var loginContainer = document.getElementById('login-container');
            if (loginContainer) {
                var rect = loginContainer.getBoundingClientRect();
                loginContainer.style.position = 'fixed';
                loginContainer.style.top = rect.top + 'px';
                loginContainer.style.left = rect.left + 'px';
                loginContainer.style.width = rect.width + 'px';
                loginContainer.style.height = rect.height + 'px';
                loginContainer.style.margin = '0';
                loginContainer.style.transform = 'none';
                loginContainer.classList.add('submitting');
            }
            
            // Fix content position
            var content = document.getElementById('content');
            if (content) {
                content.style.position = 'fixed';
                content.style.top = '0';
                content.style.left = '0';
                content.style.width = '100%';
                content.style.height = '100%';
                content.style.transform = 'none';
            }
            
            // Submit form
            form.submit();
        });
    }
    
    // Handle access denied overlay hiding after delay
    if (showAccessDenied) {
        setTimeout(function() {
            var overlay = document.getElementById('access-denied-overlay');
            if (overlay) {
                overlay.style.opacity = '0';
                setTimeout(function() {
                    overlay.style.display = 'none';
                }, 500);
            }
        }, 3000);
    }
}); 