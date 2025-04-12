<?php
/**
 * Global Functions
 * Common utility functions for the QuickCourse platform
 */

// Load configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

/**
 * Redirect to a specific URL
 */
function redirect($path) {
    header("Location: " . SITE_URL . $path);
    exit;
}

/**
 * Sanitize user input
 */
function sanitize($input) {
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = sanitize($value);
        }
        return $input;
    }
    
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Create a unique slug from a string
 */
function createSlug($string) {
    $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
    $slug = strtolower(trim($slug, '-'));
    return $slug;
}

/**
 * Format price with currency symbol
 */
function formatPrice($price) {
    return CURRENCY_SYMBOL . number_format($price, 2);
}

/**
 * Format date to local format
 */
function formatDate($date, $format = 'd/m/Y H:i') {
    $dateObj = new DateTime($date);
    return $dateObj->format($format);
}

/**
 * Generate random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

/**
 * Check if user has permission for the specified package feature
 */
function hasPackagePermission($userId, $feature) {
    $sql = "SELECT p.features FROM users u 
            JOIN packages p ON u.package_id = p.package_id 
            WHERE u.user_id = ? AND u.subscription_status = 'active'";
    
    $result = Database::fetchOne($sql, [$userId]);
    
    if (!$result) {
        return false;
    }
    
    $features = json_decode($result['features'], true);
    return in_array($feature, $features);
}

/**
 * Check if user can create more courses based on their package
 */
function canCreateMoreCourses($userId) {
    $sql = "SELECT u.package_id, p.max_courses, COUNT(c.course_id) as course_count 
            FROM users u 
            JOIN packages p ON u.package_id = p.package_id 
            LEFT JOIN courses c ON u.user_id = c.user_id 
            WHERE u.user_id = ? AND u.subscription_status = 'active'
            GROUP BY u.user_id";
    
    $result = Database::fetchOne($sql, [$userId]);
    
    if (!$result) {
        return false;
    }
    
    return $result['course_count'] < $result['max_courses'];
}

/**
 * Generate pagination
 */
function pagination($current_page, $total_pages, $url_pattern) {
    $output = '<div class="flex justify-center mt-4">';
    $output .= '<nav class="inline-flex">';
    
    // Previous button
    if ($current_page > 1) {
        $output .= '<a href="' . sprintf($url_pattern, $current_page - 1) . '" class="px-3 py-2 border border-gray-300 text-sm leading-5 font-medium text-gray-700 bg-white hover:bg-gray-50">הקודם</a>';
    } else {
        $output .= '<span class="px-3 py-2 border border-gray-300 text-sm leading-5 font-medium text-gray-300 bg-white">הקודם</span>';
    }
    
    // Page numbers
    for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++) {
        if ($i == $current_page) {
            $output .= '<span class="px-3 py-2 border border-blue-500 text-sm leading-5 font-medium text-blue-600 bg-blue-50">' . $i . '</span>';
        } else {
            $output .= '<a href="' . sprintf($url_pattern, $i) . '" class="px-3 py-2 border border-gray-300 text-sm leading-5 font-medium text-gray-700 bg-white hover:bg-gray-50">' . $i . '</a>';
        }
    }
    
    // Next button
    if ($current_page < $total_pages) {
        $output .= '<a href="' . sprintf($url_pattern, $current_page + 1) . '" class="px-3 py-2 border border-gray-300 text-sm leading-5 font-medium text-gray-700 bg-white hover:bg-gray-50">הבא</a>';
    } else {
        $output .= '<span class="px-3 py-2 border border-gray-300 text-sm leading-5 font-medium text-gray-300 bg-white">הבא</span>';
    }
    
    $output .= '</nav>';
    $output .= '</div>';
    
    return $output;
}

/**
 * Format file size in human-readable format
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * Calculate used storage for user
 */
function getUserStorageUsed($userId) {
    $sql = "SELECT SUM(size) as total_size FROM (
                SELECT file_size as size FROM course_files WHERE user_id = ?
                UNION ALL
                SELECT file_size as size FROM lesson_files WHERE user_id = ?
            ) AS files";
    
    $result = Database::fetchOne($sql, [$userId, $userId]);
    
    return $result['total_size'] ?? 0;
}

/**
 * Get user progress for a course
 */
function getCourseProgress($userId, $courseId) {
    $sql = "SELECT 
                COUNT(DISTINCT lp.lesson_id) as completed_lessons,
                COUNT(DISTINCT l.lesson_id) as total_lessons
            FROM courses c
            JOIN chapters ch ON c.course_id = ch.course_id
            JOIN lessons l ON ch.chapter_id = l.chapter_id
            LEFT JOIN lesson_progress lp ON l.lesson_id = lp.lesson_id AND lp.user_id = ? AND lp.status = 'completed'
            WHERE c.course_id = ?";
    
    $result = Database::fetchOne($sql, [$userId, $courseId]);
    
    if (!$result || $result['total_lessons'] == 0) {
        return 0;
    }
    
    return round(($result['completed_lessons'] / $result['total_lessons']) * 100);
}

/**
 * Show flash message
 */
function showFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        
        $class = 'bg-blue-100 border-blue-500 text-blue-700';
        if ($type == 'success') {
            $class = 'bg-green-100 border-green-500 text-green-700';
        } else if ($type == 'error') {
            $class = 'bg-red-100 border-red-500 text-red-700';
        } else if ($type == 'warning') {
            $class = 'bg-yellow-100 border-yellow-500 text-yellow-700';
        }
        
        echo '<div class="' . $class . ' px-4 py-3 rounded relative mb-4 border-r-4" role="alert">';
        echo '<span class="block sm:inline">' . $message . '</span>';
        echo '<span class="absolute top-0 bottom-0 left-0 px-4 py-3">';
        echo '<svg class="fill-current h-6 w-6" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>סגור</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>';
        echo '</span>';
        echo '</div>';
        
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}

/**
 * Set flash message
 */
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Check if file type is allowed
 */
function isAllowedFileType($file, $type) {
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    switch ($type) {
        case 'video':
            return in_array($extension, ALLOWED_VIDEO_TYPES);
        case 'document':
            return in_array($extension, ALLOWED_DOC_TYPES);
        case 'image':
            return in_array($extension, ALLOWED_IMAGE_TYPES);
        default:
            return false;
    }
}

/**
 * Get asset URL (supports S3 in production)
 */
function getAssetUrl($path) {
    // Check if we're using S3 for assets
    if (defined('USE_S3_STORAGE') && USE_S3_STORAGE) {
        return S3_URL . '/' . $path;
    }
    
    // Local development - use local path
    return SITE_URL . '/' . $path;
}

/**
 * Upload file and return path
 */
function uploadFile($file, $destination, $type) {
    if (!isAllowedFileType($file, $type)) {
        return false;
    }
    
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return false;
    }
    
    // Create unique filename
    $filename = uniqid() . '_' . basename($file['name']);
    
    if (USE_S3_STORAGE) {
        // Upload to S3
        if (uploadToS3($file['tmp_name'], $destination . '/' . $filename)) {
            return $filename;
        }
    } else {
        // Local upload
        $target = $destination . '/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $target)) {
            return $filename;
        }
    }
    
    return false;
}

/**
 * Upload file to S3
 */
function uploadToS3($localFile, $s3Path) {
    if (!USE_S3_STORAGE) {
        return false;
    }
    
    // Require AWS SDK if not already loaded
    if (!class_exists('Aws\S3\S3Client')) {
        // Check if Composer autoloader exists
        if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
            require_once ROOT_PATH . '/vendor/autoload.php';
        } else {
            // AWS SDK not available
            error_log('AWS SDK not available. Cannot upload to S3.');
            return false;
        }
    }
    
    try {
        // Create S3 client
        $s3 = new Aws\S3\S3Client([
            'version' => 'latest',
            'region'  => S3_REGION,
            'credentials' => [
                'key'    => S3_KEY,
                'secret' => S3_SECRET,
            ],
        ]);
        
        // Upload the file
        $result = $s3->putObject([
            'Bucket' => S3_BUCKET,
            'Key'    => $s3Path,
            'SourceFile' => $localFile,
            'ACL'    => 'public-read',
        ]);
        
        return $result['ObjectURL'] ?? false;
    } catch (Exception $e) {
        error_log('S3 upload error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Delete file (local or S3)
 */
function deleteFile($path) {
    if (USE_S3_STORAGE) {
        return deleteFromS3($path);
    } else {
        return @unlink(ROOT_PATH . '/' . $path);
    }
}

/**
 * Delete file from S3
 */
function deleteFromS3($s3Path) {
    if (!USE_S3_STORAGE) {
        return false;
    }
    
    // Require AWS SDK if not already loaded
    if (!class_exists('Aws\S3\S3Client')) {
        // Check if Composer autoloader exists
        if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
            require_once ROOT_PATH . '/vendor/autoload.php';
        } else {
            // AWS SDK not available
            error_log('AWS SDK not available. Cannot delete from S3.');
            return false;
        }
    }
    
    try {
        // Create S3 client
        $s3 = new Aws\S3\S3Client([
            'version' => 'latest',
            'region'  => S3_REGION,
            'credentials' => [
                'key'    => S3_KEY,
                'secret' => S3_SECRET,
            ],
        ]);
        
        // Delete the file
        $result = $s3->deleteObject([
            'Bucket' => S3_BUCKET,
            'Key'    => $s3Path,
        ]);
        
        return true;
    } catch (Exception $e) {
        error_log('S3 delete error: ' . $e->getMessage());
        return false;
    }
}