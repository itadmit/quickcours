<?php
/**
 * QuickCourse - Delete Course
 * Handles course deletion
 */

// Include required files
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

// Start session if not already started
startSession();

// Check if user is logged in and is an instructor
if (!isLoggedIn() || !isInstructor()) {
    // Redirect to login page
    redirect('/auth/login.php');
}

// Get user info
$userId = getCurrentUserId();

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    // Redirect to courses page
    redirect('/instructor/courses');
}

// Get course ID
$courseId = isset($_POST['course_id']) ? (int)$_POST['course_id'] : 0;

if ($courseId <= 0) {
    // Invalid course ID
    setFlashMessage('מזהה קורס לא חוקי.', 'error');
    redirect('/instructor/courses');
}

// Check if course belongs to user
$sql = "SELECT * FROM courses WHERE course_id = ? AND user_id = ?";
$course = Database::fetchOne($sql, [$courseId, $userId]);

if (!$course) {
    // Course not found or doesn't belong to user
    setFlashMessage('הקורס המבוקש לא נמצא.', 'error');
    redirect('/instructor/courses');
}

// Check if course has enrollments
$sql = "SELECT COUNT(*) as count FROM enrollments WHERE course_id = ?";
$enrollmentCount = Database::fetchOne($sql, [$courseId])['count'] ?? 0;

if ($enrollmentCount > 0) {
    // Course has enrollments, prevent deletion
    setFlashMessage('לא ניתן למחוק קורס שיש לו תלמידים רשומים. שנה את סטטוס הקורס לארכיון במקום.', 'error');
    redirect('/instructor/courses');
}

// Start transaction
Database::beginTransaction();

try {
    // Delete lessons and their files
    $sql = "SELECT l.lesson_id, l.content_type, l.content_url 
            FROM lessons l 
            JOIN chapters c ON l.chapter_id = c.chapter_id 
            WHERE c.course_id = ?";
    $lessons = Database::fetchAll($sql, [$courseId]);
    
    foreach ($lessons as $lesson) {
        // Delete lesson file if exists
        if ($lesson['content_type'] == 'video' || $lesson['content_type'] == 'pdf') {
            if (!empty($lesson['content_url'])) {
                deleteFile('assets/uploads/lessons/' . $lesson['content_url']);
            }
        }
        
        // Delete lesson progress
        $sql = "DELETE FROM lesson_progress WHERE lesson_id = ?";
        Database::update($sql, [$lesson['lesson_id']]);
        
        // Delete lesson
        $sql = "DELETE FROM lessons WHERE lesson_id = ?";
        Database::update($sql, [$lesson['lesson_id']]);
    }
    
    // Delete chapters
    $sql = "DELETE FROM chapters WHERE course_id = ?";
    Database::update($sql, [$courseId]);
    
    // Delete course cover image
    if (!empty($course['cover_image'])) {
        deleteFile('assets/uploads/courses/' . $course['cover_image']);
    }
    
    // Delete course
    $sql = "DELETE FROM courses WHERE course_id = ? AND user_id = ?";
    Database::update($sql, [$courseId, $userId]);
    
    // Commit transaction
    Database::commit();
    
    // Set success flash message
    setFlashMessage('הקורס נמחק בהצלחה.', 'success');
} catch (Exception $e) {
    // Rollback transaction
    Database::rollback();
    
    // Set error flash message
    setFlashMessage('אירעה שגיאה במחיקת הקורס. נסה שוב.', 'error');
    
    if (DEBUG_MODE) {
        error_log('Error deleting course: ' . $e->getMessage());
    }
}

// Redirect to courses page
redirect('/instructor/courses');