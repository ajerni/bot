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

// Initialize warp transition
function initWarpTransition() {
    // Get the href of the linked stylesheet to determine which animation to use
    const stylesheets = document.querySelectorAll('link[rel="stylesheet"]');
    let animationType = 'default';
    
    for (const sheet of stylesheets) {
        if (sheet.href.includes('animations')) {
            if (sheet.href.includes('animations1')) animationType = 'blackhole';
            if (sheet.href.includes('animations2')) animationType = 'planet';
            if (sheet.href.includes('animations3')) animationType = 'galaxy';
            if (sheet.href.includes('animations4')) animationType = 'nebula';
            if (sheet.href.includes('animations5')) animationType = 'teleport';
            break;
        }
    }
    
    // Initialize based on animation type
    switch (animationType) {
        case 'nebula':
            initNebulaClouds();
            break;
        case 'galaxy':
            initGalaxyCenter();
            break;
        case 'planet':
            initPlanetSurface();
            break;
        case 'teleport':
            initTeleportBeam();
            break;
        default:
            initDefaultWarp();
            break;
    }
}

// Initialize default warp stars
function initDefaultWarp() {
    const warpTransition = document.getElementById('warp-transition');
    const numLines = 100;
    
    for (let i = 0; i < numLines; i++) {
        const line = document.createElement('div');
        line.className = 'warp-star';
        
        // Random position
        const y = Math.random() * 100;
        const width = Math.random() * 50 + 10;
        const delay = Math.random() * 0.5;
        
        line.style.top = `${y}%`;
        line.style.left = `${Math.random() * 30}%`;
        line.style.width = `${width}px`;
        line.style.opacity = Math.random() * 0.7 + 0.3;
        line.style.transitionDelay = `${delay}s`;
        
        warpTransition.appendChild(line);
    }
}

// Initialize nebula clouds
function initNebulaClouds() {
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

// Initialize galaxy center
function initGalaxyCenter() {
    const warpTransition = document.getElementById('warp-transition');
    const center = document.createElement('div');
    center.className = 'galaxy-center';
    warpTransition.appendChild(center);
}

// Initialize planet surface
function initPlanetSurface() {
    const warpTransition = document.getElementById('warp-transition');
    const surface = document.createElement('div');
    surface.className = 'planet-surface';
    warpTransition.appendChild(surface);
}

// Initialize teleport beam
function initTeleportBeam() {
    const warpTransition = document.getElementById('warp-transition');
    const beam = document.createElement('div');
    beam.className = 'teleport-beam';
    warpTransition.appendChild(beam);
    
    // Add particles
    const particles = document.createElement('div');
    particles.className = 'particles';
    
    for (let i = 0; i < 50; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.left = `${Math.random() * 100}%`;
        particles.appendChild(particle);
    }
    
    warpTransition.appendChild(particles);
}

// Activate warp transition effect
function activateWarpEffect() {
    const content = document.getElementById('content');
    const warpTransition = document.getElementById('warp-transition');
    const flash = document.getElementById('flash');
    const stars = document.querySelectorAll('.star, .shining-star');
    
    // Get the animation type
    const stylesheets = document.querySelectorAll('link[rel="stylesheet"]');
    let animationType = 'default';
    
    for (const sheet of stylesheets) {
        if (sheet.href.includes('animations')) {
            if (sheet.href.includes('animations1')) animationType = 'blackhole';
            if (sheet.href.includes('animations2')) animationType = 'planet';
            if (sheet.href.includes('animations3')) animationType = 'galaxy';
            if (sheet.href.includes('animations4')) animationType = 'nebula';
            if (sheet.href.includes('animations5')) animationType = 'teleport';
            break;
        }
    }
    
    // Hide content with fade
    content.classList.add('fade-out');
    
    // Activate warp transition
    warpTransition.classList.add('active');
    
    // Apply specific animation based on type
    switch (animationType) {
        case 'blackhole':
            // Set coordinates for black hole effect
            stars.forEach(star => {
                const rect = star.getBoundingClientRect();
                star.style.setProperty('--x-pos', `${rect.left}px`);
                star.style.setProperty('--y-pos', `${rect.top}px`);
                star.classList.add('black-hole-effect');
            });
            break;
        case 'planet':
            // Move stars down for planet approach
            stars.forEach(star => {
                star.classList.add('planet-approach');
            });
            // Activate planet surface
            setTimeout(() => {
                document.querySelector('.planet-surface').classList.add('active');
            }, 500);
            break;
        case 'galaxy':
            // Rotate stars for galaxy spin
            stars.forEach(star => {
                const rect = star.getBoundingClientRect();
                star.style.setProperty('--x-pos', `${rect.left}px`);
                star.style.setProperty('--y-pos', `${rect.top}px`);
                star.classList.add('galaxy-spin');
            });
            // Activate galaxy center
            setTimeout(() => {
                document.querySelector('.galaxy-center').classList.add('active');
            }, 300);
            break;
        case 'nebula':
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
            break;
        case 'teleport':
            // Attract stars to beam
            stars.forEach(star => {
                const rect = star.getBoundingClientRect();
                star.style.setProperty('--x-pos', `${rect.left}px`);
                star.classList.add('beam-effect');
            });
            // Activate teleport beam
            setTimeout(() => {
                document.querySelector('.teleport-beam').classList.add('active');
                // Activate particles
                setTimeout(() => {
                    document.querySelectorAll('.particle').forEach((particle, i) => {
                        setTimeout(() => {
                            particle.classList.add('active');
                        }, Math.random() * 1000);
                    });
                }, 500);
            }, 300);
            break;
        default:
            // Default star warp
            const warpStars = document.querySelectorAll('.warp-star');
            warpStars.forEach(star => {
                setTimeout(() => {
                    star.style.transform = `scaleX(${20 + Math.random() * 30}) translateX(${window.innerWidth}px)`;
                    star.style.opacity = '0';
                }, Math.random() * 200);
            });
            
            // Hide regular stars
            stars.forEach(star => {
                star.style.animation = 'none';
                star.style.opacity = '0';
                star.style.transition = 'opacity 1s ease';
            });
            break;
    }
    
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