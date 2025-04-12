<?php
/**
 * QuickCourse - Logout Page
 * User logout functionality
 */

// Include required files
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Logout user
logoutUser();

// Redirect to home page
redirect('/');
?>