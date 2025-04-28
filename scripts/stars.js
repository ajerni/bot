// Create stars
function createStars() {
    const starsContainer = document.getElementById('stars');
    const numberOfStars = 150; // Regular stars
    const numberOfShiningStars = 20; // Special 4-point stars
    
    // Create regular stars
    for (let i = 0; i < numberOfStars; i++) {
        const star = document.createElement('div');
        star.className = 'star';
        
        // Random position
        const x = Math.random() * 100;
        const y = Math.random() * 100;
        
        // Random size (0.5px to 3px)
        const size = Math.random() * 2.5 + 0.5;
        
        star.style.left = `${x}%`;
        star.style.top = `${y}%`;
        star.style.width = `${size}px`;
        star.style.height = `${size}px`;
        
        // Make some stars twinkle with random delay
        if (Math.random() > 0.7) {
            star.classList.add('twinkle');
            star.style.animationDelay = `${Math.random() * 4}s`;
        }
        
        starsContainer.appendChild(star);
    }
    
    // Create shining 4-point stars (✦ or ✧)
    for (let i = 0; i < numberOfShiningStars; i++) {
        const shiningStar = document.createElement('div');
        shiningStar.className = 'shining-star';
        shiningStar.innerHTML = '✧';
        
        // Random position
        const x = Math.random() * 100;
        const y = Math.random() * 100;
        
        // Random size
        const size = Math.random() * 20 + 10;
        
        shiningStar.style.left = `${x}%`;
        shiningStar.style.top = `${y}%`;
        shiningStar.style.fontSize = `${size}px`;
        
        // Make some stars twinkle with random delay
        if (Math.random() > 0.5) {
            shiningStar.classList.add('twinkle');
            shiningStar.style.animationDelay = `${Math.random() * 4}s`;
        }
        
        starsContainer.appendChild(shiningStar);
    }
}

// Initialize nebula clouds for transition
function initWarpTransition() {
    const warpTransition = document.getElementById('warp-transition');
    const colors = ['nebula-red', 'nebula-blue', 'nebula-purple', 'nebula-teal'];
    const numClouds = 8;
    
    for (let i = 0; i < numClouds; i++) {
        const cloud = document.createElement('div');
        cloud.className = `nebula-cloud ${colors[Math.floor(Math.random() * colors.length)]}`;
        
        // Random position
        const x = Math.random() * 100;
        const y = Math.random() * 100;
        const size = Math.random() * 200 + 100;
        
        cloud.style.left = `${x}%`;
        cloud.style.top = `${y}%`;
        cloud.style.width = `${size}px`;
        cloud.style.height = `${size}px`;
        
        warpTransition.appendChild(cloud);
    }
}

// Activate warp transition effect
function activateWarpEffect() {
    // Lock scrolling and mark body for transition
    document.body.classList.add('transition-active');
    document.body.style.overflow = 'hidden';
    
    // Get elements
    const content = document.getElementById('content');
    const warpTransition = document.getElementById('warp-transition');
    const flash = document.getElementById('flash');
    const stars = document.querySelectorAll('.star, .shining-star');
    const loginContainer = document.getElementById('login-container');
    const title = document.querySelector('.title');
    
    // Take a snapshot of current positions
    const contentRect = content.getBoundingClientRect();
    
    // Fix title position if it exists
    if (title) {
        const titleRect = title.getBoundingClientRect();
        title.style.position = 'fixed';
        title.style.top = `${titleRect.top}px`;
        title.style.left = `${titleRect.left}px`;
        title.style.width = `${titleRect.width}px`;
        title.style.height = `${titleRect.height}px`;
        title.style.margin = '0';
        title.style.transform = 'none';
        title.classList.add('fade-out');
    }
    
    // Fix login container position if it exists
    if (loginContainer) {
        const rect = loginContainer.getBoundingClientRect();
        loginContainer.style.position = 'fixed';
        loginContainer.style.top = `${rect.top}px`;
        loginContainer.style.left = `${rect.left}px`;
        loginContainer.style.width = `${rect.width}px`;
        loginContainer.style.height = `${rect.height}px`;
        loginContainer.style.margin = '0';
        loginContainer.style.transform = 'none';
        loginContainer.classList.add('submitting');
    }
    
    // Fix content position
    content.style.position = 'fixed';
    content.style.top = `${contentRect.top}px`;
    content.style.left = `${contentRect.left}px`;
    content.style.width = `${contentRect.width}px`;
    content.style.height = `${contentRect.height}px`;
    content.style.margin = '0';
    content.style.transform = 'none';
    content.classList.add('fade-out');
    
    // Activate warp transition
    warpTransition.classList.add('active');
    
    // Transform stars into nebula
    stars.forEach(star => {
        star.classList.add('nebula-effect');
    });
    
    // Activate nebula clouds
    setTimeout(() => {
        document.querySelectorAll('.nebula-cloud').forEach(cloud => {
            cloud.classList.add('active');
        });
    }, 1000);
    
    // Flash effect
    setTimeout(() => {
        flash.style.animation = 'flash 1.5s forwards';
        
        // Redirect after animation
        setTimeout(() => {
            window.location.href = 'chat.php';
        }, 1500);
    }, 1200);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    createStars();
    initWarpTransition();
    
    // Check if login was successful via a global variable set in the HTML
    if (typeof loginSuccess !== 'undefined' && loginSuccess === true) {
        setTimeout(activateWarpEffect, 500);
    }
}); 