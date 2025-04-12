<?php
/**
 * QuickCourse Configuration
 * Main configuration file for the QuickCourse platform
 */

// Environment
define('ENVIRONMENT', 'development'); // 'development' or 'production'

// Site Info
define('SITE_NAME', 'קוויק קורס');
define('SITE_URL', 'http://localhost:2');
define('ADMIN_EMAIL', 'admin@quickcourse.com');

// S3 Configuration for file storage
define('USE_S3_STORAGE', ENVIRONMENT === 'production'); // Use S3 in production
define('S3_BUCKET', 'quickcourse-assets');
define('S3_REGION', 'eu-west-1');
define('S3_URL', 'https://' . S3_BUCKET . '.s3.' . S3_REGION . '.amazonaws.com');
define('S3_KEY', ''); // Fill in production
define('S3_SECRET', ''); // Fill in production

// Application Paths
define('ROOT_PATH', dirname(__DIR__));
define('UPLOADS_PATH', ROOT_PATH . '/assets/uploads');
define('COURSE_UPLOADS', UPLOADS_PATH . '/courses');
define('PROFILE_UPLOADS', UPLOADS_PATH . '/profiles');
define('LESSON_UPLOADS', UPLOADS_PATH . '/lessons');

// Security settings
define('SESSION_TIMEOUT', 3600); // in seconds (1 hour)
define('MAX_LOGIN_ATTEMPTS', 5);
define('MIN_PASSWORD_LENGTH', 8);
define('VERIFY_IP', true); // Check for IP changes
define('MAX_DEVICES', 3); // Maximum number of devices per account

// User Types
define('USER_ADMIN', 'admin');
define('USER_INSTRUCTOR', 'instructor');
define('USER_STUDENT', 'student');

// Package Types
define('PACKAGE_TEACHER', 1); // "מורה פרטי"
define('PACKAGE_COORDINATOR', 2); // "רכז שכבה"
define('PACKAGE_PRINCIPAL', 3); // "מנהל בית ספר"

// Course Status
define('COURSE_DRAFT', 'draft');
define('COURSE_PUBLISHED', 'published');
define('COURSE_ARCHIVED', 'archived');

// Media settings
define('MAX_UPLOAD_SIZE', 100 * 1024 * 1024); // 100MB
define('ALLOWED_VIDEO_TYPES', ['mp4']);
define('ALLOWED_DOC_TYPES', ['pdf']);
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png']);

// Payment settings
define('CURRENCY', 'ILS');
define('CURRENCY_SYMBOL', '₪');

// Debug mode
define('DEBUG_MODE', true);