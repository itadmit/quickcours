<?php
/**
 * Chapter Handler
 * Handles all operations related to course chapters (create, edit, delete)
 */

// Make sure this file is included and not accessed directly
if (!defined('COURSE_ID')) {
    define('COURSE_ID', $courseId);
}

// Handle chapter creation/editing
if (isset($_POST['action']) && $_POST['action'] == 'save_chapter') {
    $chapterId = isset($_POST['chapter_id']) ? (int)$_POST['chapter_id'] : 0;
    $chapterTitle = sanitize($_POST['chapter_title']);
    $chapterDescription = sanitize($_POST['chapter_description']);
    
    if (empty($chapterTitle)) {
        $message = 'שם הפרק הוא שדה חובה.';
        $messageType = 'error';
    } else {
        if ($chapterId > 0) {
            // Update existing chapter
            $sql = "UPDATE chapters SET title = ?, description = ? WHERE chapter_id = ? AND course_id = ?";
            $result = Database::update($sql, [$chapterTitle, $chapterDescription, $chapterId, COURSE_ID]);
            
            if ($result) {
                $message = 'הפרק עודכן בהצלחה.';
                $messageType = 'success';
            } else {
                $message = 'אירעה שגיאה בעדכון הפרק. נסה שוב.';
                $messageType = 'error';
            }
        } else {
            // Get highest order number
            $sql = "SELECT MAX(order_num) as max_order FROM chapters WHERE course_id = ?";
            $result = Database::fetchOne($sql, [COURSE_ID]);
            $orderNum = ($result && isset($result['max_order'])) ? (int)$result['max_order'] + 1 : 1;
            
            // Create new chapter with proper order
            $sql = "INSERT INTO chapters (course_id, title, description, order_num) VALUES (?, ?, ?, ?)";
            $result = Database::insert($sql, [COURSE_ID, $chapterTitle, $chapterDescription, $orderNum]);
            
            if ($result) {
                $message = 'הפרק נוצר בהצלחה.';
                $messageType = 'success';
                // Clear any POST data to prevent resubmission
                $_POST = array();
            } else {
                $message = 'אירעה שגיאה ביצירת הפרק. נסה שוב.';
                $messageType = 'error';
            }
        }
    }
}

// Handle chapter deletion
if (isset($_POST['action']) && $_POST['action'] == 'delete_chapter') {
    $chapterId = (int)$_POST['chapter_id'];
    
    // Verify chapter belongs to this course
    $sql = "SELECT chapter_id FROM chapters WHERE chapter_id = ? AND course_id = ?";
    $chapterCheck = Database::fetchOne($sql, [$chapterId, COURSE_ID]);
    
    if (!$chapterCheck) {
        $message = 'הפרק שנבחר אינו שייך לקורס זה.';
        $messageType = 'error';
    } else {
        // Start transaction
        Database::beginTransaction();
        
        try {
            // Delete lessons in this chapter
            $sql = "SELECT lesson_id, content_type, content_url FROM lessons WHERE chapter_id = ?";
            $lessons = Database::fetchAll($sql, [$chapterId]);
            
            foreach ($lessons as $lesson) {
                // Delete lesson files if needed
                if (($lesson['content_type'] == 'video' || $lesson['content_type'] == 'pdf') && !empty($lesson['content_url'])) {
                    deleteFile('assets/uploads/lessons/' . $lesson['content_url']);
                }
                
                // Delete lesson progress
                $sql = "DELETE FROM lesson_progress WHERE lesson_id = ?";
                Database::update($sql, [$lesson['lesson_id']]);
            }
            
            // Delete all lessons in chapter
            $sql = "DELETE FROM lessons WHERE chapter_id = ?";
            Database::update($sql, [$chapterId]);
            
            // Delete chapter
            $sql = "DELETE FROM chapters WHERE chapter_id = ? AND course_id = ?";
            Database::update($sql, [$chapterId, COURSE_ID]);
            
            // Reorder remaining chapters
            $sql = "SELECT chapter_id FROM chapters WHERE course_id = ? ORDER BY order_num ASC";
            $remainingChapters = Database::fetchAll($sql, [COURSE_ID]);
            
            foreach ($remainingChapters as $index => $chapter) {
                $newOrder = $index + 1;
                $sql = "UPDATE chapters SET order_num = ? WHERE chapter_id = ?";
                Database::update($sql, [$newOrder, $chapter['chapter_id']]);
            }
            
            // Commit transaction
            Database::commit();
            
            $message = 'הפרק נמחק בהצלחה.';
            $messageType = 'success';
        } catch (Exception $e) {
            // Rollback on error
            Database::rollback();
            
            $message = 'אירעה שגיאה במחיקת הפרק. נסה שוב.';
            $messageType = 'error';
            
            if (DEBUG_MODE) {
                error_log('Error deleting chapter: ' . $e->getMessage());
            }
        }
    }
}