<?php
/**
 * Header template
 * Includes site header with navigation and user info
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load necessary files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

// Get current user info if logged in
$currentUser = null;
$userType = null;

if (isLoggedIn()) {
    $currentUser = getCurrentUser();
    $userType = getCurrentUserType();
}

// Determine active page
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME ?></title>
    
    <!-- Tailwind CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    <!-- Remix Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <style>
        /* Custom fonts */
        @import url('https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Rubik', sans-serif;
            background-color: #f7f9fc;
        }
        
        /* Monday.com inspired colors */
        .primary-bg { background-color: #0073ea; }
        .primary-hover:hover { background-color: #0060c0; }
        .secondary-bg { background-color: #f5f6f8; }
        .accent-bg { background-color: #00ca72; }
        .accent-hover:hover { background-color: #00a45a; }
        .pastel-blue-bg { background-color: #e6f4ff; }
        .pastel-green-bg { background-color: #dffbf4; }
        .pastel-purple-bg { background-color: #f5ebff; }
        .pastel-yellow-bg { background-color: #fff7d9; }
        .pastel-red-bg { background-color: #ffe5e5; }
        
        /* Modern card styles */
        .card {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }
        
        .card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        /* Custom button styles */
        .btn-primary {
            background-color: #0073ea;
            color: white;
            transition: all 0.2s ease;
        }
        
        .btn-primary:hover {
            background-color: #0060c0;
        }
        
        .btn-success {
            background-color: #00ca72;
            color: white;
            transition: all 0.2s ease;
        }
        
        .btn-success:hover {
            background-color: #00a45a;
        }
        
        /* Sidebar active item */
        .sidebar-active {
            background-color: #e6f4ff;
            border-right: 3px solid #0073ea;
            color: #0073ea;
        }
        
        /* Progress bar */
        .progress-bar {
            height: 8px;
            border-radius: 4px;
            background-color: #f0f0f0;
            overflow: hidden;
        }
        
        .progress-value {
            height: 100%;
            background-color: #0073ea;
            border-radius: 4px;
        }
    </style>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="flex flex-col min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="<?= SITE_URL ?>" class="text-2xl font-bold text-blue-600">
                        <i class="ri-book-open-line mr-2"></i>
                        <?= SITE_NAME ?>
                    </a>
                </div>
                
                <!-- Navigation -->
                <nav class="hidden md:flex space-x-1 mr-4">
                    <?php if (!isLoggedIn()): ?>
                        <a href="<?= SITE_URL ?>/courses" class="px-3 py-2 rounded hover:bg-gray-100 text-gray-700 <?= ($currentPage == 'courses.php') ? 'bg-gray-100 font-medium' : '' ?>">
                            <i class="ri-book-2-line mr-1"></i> קורסים
                        </a>
                        <a href="<?= SITE_URL ?>/pricing.php" class="px-3 py-2 rounded hover:bg-gray-100 text-gray-700 <?= ($currentPage == 'pricing.php') ? 'bg-gray-100 font-medium' : '' ?>">
                            <i class="ri-price-tag-3-line mr-1"></i> תמחור
                        </a>
                        <a href="<?= SITE_URL ?>/about.php" class="px-3 py-2 rounded hover:bg-gray-100 text-gray-700 <?= ($currentPage == 'about.php') ? 'bg-gray-100 font-medium' : '' ?>">
                            <i class="ri-information-line mr-1"></i> אודות
                        </a>
                        <a href="<?= SITE_URL ?>/contact.php" class="px-3 py-2 rounded hover:bg-gray-100 text-gray-700 <?= ($currentPage == 'contact.php') ? 'bg-gray-100 font-medium' : '' ?>">
                            <i class="ri-customer-service-2-line mr-1"></i> צור קשר
                        </a>
                    <?php elseif ($userType == USER_STUDENT): ?>
                        <a href="<?= SITE_URL ?>/student/courses" class="px-3 py-2 rounded hover:bg-gray-100 text-gray-700 <?= (strpos($currentPage, 'student/courses') !== false) ? 'bg-gray-100 font-medium' : '' ?>">
                            <i class="ri-book-open-line mr-1"></i> הקורסים שלי
                        </a>
                        <a href="<?= SITE_URL ?>/courses" class="px-3 py-2 rounded hover:bg-gray-100 text-gray-700 <?= ($currentPage == 'courses.php') ? 'bg-gray-100 font-medium' : '' ?>">
                            <i class="ri-store-2-line mr-1"></i> חנות הקורסים
                        </a>
                    <?php elseif ($userType == USER_INSTRUCTOR): ?>
                        <a href="<?= SITE_URL ?>/instructor/courses" class="px-3 py-2 rounded hover:bg-gray-100 text-gray-700 <?= (strpos($currentPage, 'instructor/courses') !== false) ? 'bg-gray-100 font-medium' : '' ?>">
                            <i class="ri-book-open-line mr-1"></i> הקורסים שלי
                        </a>
                        <a href="<?= SITE_URL ?>/instructor/students" class="px-3 py-2 rounded hover:bg-gray-100 text-gray-700 <?= (strpos($currentPage, 'instructor/students') !== false) ? 'bg-gray-100 font-medium' : '' ?>">
                            <i class="ri-user-star-line mr-1"></i> תלמידים
                        </a>
                        <a href="<?= SITE_URL ?>/instructor/sales" class="px-3 py-2 rounded hover:bg-gray-100 text-gray-700 <?= (strpos($currentPage, 'instructor/sales') !== false) ? 'bg-gray-100 font-medium' : '' ?>">
                            <i class="ri-line-chart-line mr-1"></i> מכירות
                        </a>
                    <?php elseif ($userType == USER_ADMIN): ?>
                        <a href="<?= SITE_URL ?>/admin" class="px-3 py-2 rounded hover:bg-gray-100 text-gray-700 <?= (strpos($currentPage, 'admin') !== false) ? 'bg-gray-100 font-medium' : '' ?>">
                            <i class="ri-dashboard-line mr-1"></i> ניהול
                        </a>
                    <?php endif; ?>
                </nav>
                
                <!-- User Actions -->
                <div class="flex items-center space-x-2">
                    <?php if (isLoggedIn()): ?>
                        <!-- Notifications -->
                        <div class="relative mr-2">
                            <button class="p-2 rounded-full hover:bg-gray-100">
                                <i class="ri-notification-3-line text-gray-600 text-xl"></i>
                                <span class="absolute top-0 right-0 h-4 w-4 bg-red-500 rounded-full text-xs text-white flex items-center justify-center">3</span>
                            </button>
                        </div>
                        
                        <!-- User Dropdown -->
                        <div class="relative inline-block text-left">
                            <div>
                                <button type="button" class="inline-flex items-center justify-center w-full rounded-md px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                                    <?php if (!empty($currentUser['profile_image'])): ?>
                                        <img src="<?= SITE_URL ?>/assets/uploads/profiles/<?= $currentUser['profile_image'] ?>" alt="Profile" class="h-8 w-8 rounded-full mr-2">
                                    <?php else: ?>
                                        <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center mr-2">
                                            <span class="text-blue-600 font-medium">
                                                <?= substr($currentUser['first_name'], 0, 1) . substr($currentUser['last_name'], 0, 1) ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    <span><?= $currentUser['first_name'] ?></span>
                                    <i class="ri-arrow-down-s-line mr-1"></i>
                                </button>
                            </div>
                            
                            <!-- Dropdown menu -->
                            <div class="hidden origin-top-right absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none user-dropdown" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1">
                                <div class="py-1" role="none">
                                    <?php if ($userType == USER_STUDENT): ?>
                                        <a href="<?= SITE_URL ?>/student/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                            <i class="ri-user-line mr-2"></i> הפרופיל שלי
                                        </a>
                                    <?php elseif ($userType == USER_INSTRUCTOR): ?>
                                        <a href="<?= SITE_URL ?>/instructor/settings/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                            <i class="ri-user-line mr-2"></i> הפרופיל שלי
                                        </a>
                                        <a href="<?= SITE_URL ?>/instructor/settings/billing.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                            <i class="ri-bank-card-line mr-2"></i> חיוב ותשלומים
                                        </a>
                                        <a href="<?= SITE_URL ?>/instructor/settings/branding.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                            <i class="ri-palette-line mr-2"></i> מיתוג
                                        </a>
                                    <?php elseif ($userType == USER_ADMIN): ?>
                                        <a href="<?= SITE_URL ?>/admin/settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                            <i class="ri-settings-line mr-2"></i> הגדרות מערכת
                                        </a>
                                    <?php endif; ?>
                                    
                                    <div class="border-t border-gray-100 my-1"></div>
                                    
                                    <a href="<?= SITE_URL ?>/auth/logout.php" class="block px-4 py-2 text-sm text-red-700 hover:bg-gray-100" role="menuitem">
                                        <i class="ri-logout-box-r-line mr-2"></i> התנתק
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?= SITE_URL ?>/auth/login.php" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            התחברות
                        </a>
                        <a href="<?= SITE_URL ?>/auth/register.php" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">
                            הרשמה
                        </a>
                    <?php endif; ?>
                    
                    <!-- Mobile menu button -->
                    <button type="button" class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none" id="mobile-menu-button">
                        <i class="ri-menu-line text-xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Mobile menu -->
            <div class="hidden md:hidden mt-3 pt-2 border-t border-gray-200 mobile-menu">
                <div class="space-y-1 px-2 pb-3">
                    <?php if (!isLoggedIn()): ?>
                        <a href="<?= SITE_URL ?>/courses" class="block px-3 py-2 rounded-md text-base font-medium <?= ($currentPage == 'courses.php') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-50' ?>">
                            <i class="ri-book-2-line mr-2"></i> קורסים
                        </a>
                        <a href="<?= SITE_URL ?>/pricing.php" class="block px-3 py-2 rounded-md text-base font-medium <?= ($currentPage == 'pricing.php') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-50' ?>">
                            <i class="ri-price-tag-3-line mr-2"></i> תמחור
                        </a>
                        <a href="<?= SITE_URL ?>/about.php" class="block px-3 py-2 rounded-md text-base font-medium <?= ($currentPage == 'about.php') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-50' ?>">
                            <i class="ri-information-line mr-2"></i> אודות
                        </a>
                        <a href="<?= SITE_URL ?>/contact.php" class="block px-3 py-2 rounded-md text-base font-medium <?= ($currentPage == 'contact.php') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-50' ?>">
                            <i class="ri-customer-service-2-line mr-2"></i> צור קשר
                        </a>
                    <?php elseif ($userType == USER_STUDENT): ?>
                        <a href="<?= SITE_URL ?>/student/courses" class="block px-3 py-2 rounded-md text-base font-medium <?= (strpos($currentPage, 'student/courses') !== false) ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-50' ?>">
                            <i class="ri-book-open-line mr-2"></i> הקורסים שלי
                        </a>
                        <a href="<?= SITE_URL ?>/courses" class="block px-3 py-2 rounded-md text-base font-medium <?= ($currentPage == 'courses.php') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-50' ?>">
                            <i class="ri-store-2-line mr-2"></i> חנות הקורסים
                        </a>
                        <a href="<?= SITE_URL ?>/student/profile.php" class="block px-3 py-2 rounded-md text-base font-medium <?= ($currentPage == 'profile.php') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-50' ?>">
                            <i class="ri-user-line mr-2"></i> הפרופיל שלי
                        </a>
                    <?php elseif ($userType == USER_INSTRUCTOR): ?>
                        <a href="<?= SITE_URL ?>/instructor/courses" class="block px-3 py-2 rounded-md text-base font-medium <?= (strpos($currentPage, 'instructor/courses') !== false) ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-50' ?>">
                            <i class="ri-book-open-line mr-2"></i> הקורסים שלי
                        </a>
                        <a href="<?= SITE_URL ?>/instructor/students" class="block px-3 py-2 rounded-md text-base font-medium <?= (strpos($currentPage, 'instructor/students') !== false) ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-50' ?>">
                            <i class="ri-user-star-line mr-2"></i> תלמידים
                        </a>
                        <a href="<?= SITE_URL ?>/instructor/sales" class="block px-3 py-2 rounded-md text-base font-medium <?= (strpos($currentPage, 'instructor/sales') !== false) ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-50' ?>">
                            <i class="ri-line-chart-line mr-2"></i> מכירות
                        </a>
                        <a href="<?= SITE_URL ?>/instructor/settings/profile.php" class="block px-3 py-2 rounded-md text-base font-medium <?= (strpos($currentPage, 'instructor/settings/profile') !== false) ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-50' ?>">
                            <i class="ri-user-line mr-2"></i> הפרופיל שלי
                        </a>
                    <?php elseif ($userType == USER_ADMIN): ?>
                        <a href="<?= SITE_URL ?>/admin" class="block px-3 py-2 rounded-md text-base font-medium <?= (strpos($currentPage, 'admin') !== false) ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-50' ?>">
                            <i class="ri-dashboard-line mr-2"></i> ניהול
                        </a>
                        <a href="<?= SITE_URL ?>/admin/settings.php" class="block px-3 py-2 rounded-md text-base font-medium <?= ($currentPage == 'settings.php') ? 'bg-gray-100 text-blue-600' : 'text-gray-700 hover:bg-gray-50' ?>">
                            <i class="ri-settings-line mr-2"></i> הגדרות מערכת
                        </a>
                    <?php endif; ?>
                    
                    <?php if (isLoggedIn()): ?>
                    <div class="border-t border-gray-200 my-2"></div>
                    <a href="<?= SITE_URL ?>/auth/logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-red-600 hover:bg-red-50">
                        <i class="ri-logout-box-r-line mr-2"></i> התנתק
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Flash Messages -->
    <div class="container mx-auto px-4 mt-4">
        <?php showFlashMessage(); ?>
    </div>
    
    <!-- Main Content Container -->
    <main class="flex-grow container mx-auto px-4 py-6">
    
    <script>
        // User dropdown toggle
        $(document).ready(function() {
            $('#user-menu-button').click(function() {
                $('.user-dropdown').toggleClass('hidden');
            });
            
            // Close dropdown when clicking outside
            $(document).click(function(e) {
                if (!$(e.target).closest('#user-menu-button, .user-dropdown').length) {
                    $('.user-dropdown').addClass('hidden');
                }
            });
            
            // Mobile menu toggle
            $('#mobile-menu-button').click(function() {
                $('.mobile-menu').toggleClass('hidden');
            });
        });
    </script>