<?php
/**
 * Reorder Handler
 * Handles reordering operations for chapters and lessons
 */

// Make sure this file is included and not accessed directly
if (!defined('COURSE_ID')) {
    define('COURSE_ID', $courseId);
}

// Handle chapter reordering
if (isset($_POST['action']) && $_POST['action'] == 'reorder_chapters') {
    $chapterOrder = json_decode($_POST['chapter_order'], true);
    
    if (is_array($chapterOrder)) {
        $success = true;
        
        foreach ($chapterOrder as $order => $chapterId) {
            $sql = "UPDATE chapters SET order_num = ? WHERE chapter_id = ? AND course_id = ?";
            $result = Database::update($sql, [$order + 1, $chapterId, COURSE_ID]);
            
            if (!$result) {
                $success = false;
            }
        }
        
        if ($success) {
            $message = 'סדר הפרקים עודכן בהצלחה.';
            $messageType = 'success';
        } else {
            $message = 'אירעה שגיאה בעדכון סדר הפרקים. נסה שוב.';
            $messageType = 'error';
        }
    }
    
    // If this is an AJAX request, return JSON response
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => ($messageType == 'success'), 'message' => $message]);
        exit;
    }
}

// Handle lesson reordering
if (isset($_POST['action']) && $_POST['action'] == 'reorder_lessons') {
    $lessonOrder = json_decode($_POST['lesson_order'], true);
    $chapterId = (int)$_POST['chapter_id'];
    
    // Verify chapter belongs to this course
    $sql = "SELECT chapter_id FROM chapters WHERE chapter_id = ? AND course_id = ?";
    $chapterCheck = Database::fetchOne($sql, [$chapterId, COURSE_ID]);
    
    if (!$chapterCheck) {
        $message = 'הפרק שנבחר אינו שייך לקורס זה.';
        $messageType = 'error';
    } else if (is_array($lessonOrder)) {
        $success = true;
        
        foreach ($lessonOrder as $order => $lessonId) {
            $sql = "UPDATE lessons SET order_num = ? WHERE lesson_id = ? AND chapter_id = ?";
            $result = Database::update($sql, [$order + 1, $lessonId, $chapterId]);
            
            if (!$result) {
                $success = false;
            }
        }
        
        if ($success) {
            $message = 'סדר השיעורים עודכן בהצלחה.';
            $messageType = 'success';
        } else {
            $message = 'אירעה שגיאה בעדכון סדר השיעורים. נסה שוב.';
            $messageType = 'error';
        }
    }
    
    // If this is an AJAX request, return JSON response
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => ($messageType == 'success'), 'message' => $message]);
        exit;
    }
}