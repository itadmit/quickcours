<?php
/**
 * QuickCourse - Course Publication Management
 * Allows instructors to change the status of their courses
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
    setFlashMessage('הקורס המבוקש לא נמצא או שאינך מורשה לערוך אותו.', 'error');
    redirect('/instructor/courses');
}

// Check if the course has at least one chapter with at least one lesson
$sql = "
    SELECT COUNT(*) as lesson_count
    FROM chapters c
    JOIN lessons l ON c.chapter_id = l.chapter_id
    WHERE c.course_id = ?
";
$result = Database::fetchOne($sql, [$courseId]);
$lessonCount = $result['lesson_count'] ?? 0;

if ($lessonCount == 0) {
    // Course has no content
    setFlashMessage('לא ניתן לפרסם קורס ללא תוכן. אנא הוסף לפחות פרק אחד ושיעור אחד לקורס.', 'error');
    redirect('/instructor/courses/preview.php?id=' . $courseId);
}

// Determine new status based on current status
$newStatus = 'published';
if ($course['status'] == 'published') {
    $newStatus = 'archived';
} else if ($course['status'] == 'archived') {
    $newStatus = 'published';
}

// Update course status
$sql = "UPDATE courses SET status = ?, updated_at = NOW() WHERE course_id = ? AND user_id = ?";
$result = Database::update($sql, [$newStatus, $courseId, $userId]);

if ($result) {
    // Success message based on new status
    if ($newStatus == 'published') {
        setFlashMessage('הקורס פורסם בהצלחה! הוא עכשיו זמין לתלמידים.', 'success');
    } else if ($newStatus == 'archived') {
        setFlashMessage('הקורס הועבר לארכיון בהצלחה. תלמידים קיימים עדיין יכולים לגשת אליו, אך הוא לא זמין לתלמידים חדשים.', 'success');
    }
} else {
    // Error message
    setFlashMessage('אירעה שגיאה בעדכון סטטוס הקורס. נסה שוב.', 'error');
}

// Redirect back to course preview
redirect('/instructor/courses/preview.php?id=' . $courseId);
?>