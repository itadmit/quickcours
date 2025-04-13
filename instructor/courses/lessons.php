<?php
/**
 * QuickCourse - Course Lessons Management
 * Allows instructors to manage chapters and lessons for a course
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

// Process form submissions via handler files
$message = '';
$messageType = '';

// Include handler files
require_once 'handlers/chapter_handler.php';
require_once 'handlers/lesson_handler.php';
require_once 'handlers/reorder_handler.php';

// Get course chapters and lessons
$sql = "SELECT * FROM chapters WHERE course_id = ? ORDER BY order_num ASC";
$chapters = Database::fetchAll($sql, [$courseId]);

// Fetch lessons for each chapter
foreach ($chapters as &$chapter) {
    $sql = "SELECT * FROM lessons WHERE chapter_id = ? ORDER BY order_num ASC";
    $chapter['lessons'] = Database::fetchAll($sql, [$chapter['chapter_id']]);
}

// Set page title and include header
$pageTitle = 'ניהול תוכן לקורס: ' . $course['title'];
require_once '../../includes/header.php';
?>

<div class="flex flex-col md:flex-row">
    <!-- Sidebar -->
    <div class="w-full md:w-64 bg-white md:min-h-screen md:border-l border-gray-200">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">ניהול קורסים</h2>
        </div>
        <nav class="p-4">
            <ul class="space-y-2">
                <li>
                    <a href="<?= SITE_URL ?>/instructor" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md">
                        <i class="ri-dashboard-line ml-2"></i>
                        <span>דשבורד</span>
                    </a>
                </li>
                <li>
                    <a href="<?= SITE_URL ?>/instructor/courses" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md">
                        <i class="ri-book-open-line ml-2"></i>
                        <span>הקורסים שלי</span>
                    </a>
                </li>
                <li>
                    <a href="<?= SITE_URL ?>/instructor/courses/create.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md">
                        <i class="ri-add-circle-line ml-2"></i>
                        <span>צור קורס חדש</span>
                    </a>
                </li>
                <li>
                    <a href="<?= SITE_URL ?>/instructor/students" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md">
                        <i class="ri-user-star-line ml-2"></i>
                        <span>תלמידים</span>
                    </a>
                </li>
                <li>
                    <a href="<?= SITE_URL ?>/instructor/sales" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md">
                        <i class="ri-line-chart-line ml-2"></i>
                        <span>מכירות</span>
                    </a>
                </li>
                <li>
                    <a href="<?= SITE_URL ?>/instructor/sales/coupons.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md">
                        <i class="ri-coupon-line ml-2"></i>
                        <span>קופונים</span>
                    </a>
                </li>
                
                <?php if (hasPackagePermission($userId, 'affiliates')): ?>
                <li>
                    <a href="<?= SITE_URL ?>/instructor/affiliates" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md">
                        <i class="ri-share-line ml-2"></i>
                        <span>תוכנית שותפים</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="border-t border-gray-200 pt-2 mt-2">
                    <a href="<?= SITE_URL ?>/instructor/settings/profile.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md">
                        <i class="ri-user-settings-line ml-2"></i>
                        <span>הגדרות פרופיל</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="flex-1 p-6 bg-gray-50">
        <!-- Page Heading -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-1">ניהול תוכן הקורס</h1>
                <p class="text-sm text-gray-600">
                    קורס: <a href="<?= SITE_URL ?>/instructor/courses/create.php?id=<?= $courseId ?>" class="text-blue-600 hover:text-blue-800"><?= $course['title'] ?></a>
                </p>
            </div>
            
            <div class="mt-4 md:mt-0">
                <a href="<?= SITE_URL ?>/instructor/courses/create.php?id=<?= $courseId ?>" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="ri-edit-line ml-1"></i> ערוך פרטי קורס
                </a>
                <?php if ($course['status'] == 'published'): ?>
                <a href="<?= SITE_URL ?>/courses/<?= $course['slug'] ?>" target="_blank" class="mr-2 px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="ri-eye-line ml-1"></i> צפה בקורס
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Status Message -->
        <?php if (!empty($message)): ?>
            <div class="bg-<?= $messageType == 'success' ? 'green' : 'red' ?>-100 border-r-4 border-<?= $messageType == 'success' ? 'green' : 'red' ?>-500 text-<?= $messageType == 'success' ? 'green' : 'red' ?>-700 p-4 mb-6 rounded">
                <p><?= $message ?></p>
            </div>
        <?php endif; ?>
        
        <!-- Course Content Section -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <!-- Content Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">תוכן הקורס</h2>
                
                <button type="button" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" id="add-chapter-btn">
                    <i class="ri-add-line ml-1"></i> הוסף פרק
                </button>
            </div>
            
            <!-- Course Structure - Empty State -->
            <?php if (empty($chapters)): ?>
                <div class="p-12 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 text-blue-600 mb-4">
                        <i class="ri-file-list-3-line text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-medium text-gray-900 mb-2">אין תוכן עדיין</h3>
                    <p class="text-gray-600 mb-6 max-w-md mx-auto">
                        התחל לבנות את הקורס שלך על ידי יצירת פרקים ושיעורים. 
                        ארגן את התוכן בפרקים לוגיים כדי לשפר את חווית הלמידה.
                    </p>
                    <button type="button" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" id="empty-add-chapter-btn">
                        <i class="ri-add-line ml-1"></i> הוסף פרק ראשון
                    </button>
                </div>
            <?php else: ?>
                <!-- Course Structure - With Content -->
                <div id="sortable-chapters" class="divide-y divide-gray-200">
                    <?php foreach ($chapters as $chapter): ?>
                        <div class="chapter-item" data-id="<?= $chapter['chapter_id'] ?>">
                            <div class="p-4 bg-gray-50 flex items-center">
                                <div class="mr-2 cursor-move chapter-handle">
                                    <i class="ri-drag-move-2-fill text-gray-400"></i>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-medium text-gray-900"><?= $chapter['title'] ?></h3>
                                    <?php if (!empty($chapter['description'])): ?>
                                        <p class="text-sm text-gray-600"><?= $chapter['description'] ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="flex space-x-2 space-x-reverse">
                                    <button type="button" class="p-2 text-gray-500 hover:text-blue-600 add-lesson-btn" data-chapter-id="<?= $chapter['chapter_id'] ?>">
                                        <i class="ri-add-circle-line"></i>
                                    </button>
                                    <button type="button" class="p-2 text-gray-500 hover:text-blue-600 edit-chapter-btn" data-chapter-id="<?= $chapter['chapter_id'] ?>" data-title="<?= htmlspecialchars($chapter['title']) ?>" data-description="<?= htmlspecialchars($chapter['description']) ?>">
                                        <i class="ri-edit-line"></i>
                                    </button>
                                    <button type="button" class="p-2 text-gray-500 hover:text-red-600 delete-chapter-btn" data-chapter-id="<?= $chapter['chapter_id'] ?>" data-title="<?= htmlspecialchars($chapter['title']) ?>">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Lessons -->
                            <div class="lessons-container">
                                <?php if (empty($chapter['lessons'])): ?>
                                    <div class="p-4 text-center text-gray-500 lesson-empty-state">
                                        <p>אין שיעורים בפרק זה. <button type="button" class="text-blue-600 hover:text-blue-800 add-first-lesson-btn" data-chapter-id="<?= $chapter['chapter_id'] ?>">הוסף שיעור ראשון</button></p>
                                    </div>
                                <?php else: ?>
                                    <div class="divide-y divide-gray-100 sortable-lessons" data-chapter-id="<?= $chapter['chapter_id'] ?>">
                                        <?php foreach ($chapter['lessons'] as $lesson): ?>
                                            <div class="lesson-item p-3 pl-4 pr-10 relative hover:bg-gray-50" data-id="<?= $lesson['lesson_id'] ?>">
                                                <div class="flex items-center">
                                                    <div class="mr-3 cursor-move lesson-handle">
                                                        <i class="ri-drag-move-2-fill text-gray-400"></i>
                                                    </div>
                                                    <div class="mr-3 text-center">
                                                        <?php if ($lesson['content_type'] == 'video'): ?>
                                                            <i class="ri-video-line text-red-500"></i>
                                                        <?php elseif ($lesson['content_type'] == 'pdf'): ?>
                                                            <i class="ri-file-pdf-line text-orange-500"></i>
                                                        <?php else: ?>
                                                            <i class="ri-text text-blue-500"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="flex-1">
                                                        <h4 class="text-sm font-medium text-gray-900"><?= $lesson['title'] ?></h4>
                                                        <?php if (!empty($lesson['description'])): ?>
                                                            <p class="text-xs text-gray-600"><?= $lesson['description'] ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="text-gray-500 text-xs">
                                                        <?php if ($lesson['content_type'] == 'video' && $lesson['duration'] > 0): ?>
                                                            <?php 
                                                            $minutes = floor($lesson['duration'] / 60);
                                                            $seconds = $lesson['duration'] % 60;
                                                            echo sprintf('%d:%02d', $minutes, $seconds);
                                                            ?>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="flex space-x-2 space-x-reverse ml-2">
                                                        <button type="button" class="p-1 text-gray-500 hover:text-blue-600 edit-lesson-btn" 
                                                                data-id="<?= $lesson['lesson_id'] ?>"
                                                                data-chapter-id="<?= $chapter['chapter_id'] ?>"
                                                                data-title="<?= htmlspecialchars($lesson['title']) ?>"
                                                                data-description="<?= htmlspecialchars($lesson['description']) ?>"
                                                                data-content-type="<?= $lesson['content_type'] ?>"
                                                                data-content-text="<?= htmlspecialchars($lesson['content_text']) ?>"
                                                                data-duration="<?= $lesson['duration'] ?>">
                                                            <i class="ri-edit-line"></i>
                                                        </button>
                                                        <button type="button" class="p-1 text-gray-500 hover:text-red-600 delete-lesson-btn" 
                                                                data-id="<?= $lesson['lesson_id'] ?>"
                                                                data-title="<?= htmlspecialchars($lesson['title']) ?>">
                                                            <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="p-2 border-t border-gray-100 text-center">
                                        <button type="button" class="px-3 py-1 text-sm text-blue-600 hover:text-blue-800 add-lesson-btn" data-chapter-id="<?= $chapter['chapter_id'] ?>">
                                            <i class="ri-add-line"></i> הוסף שיעור
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Chapter Tips -->
        <div class="mt-6 bg-blue-50 rounded-lg p-4 border border-blue-100">
            <h3 class="text-blue-800 font-medium mb-2">טיפים לבניית קורס אפקטיבי:</h3>
            <ul class="text-blue-700 text-sm space-y-1 mr-5 list-disc">
                <li>סדר את התוכן באופן לוגי המאפשר למידה הדרגתית</li>
                <li>חלק את הקורס לפרקים ממוקדי נושא</li>
                <li>שמור על אורך שיעור סביר (5-15 דקות לשיעור וידאו)</li>
                <li>תן שמות ברורים לפרקים ושיעורים</li>
                <li>הוסף תיאורים מפורטים כדי שהתלמידים יבינו מה מצפה להם</li>
            </ul>
        </div>
    </div>
</div>

<?php 
// Include modals
require_once 'handlers/modals.php';
?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<script src="<?= SITE_URL ?>/instructor/assets/js/lesson_manager.js"></script>
<?php
// Include footer with JavaScript
require_once '../../includes/footer.php';
?>

