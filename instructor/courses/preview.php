<?php
/**
 * QuickCourse - Instructor Course Preview
 * Allows instructors to preview their courses as students would see them
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

// Get course ID from URL
$courseId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($courseId <= 0) {
    // Redirect to courses page if no ID
    setFlashMessage('בחר קורס לצפייה.', 'error');
    redirect('/instructor/courses');
}

// Check if course belongs to user
$sql = "SELECT * FROM courses WHERE course_id = ? AND user_id = ?";
$course = Database::fetchOne($sql, [$courseId, $userId]);

if (!$course) {
    // Course not found or doesn't belong to user
    setFlashMessage('הקורס המבוקש לא נמצא או שאינך מורשה לצפות בו.', 'error');
    redirect('/instructor/courses');
}

// Get chapters and lessons
$sql = "SELECT * FROM chapters WHERE course_id = ? ORDER BY order_num ASC";
$chapters = Database::fetchAll($sql, [$courseId]);

// Get current lesson ID from URL
$currentLessonId = isset($_GET['lesson_id']) ? (int)$_GET['lesson_id'] : 0;
$currentLesson = null;
$currentChapterId = 0;

// If no lesson ID specified, get the first lesson
if ($currentLessonId <= 0 && !empty($chapters)) {
    $firstChapter = $chapters[0];
    $sql = "SELECT * FROM lessons WHERE chapter_id = ? ORDER BY order_num ASC LIMIT 1";
    $firstLesson = Database::fetchOne($sql, [$firstChapter['chapter_id']]);
    
    if ($firstLesson) {
        $currentLessonId = $firstLesson['lesson_id'];
    }
}

// Get all lessons for each chapter and find current lesson
foreach ($chapters as &$chapter) {
    $sql = "SELECT l.* FROM lessons l WHERE l.chapter_id = ? ORDER BY l.order_num ASC";
    $chapter['lessons'] = Database::fetchAll($sql, [$chapter['chapter_id']]);
    
    // Check if current lesson is in this chapter
    foreach ($chapter['lessons'] as $lesson) {
        if ($lesson['lesson_id'] == $currentLessonId) {
            $currentLesson = $lesson;
            $currentChapterId = $chapter['chapter_id'];
            break;
        }
    }
}

// Calculate total lessons count
$totalLessons = 0;
foreach ($chapters as $chapter) {
    $totalLessons += count($chapter['lessons']);
}

// Set page title
$pageTitle = 'צפייה בקורס: ' . $course['title'];
require_once '../../includes/header.php';
?>

<div class="flex flex-col lg:flex-row">
    <!-- Sidebar with Course Chapters and Lessons -->
    <div class="lg:w-80 bg-white lg:min-h-screen border-b lg:border-l lg:border-b-0 border-gray-200 overflow-y-auto">
        <div class="p-4 border-b border-gray-200 bg-blue-50">
            <div class="flex items-center justify-between">
                <a href="<?= SITE_URL ?>/instructor/courses" class="flex items-center text-gray-700 hover:text-blue-600">
                    <i class="ri-arrow-right-s-line ml-1"></i>
                    <span>חזרה לקורסים שלי</span>
                </a>
                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">מצב תצוגה</span>
            </div>
            <div class="mt-2 text-sm text-gray-600">
                צופה בקורס כפי שהתלמידים יראו אותו
            </div>
        </div>
        
        <!-- Course Info -->
        <div class="p-4 border-b border-gray-200">
            <h3 class="font-bold text-lg text-gray-900 mb-1"><?= htmlspecialchars($course['title']) ?></h3>
            <p class="text-sm text-gray-600">
                <?= htmlspecialchars($course['short_description']) ?>
            </p>
        </div>
        
        <!-- Chapters and Lessons -->
        <div class="overflow-y-auto" style="max-height: calc(100vh - 200px);">
            <?php foreach ($chapters as $chapterIndex => $chapter): ?>
                <div class="border-b border-gray-200">
                    <div class="p-3 bg-gray-50">
                        <h4 class="font-semibold text-gray-800 flex items-center justify-between">
                            <span>פרק <?= $chapterIndex + 1 ?>: <?= htmlspecialchars($chapter['title']) ?></span>
                            <a href="<?= SITE_URL ?>/instructor/courses/create.php?id=<?= $courseId ?>" class="text-xs text-blue-600 hover:text-blue-800">
                                <i class="ri-edit-line"></i>
                            </a>
                        </h4>
                    </div>
                    
                    <ul class="divide-y divide-gray-100">
                        <?php foreach ($chapter['lessons'] as $lessonIndex => $lesson): ?>
                            <li>
                                <a href="<?= SITE_URL ?>/instructor/courses/preview.php?id=<?= $courseId ?>&lesson_id=<?= $lesson['lesson_id'] ?>" 
                                   class="block p-3 hover:bg-gray-50 transition-colors <?= $lesson['lesson_id'] == $currentLessonId ? 'bg-blue-50 border-r-4 border-blue-500' : '' ?>">
                                    <div class="flex items-center">
                                        <div class="ml-3 flex-shrink-0">
                                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-gray-500">
                                                <?= $chapterIndex + 1 ?>.<?= $lessonIndex + 1 ?>
                                            </span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                <?= htmlspecialchars($lesson['title']) ?>
                                            </p>
                                            <div class="flex items-center text-xs text-gray-500">
                                                <?php if ($lesson['content_type'] == 'video'): ?>
                                                    <i class="ri-video-line ml-1"></i>
                                                    <?php if ($lesson['duration'] > 0): ?>
                                                        <span>
                                                            <?php 
                                                            $minutes = floor($lesson['duration'] / 60);
                                                            $seconds = $lesson['duration'] % 60;
                                                            echo sprintf('%d:%02d', $minutes, $seconds);
                                                            ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span>וידאו</span>
                                                    <?php endif; ?>
                                                <?php elseif ($lesson['content_type'] == 'pdf'): ?>
                                                    <i class="ri-file-pdf-line ml-1"></i>
                                                    <span>PDF</span>
                                                <?php else: ?>
                                                    <i class="ri-file-text-line ml-1"></i>
                                                    <span>טקסט</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <li>
                            <a href="<?= SITE_URL ?>/instructor/courses/lessons.php?id=<?= $courseId ?>&chapter_id=<?= $chapter['chapter_id'] ?>" class="block p-3 bg-gray-50 text-center text-sm text-blue-600 hover:text-blue-800">
                                <i class="ri-add-line ml-1"></i> הוסף שיעור לפרק זה
                            </a>
                        </li>
                    </ul>
                </div>
            <?php endforeach; ?>
            <div class="p-4 text-center">
                <a href="<?= SITE_URL ?>/instructor/courses/lessons.php?id=<?= $courseId ?>" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                    <i class="ri-add-line ml-1"></i> הוסף פרק חדש
                </a>
            </div>
        </div>
    </div>
    
    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col">
        <?php if ($currentLesson): ?>
            <!-- Lesson Header -->
            <div class="bg-white p-4 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900"><?= htmlspecialchars($currentLesson['title']) ?></h2>
                </div>
                
                <div class="flex space-x-2 space-x-reverse">
                    <!-- Navigation Controls -->
                    <?php 
                    $prevLessonId = null;
                    $nextLessonId = null;
                    $foundCurrent = false;
                    
                    // Find previous and next lessons
                    foreach ($chapters as $chapter) {
                        foreach ($chapter['lessons'] as $lesson) {
                            if ($foundCurrent) {
                                $nextLessonId = $lesson['lesson_id'];
                                break 2; // Exit both loops
                            }
                            
                            if ($lesson['lesson_id'] == $currentLessonId) {
                                $foundCurrent = true;
                            } else {
                                $prevLessonId = $lesson['lesson_id'];
                            }
                        }
                    }
                    ?>
                    
                    <?php if ($prevLessonId): ?>
                        <a href="<?= SITE_URL ?>/instructor/courses/preview.php?id=<?= $courseId ?>&lesson_id=<?= $prevLessonId ?>" class="px-3 py-1 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 flex items-center">
                            <i class="ri-arrow-right-s-line ml-1"></i>
                            הקודם
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($nextLessonId): ?>
                        <a href="<?= SITE_URL ?>/instructor/courses/preview.php?id=<?= $courseId ?>&lesson_id=<?= $nextLessonId ?>" class="px-3 py-1 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 flex items-center">
                            הבא
                            <i class="ri-arrow-left-s-line mr-1"></i>
                        </a>
                    <?php endif; ?>
                    
                    <!-- Edit Button -->
                    <a href="<?= SITE_URL ?>/instructor/courses/lessons.php?id=<?= $courseId ?>&edit_lesson=<?= $currentLessonId ?>" class="px-3 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700 flex items-center">
                        <i class="ri-edit-line ml-1"></i>
                        ערוך שיעור
                    </a>
                </div>
            </div>
            
            <!-- Lesson Content -->
            <div class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <?php if ($currentLesson['content_type'] == 'video' && !empty($currentLesson['content_url'])): ?>
                    <div class="bg-black rounded-lg overflow-hidden mb-6 aspect-video">
                        <video id="lesson-video" class="w-full h-full" controls>
                            <source src="<?= getAssetUrl('assets/uploads/lessons/' . $currentLesson['content_url']) ?>" type="video/mp4">
                            הדפדפן שלך לא תומך בתגית וידאו.
                        </video>
                    </div>
                    
                    <?php if (!empty($currentLesson['description'])): ?>
                        <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">תיאור השיעור</h3>
                            <div class="text-gray-700">
                                <?= nl2br(htmlspecialchars($currentLesson['description'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                
                <?php elseif ($currentLesson['content_type'] == 'pdf' && !empty($currentLesson['content_url'])): ?>
                    <div class="bg-white rounded-lg overflow-hidden mb-6 shadow-sm">
                        <div class="bg-gray-800 text-white py-2 px-4 flex justify-between items-center">
                            <h3 class="font-medium">מסמך PDF</h3>
                            <a href="<?= getAssetUrl('assets/uploads/lessons/' . $currentLesson['content_url']) ?>" target="_blank" class="text-sm text-blue-300 hover:text-blue-100">
                                פתח בחלון חדש <i class="ri-external-link-line mr-1"></i>
                            </a>
                        </div>
                        <iframe src="<?= getAssetUrl('assets/uploads/lessons/' . $currentLesson['content_url']) ?>" class="w-full" style="height: 700px;"></iframe>
                    </div>
                    
                    <?php if (!empty($currentLesson['description'])): ?>
                        <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">תיאור השיעור</h3>
                            <div class="text-gray-700">
                                <?= nl2br(htmlspecialchars($currentLesson['description'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                
                <?php elseif ($currentLesson['content_type'] == 'text'): ?>
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <?php if (!empty($currentLesson['description'])): ?>
                            <h3 class="text-xl font-semibold text-gray-900 mb-4">תיאור</h3>
                            <div class="text-gray-700 mb-8">
                                <?= nl2br(htmlspecialchars($currentLesson['description'])) ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="prose max-w-none">
                            <?= nl2br(htmlspecialchars($currentLesson['content_text'])) ?>
                        </div>
                    </div>
                
                <?php else: ?>
                    <div class="bg-white p-6 rounded-lg shadow-sm text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 text-blue-400 mb-4">
                            <i class="ri-error-warning-line text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-medium text-gray-900 mb-2">לא נמצא תוכן לשיעור זה</h3>
                        <p class="text-gray-600 mb-6">
                            נראה ששיעור זה טרם מולא בתוכן. עליך להוסיף תוכן לשיעור כדי שהתלמידים יוכלו לצפות בו.
                        </p>
                        <a href="<?= SITE_URL ?>/instructor/courses/lessons.php?id=<?= $courseId ?>&edit_lesson=<?= $currentLessonId ?>" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                            <i class="ri-edit-line ml-1"></i> ערוך שיעור
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
        <?php else: ?>
            <!-- No Lesson Selected -->
            <div class="flex-1 flex items-center justify-center bg-gray-50">
                <div class="text-center p-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 text-blue-600 mb-4">
                        <i class="ri-play-circle-line text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-medium text-gray-900 mb-2">בחר שיעור לצפייה</h3>
                    <p class="text-gray-600 mb-6">
                        בחר שיעור מהרשימה בצד ימין כדי לצפות בתוכן.
                    </p>
                    
                    <?php if (empty($chapters) || $totalLessons == 0): ?>
                        <div class="mt-4">
                            <p class="text-gray-700 mb-4">הקורס עדיין ריק. עליך להוסיף פרקים ושיעורים כדי שהתלמידים יוכלו ללמוד ממנו.</p>
                            <a href="<?= SITE_URL ?>/instructor/courses/lessons.php?id=<?= $courseId ?>" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                                <i class="ri-add-line ml-1"></i> הוסף תוכן לקורס
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Publication Status Bar -->
<?php if ($course['status'] !== 'published'): ?>
<div class="fixed bottom-0 left-0 right-0 bg-yellow-50 border-t border-yellow-200 p-3">
    <div class="container mx-auto flex flex-col md:flex-row items-center justify-between">
        <div class="flex items-center">
            <div class="bg-yellow-100 p-2 rounded-full mr-3">
                <i class="ri-error-warning-line text-yellow-600"></i>
            </div>
            <div>
                <p class="font-medium text-yellow-800">
                    <?php if ($course['status'] == 'draft'): ?>
                        קורס זה הוא טיוטה ואינו זמין לתלמידים.
                    <?php elseif ($course['status'] == 'archived'): ?>
                        קורס זה בארכיון ואינו זמין לתלמידים חדשים.
                    <?php endif; ?>
                </p>
            </div>
        </div>
        
        <div class="mt-3 md:mt-0 flex">
            <a href="<?= SITE_URL ?>/instructor/courses/create.php?id=<?= $courseId ?>" class="px-4 py-2 border border-yellow-600 text-yellow-700 rounded-md hover:bg-yellow-100 mr-2">
                עריכת פרטי קורס
            </a>
            <?php if ($course['status'] == 'draft'): ?>
                <form method="post" action="<?= SITE_URL ?>/instructor/courses/publish.php">
                    <input type="hidden" name="course_id" value="<?= $courseId ?>">
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        פרסם קורס
                    </button>
                </form>
            <?php elseif ($course['status'] == 'archived'): ?>
                <form method="post" action="<?= SITE_URL ?>/instructor/courses/publish.php">
                    <input type="hidden" name="course_id" value="<?= $courseId ?>">
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        שחזר קורס
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once '../../includes/footer.php'; ?>