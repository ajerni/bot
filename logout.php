<?php
// Include security functions
require_once 'includes/security.php';

// Initialize security
initSecurity();

// Destroy the session
session_unset();
session_destroy();

// Redirect to login page
header('Location: index.php');
exit; 