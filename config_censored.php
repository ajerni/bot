<?php
// Security configuration
return [
    // Password hash - generate this once and store it (use the tool under utils/generate_hash.php)
    'password_hash' => '$2y$ddddsafdd&%lx71OhDrp4rrEDL3TvuorVYi6', // This is a hash of 'password' - you should change this!
    
    // Session settings
    'session_timeout' => 3600, // 1 hour
    'max_login_attempts' => 5,
    'lockout_time' => 900, // 15 minutes
    
    // Security headers
    'security_headers' => [
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
        'X-Frame-Options' => 'DENY',
        'X-Content-Type-Options' => 'nosniff',
        'X-XSS-Protection' => '1; mode=block',
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; font-src 'self' https://cdnjs.cloudflare.com data:; img-src 'self' data: https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; connect-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://your.domain.com;"
    ]
]; 