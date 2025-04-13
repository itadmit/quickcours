<?php
/**
 * Lesson Handler
 * Handles all operations related to course lessons (create, edit, delete)
 */

// Make sure this file is included and not accessed directly
if (!defined('COURSE_ID')) {
    define('COURSE_ID', $courseId);
}

// Handle lesson creation/editing
if (isset($_POST['action']) && $_POST['action'] == 'save_lesson') {
    $lessonId = isset($_POST['lesson_id']) ? (int)$_POST['lesson_id'] : 0;
    $chapterId = (int)$_POST['chapter_id'];
    $lessonTitle = sanitize($_POST['lesson_title']);
    $lessonDescription = sanitize($_POST['lesson_description']);
    $contentType = sanitize($_POST['content_type']);
    $contentText = isset($_POST['content_text']) ? $_POST['content_text'] : '';
    $duration = isset($_POST['duration']) ? (int)$_POST['duration'] : 0;
    
    // Validate chapter belongs to course
    $sql = "SELECT chapter_id FROM chapters WHERE chapter_id = ? AND course_id = ?";
    $chapterCheck = Database::fetchOne($sql, [$chapterId, COURSE_ID]);
    
    if (!$chapterCheck) {
        $message = 'הפרק שנבחר אינו שייך לקורס זה.';
        $messageType = 'error';
    } else if (empty($lessonTitle)) {
        $message = 'שם השיעור הוא שדה חובה.';
        $messageType = 'error';
    } else {
        // Handle file upload if needed
        $contentUrl = '';
        $uploadError = false;
        
        if ($contentType == 'video' || $contentType == 'pdf') {
            if ($lessonId > 0) {
                // Get existing content URL if editing
                $sql = "SELECT content_url FROM lessons WHERE lesson_id = ?";
                $existingLesson = Database::fetchOne($sql, [$lessonId]);
                $contentUrl = $existingLesson ? $existingLesson['content_url'] : '';
            }
            
            // Check if file was uploaded
            if (isset($_FILES['content_file']) && $_FILES['content_file']['size'] > 0) {
                $fileType = $contentType == 'video' ? 'video' : 'document';
                
                if (isAllowedFileType($_FILES['content_file'], $fileType)) {
                    $uploadedFile = uploadFile($_FILES['content_file'], LESSON_UPLOADS, $fileType);
                    
                    if ($uploadedFile) {
                        // If updating and had a previous file, delete it
                        if ($lessonId > 0 && !empty($contentUrl)) {
                            deleteFile('assets/uploads/lessons/' . $contentUrl);
                        }
                        
                        $contentUrl = $uploadedFile;
                    } else {
                        $message = 'שגיאה בהעלאת הקובץ. נסה שוב.';
                        $messageType = 'error';
                        $uploadError = true;
                    }
                } else {
                    $message = 'פורמט קובץ לא נתמך. נא להעלות קובץ בפורמט מתאים.';
                    $messageType = 'error';
                    $uploadError = true;
                }
            } else if ($lessonId == 0 && ($contentType == 'video' || $contentType == 'pdf')) {
                // New lesson requires a file
                $message = 'נא להעלות קובץ לשיעור.';
                $messageType = 'error';
                $uploadError = true;
            }
        }
        
        if (!$uploadError) {
            if ($lessonId > 0) {
                // Update existing lesson
                $sql = "UPDATE lessons SET 
                        chapter_id = ?, 
                        title = ?, 
                        description = ?, 
                        content_type = ?, 
                        content_url = ?, 
                        content_text = ?,
                        duration = ?
                        WHERE lesson_id = ?";
                
                $params = [
                    $chapterId,
                    $lessonTitle,
                    $lessonDescription,
                    $contentType,
                    $contentUrl,
                    $contentText,
                    $duration,
                    $lessonId
                ];
                
                $result = Database::update($sql, $params);
                
                if ($result) {
                    $message = 'השיעור עודכן בהצלחה.';
                    $messageType = 'success';
                } else {
                    $message = 'אירעה שגיאה בעדכון השיעור. נסה שוב.';
                    $messageType = 'error';
                }
            } else {
                // Get highest order number for this chapter
                $sql = "SELECT MAX(order_num) as max_order FROM lessons WHERE chapter_id = ?";
                $result = Database::fetchOne($sql, [$chapterId]);
                $orderNum = ($result && isset($result['max_order'])) ? $result['max_order'] + 1 : 1;
                
                // Create new lesson
                $sql = "INSERT INTO lessons (
                        chapter_id, 
                        title, 
                        description, 
                        content_type, 
                        content_url, 
                        content_text,
                        duration,
                        order_num
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                
                $params = [
                    $chapterId,
                    $lessonTitle,
                    $lessonDescription,
                    $contentType,
                    $contentUrl,
                    $contentText,
                    $duration,
                    $orderNum
                ];
                
                $result = Database::insert($sql, $params);
                
                if ($result) {
                    $message = 'השיעור נוצר בהצלחה.';
                    $messageType = 'success';
                } else {
                    $message = 'אירעה שגיאה ביצירת השיעור. נסה שוב.';
                    $messageType = 'error';
                }
            }
        }
    }
}

// Handle lesson deletion
if (isset($_POST['action']) && $_POST['action'] == 'delete_lesson') {
    $lessonId = (int)$_POST['lesson_id'];
    
    // Verify lesson belongs to this course
    $sql = "SELECT l.lesson_id, l.content_type, l.content_url 
            FROM lessons l 
            JOIN chapters c ON l.chapter_id = c.chapter_id 
            WHERE l.lesson_id = ? AND c.course_id = ?";
    $lessonCheck = Database::fetchOne($sql, [$lessonId, COURSE_ID]);
    
    if (!$lessonCheck) {
        $message = 'השיעור שנבחר אינו שייך לקורס זה.';
        $messageType = 'error';
    } else {
        // Start transaction
        Database::beginTransaction();
        
        try {
            // Delete lesson file if needed
            if (($lessonCheck['content_type'] == 'video' || $lessonCheck['content_type'] == 'pdf') && !empty($lessonCheck['content_url'])) {
                deleteFile('assets/uploads/lessons/' . $lessonCheck['content_url']);
            }
            
            // Delete lesson progress
            $sql = "DELETE FROM lesson_progress WHERE lesson_id = ?";
            Database::update($sql, [$lessonId]);
            
            // Delete lesson
            $sql = "DELETE FROM lessons WHERE lesson_id = ?";
            Database::update($sql, [$lessonId]);
            
            // Commit transaction
            Database::commit();
            
            $message = 'השיעור נמחק בהצלחה.';
            $messageType = 'success';
        } catch (Exception $e) {
            // Rollback on error
            Database::rollback();
            
            $message = 'אירעה שגיאה במחיקת השיעור. נסה שוב.';
            $messageType = 'error';
            
            if (DEBUG_MODE) {
                error_log('Error deleting lesson: ' . $e->getMessage());
            }
        }
    }
}