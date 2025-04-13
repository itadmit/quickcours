<?php
/**
 * QuickCourse - Course View Page
 * Shows details for a specific course and allows purchase
 */

// Include required files
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Start session if not already started
startSession();

// Get slug from URL
$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';

if (empty($slug)) {
    // No slug provided, redirect to courses page
    redirect('/courses');
}

// Get course info
$sql = "SELECT c.*, 
        u.first_name, u.last_name, u.profile_image,
        (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.course_id) as student_count
        FROM courses c 
        JOIN users u ON c.user_id = u.user_id
        WHERE c.slug = ? AND c.status = 'published'";
$course = Database::fetchOne($sql, [$slug]);

if (!$course) {
    // Course not found or not published, redirect to courses page
    setFlashMessage('הקורס המבוקש לא נמצא או שאינו זמין.', 'error');
    redirect('/courses');
}

// Get instructor info
$sql = "SELECT bio FROM instructor_profiles WHERE user_id = ?";
$instructor = Database::fetchOne($sql, [$course['user_id']]);

// Get course chapters and lessons count
$sql = "SELECT ch.*, 
        (SELECT COUNT(*) FROM lessons l WHERE l.chapter_id = ch.chapter_id) as lesson_count,
        (SELECT SUM(duration) FROM lessons l WHERE l.chapter_id = ch.chapter_id) as total_duration
        FROM chapters ch
        WHERE ch.course_id = ?
        ORDER BY ch.order_num";
$chapters = Database::fetchAll($sql, [$course['course_id']]);

// Count total lessons and duration
$totalLessons = 0;
$totalDuration = 0;
foreach ($chapters as $chapter) {
    $totalLessons += $chapter['lesson_count'];
    $totalDuration += $chapter['total_duration'];
}

// Check if user is already enrolled
$isEnrolled = false;
if (isLoggedIn()) {
    $userId = getCurrentUserId();
    $sql = "SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?";
    $enrollment = Database::fetchOne($sql, [$userId, $course['course_id']]);
    $isEnrolled = !empty($enrollment);
}

// Get discount code if provided
$discountCode = isset($_GET['coupon']) ? sanitize($_GET['coupon']) : '';
$discountAmount = 0;
$finalPrice = $course['price'];

if (!empty($discountCode)) {
    // Check if discount code is valid
    $sql = "SELECT * FROM coupons 
            WHERE code = ? AND is_active = 1 
            AND (course_id IS NULL OR course_id = ?) 
            AND (valid_to IS NULL OR valid_to >= CURDATE())
            AND (usage_limit IS NULL OR usage_count < usage_limit)";
    $coupon = Database::fetchOne($sql, [$discountCode, $course['course_id']]);
    
    if ($coupon) {
        if ($coupon['discount_type'] == 'percentage') {
            $discountAmount = ($course['price'] * $coupon['discount_value']) / 100;
        } else {
            $discountAmount = $coupon['discount_value'];
        }
        
        // Calculate final price, don't go below 0
        $finalPrice = max(0, $course['price'] - $discountAmount);
    } else {
        // Invalid coupon code
        $discountCode = '';
    }
}

// Set page title
$pageTitle = $course['title'];
require_once '../includes/header.php';
?>

<!-- Course Header -->
<section class="bg-gradient-to-r from-blue-50 to-indigo-50 py-8 mb-8">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row md:items-start">
            <!-- Course Info -->
            <div class="md:w-2/3 md:ml-8">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4"><?= htmlspecialchars($course['title']) ?></h1>
                
                <p class="text-xl text-gray-600 mb-6"><?= htmlspecialchars($course['short_description']) ?></p>
                
                <div class="flex flex-wrap items-center text-gray-700 mb-4">
                    <span class="flex items-center ml-6 mb-2">
                        <i class="ri-user-star-line text-blue-600 ml-1"></i>
                        <?= htmlspecialchars($course['first_name'] . ' ' . $course['last_name']) ?>
                    </span>
                    
                    <span class="flex items-center ml-6 mb-2">
                        <i class="ri-user-follow-line text-blue-600 ml-1"></i>
                        <?= $course['student_count'] ?> תלמידים רשומים
                    </span>
                    
                    <span class="flex items-center ml-6 mb-2">
                        <i class="ri-time-line text-blue-600 ml-1"></i>
                        <?= formatDuration($totalDuration) ?>
                    </span>
                    
                    <span class="flex items-center mb-2">
                        <i class="ri-file-list-line text-blue-600 ml-1"></i>
                        <?= $totalLessons ?> שיעורים
                    </span>
                </div>
                
                <?php if ($course['price'] == 0): ?>
                    <div class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-md text-sm font-medium mb-6">
                        קורס חינמי
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Course Image (Mobile) -->
            <div class="md:hidden mb-8">
                <?php if (!empty($course['cover_image'])): ?>
                    <img src="<?= getAssetUrl('assets/uploads/courses/' . $course['cover_image']) ?>" alt="<?= htmlspecialchars($course['title']) ?>" class="w-full rounded-lg shadow-md">
                <?php else: ?>
                    <div class="w-full h-48 bg-blue-100 rounded-lg shadow-md flex items-center justify-center">
                        <i class="ri-book-open-line text-6xl text-blue-600"></i>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Course Image (Desktop) -->
            <div class="hidden md:block md:w-1/3">
                <?php if (!empty($course['cover_image'])): ?>
                    <img src="<?= getAssetUrl('assets/uploads/courses/' . $course['cover_image']) ?>" alt="<?= htmlspecialchars($course['title']) ?>" class="w-full rounded-lg shadow-md">
                <?php else: ?>
                    <div class="w-full h-64 bg-blue-100 rounded-lg shadow-md flex items-center justify-center">
                        <i class="ri-book-open-line text-6xl text-blue-600"></i>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<div class="container mx-auto px-4 pb-12">
    <div class="flex flex-col md:flex-row">
        <!-- Main Content -->
        <div class="md:w-2/3 md:ml-8">
            <!-- Course Description -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">אודות הקורס</h2>
                <div class="prose max-w-none">
                    <?= nl2br(htmlspecialchars($course['full_description'])) ?>
                </div>
            </div>
            
            <!-- Course Content -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">תוכן הקורס</h2>
                
                <div class="mb-4 flex items-center text-gray-700">
                    <span class="flex items-center ml-4">
                        <i class="ri-book-open-line text-blue-600 ml-1"></i>
                        <?= count($chapters) ?> פרקים
                    </span>
                    <span class="flex items-center ml-4">
                        <i class="ri-file-list-line text-blue-600 ml-1"></i>
                        <?= $totalLessons ?> שיעורים
                    </span>
                    <span class="flex items-center">
                        <i class="ri-time-line text-blue-600 ml-1"></i>
                        סה"כ <?= formatDuration($totalDuration) ?>
                    </span>
                </div>
                
                <!-- Chapters Accordion -->
                <div class="space-y-4">
                    <?php foreach ($chapters as $index => $chapter): ?>
                        <div class="border border-gray-200 rounded-md overflow-hidden chapter-accordion">
                            <div class="bg-gray-50 px-4 py-3 flex justify-between items-center cursor-pointer chapter-header">
                                <h3 class="text-lg font-medium text-gray-800">
                                    <?= ($index + 1) . '. ' . htmlspecialchars($chapter['title']) ?>
                                </h3>
                                <div class="flex items-center">
                                    <span class="text-sm text-gray-600 ml-2">
                                        <?= $chapter['lesson_count'] ?> שיעורים
                                    </span>
                                    <i class="ri-arrow-down-s-line text-gray-500 chapter-icon"></i>
                                </div>
                            </div>
                            
                            <div class="p-4 border-t border-gray-200 hidden chapter-content">
                                <?php
                                // Get lessons for this chapter
                                $sql = "SELECT * FROM lessons WHERE chapter_id = ? ORDER BY order_num";
                                $lessons = Database::fetchAll($sql, [$chapter['chapter_id']]);
                                ?>
                                
                                <ul class="space-y-3">
                                    <?php foreach ($lessons as $lessonIndex => $lesson): ?>
                                        <li class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <?php if ($lesson['content_type'] == 'video'): ?>
                                                    <i class="ri-video-line text-blue-600 ml-2"></i>
                                                <?php elseif ($lesson['content_type'] == 'pdf'): ?>
                                                    <i class="ri-file-pdf-line text-red-600 ml-2"></i>
                                                <?php else: ?>
                                                    <i class="ri-text text-green-600 ml-2"></i>
                                                <?php endif; ?>
                                                <span class="text-gray-800">
                                                    <?= ($index + 1) . '.' . ($lessonIndex + 1) . ' ' . htmlspecialchars($lesson['title']) ?>
                                                </span>
                                            </div>
                                            <?php if ($lesson['duration']): ?>
                                                <span class="text-sm text-gray-600"><?= formatDuration($lesson['duration']) ?></span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Instructor Info -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">המרצה</h2>
                
                <div class="flex items-start">
                    <div class="flex-shrink-0 ml-4">
                        <?php if (!empty($course['profile_image'])): ?>
                            <img src="<?= getAssetUrl('assets/uploads/profiles/' . $course['profile_image']) ?>" alt="<?= htmlspecialchars($course['first_name'] . ' ' . $course['last_name']) ?>" class="w-16 h-16 rounded-full object-cover">
                        <?php else: ?>
                            <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center">
                                <span class="text-2xl text-blue-600 font-medium">
                                    <?= substr($course['first_name'], 0, 1) . substr($course['last_name'], 0, 1) ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-medium text-gray-800 mb-2">
                            <?= htmlspecialchars($course['first_name'] . ' ' . $course['last_name']) ?>
                        </h3>
                        
                        <?php if (!empty($instructor['bio'])): ?>
                            <p class="text-gray-700">
                                <?= nl2br(htmlspecialchars($instructor['bio'])) ?>
                            </p>
                        <?php else: ?>
                            <p class="text-gray-700">
                                מדריך באתר קוויק קורס.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar - Course Purchase Card -->
        <div class="md:w-1/3 mt-8 md:mt-0">
            <div class="bg-white rounded-lg shadow-md p-6 sticky top-8">
                <?php if ($isEnrolled): ?>
                    <!-- Already Enrolled -->
                    <div class="text-center mb-6">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 text-green-500 mb-4">
                            <i class="ri-check-line text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">אתה כבר רשום לקורס זה</h3>
                    </div>
                    
                    <a href="<?= SITE_URL ?>/student/courses/view.php?id=<?= $course['course_id'] ?>" class="block w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md text-center transition">
                        המשך ללמוד
                    </a>
                    
                    <a href="<?= SITE_URL ?>/student/courses" class="block w-full mt-4 py-3 px-4 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium rounded-md text-center transition">
                        לכל הקורסים שלי
                    </a>
                <?php else: ?>
                    <!-- Course Price -->
                    <div class="text-center mb-6">
                        <div class="text-3xl font-bold text-gray-800 mb-2">
                            <?php if ($course['price'] > 0): ?>
                                <?php if (!empty($discountCode) && $discountAmount > 0): ?>
                                    <span class="line-through text-gray-500 text-xl ml-2"><?= formatPrice($course['price']) ?></span>
                                    <?= formatPrice($finalPrice) ?>
                                <?php else: ?>
                                    <?= formatPrice($course['price']) ?>
                                <?php endif; ?>
                            <?php else: ?>
                                חינם
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($course['price'] > 0): ?>
                            <p class="text-sm text-gray-600 mb-4">
                                מחיר חד פעמי, גישה לצמיתות
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- What You'll Get -->
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold text-gray-800 mb-3">מה תקבל:</h4>
                        <ul class="space-y-2">
                            <li class="flex items-center">
                                <i class="ri-check-line text-green-500 ml-2"></i>
                                <span class="text-gray-700">גישה מלאה ל-<?= $totalLessons ?> שיעורים</span>
                            </li>
                            <li class="flex items-center">
                                <i class="ri-check-line text-green-500 ml-2"></i>
                                <span class="text-gray-700"><?= formatDuration($totalDuration) ?> של תוכן ברמה גבוהה</span>
                            </li>
                            <li class="flex items-center">
                                <i class="ri-check-line text-green-500 ml-2"></i>
                                <span class="text-gray-700">גישה מכל מכשיר, בכל זמן</span>
                            </li>
                            <li class="flex items-center">
                                <i class="ri-check-line text-green-500 ml-2"></i>
                                <span class="text-gray-700">תעודת סיום</span>
                            </li>
                            <li class="flex items-center">
                                <i class="ri-check-line text-green-500 ml-2"></i>
                                <span class="text-gray-700">תמיכה ישירה מהמרצה</span>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Coupon Code -->
                    <?php if ($course['price'] > 0): ?>
                        <div class="mb-6">
                            <label for="coupon_code" class="block text-sm font-medium text-gray-700 mb-1">יש לך קוד קופון?</label>
                            <form action="<?= SITE_URL ?>/courses/<?= $slug ?>" method="get" class="flex">
                                <input type="text" id="coupon_code" name="coupon" value="<?= htmlspecialchars($discountCode) ?>" class="block flex-grow border-gray-300 rounded-r-md focus:ring-blue-500 focus:border-blue-500" placeholder="הזן קוד קופון">
                                <button type="submit" class="px-4 py-2 bg-gray-200 text-gray-700 font-medium rounded-l-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    החל
                                </button>
                            </form>
                            <?php if (!empty($discountCode)): ?>
                                <?php if ($discountAmount > 0): ?>
                                    <p class="text-sm text-green-600 mt-1">
                                        <i class="ri-check-line"></i> קוד הקופון הוחל בהצלחה! חסכת <?= formatPrice($discountAmount) ?>
                                    </p>
                                <?php else: ?>
                                    <p class="text-sm text-red-600 mt-1">
                                        <i class="ri-close-line"></i> קוד הקופון שהזנת אינו תקף או אינו חל על קורס זה.
                                    </p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Call to Action -->
                    <?php if (isLoggedIn()): ?>
                        <a href="<?= SITE_URL ?>/checkout/index.php?course_id=<?= $course['course_id'] ?><?= !empty($discountCode) ? '&coupon=' . urlencode($discountCode) : '' ?>" class="block w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md text-center transition">
                            <?= $course['price'] > 0 ? 'רכוש עכשיו' : 'הירשם לקורס בחינם' ?>
                        </a>
                    <?php else: ?>
                        <a href="<?= SITE_URL ?>/auth/login.php?redirect=<?= urlencode('/courses/' . $slug . (!empty($discountCode) ? '?coupon=' . $discountCode : '')) ?>" class="block w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md text-center transition">
                            התחבר כדי להירשם
                        </a>
                        <a href="<?= SITE_URL ?>/auth/register.php?redirect=<?= urlencode('/courses/' . $slug . (!empty($discountCode) ? '?coupon=' . $discountCode : '')) ?>" class="block w-full mt-4 py-3 px-4 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium rounded-md text-center transition">
                            צור חשבון חדש
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Guarantee -->
                <?php if ($course['price'] > 0): ?>
                    <div class="mt-6 pt-6 border-t border-gray-200 text-center">
                        <div class="flex items-center justify-center mb-2">
                            <i class="ri-shield-check-line text-green-500 text-xl ml-2"></i>
                            <span class="text-gray-800 font-medium">30 יום אחריות החזר כספי</span>
                        </div>
                        <p class="text-sm text-gray-600">
                            לא מרוצים? נחזיר לכם את הכסף תוך 30 יום
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chapter accordion functionality
    const chapterHeaders = document.querySelectorAll('.chapter-header');
    
    chapterHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const content = this.nextElementSibling;
            const icon = this.querySelector('.chapter-icon');
            
            // Toggle content visibility
            content.classList.toggle('hidden');
            
            // Toggle icon rotation
            if (content.classList.contains('hidden')) {
                icon.classList.remove('transform', 'rotate-180');
            } else {
                icon.classList.add('transform', 'rotate-180');
            }
        });
    });
});

// Helper function to format duration in seconds to readable format
function formatDuration(seconds) {
    if (!seconds) return '0 דקות';
    
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    
    let result = '';
    if (hours > 0) {
        result += hours + ' שעות ';
    }
    if (minutes > 0 || hours === 0) {
        result += minutes + ' דקות';
    }
    
    return result.trim();
}
</script>

<?php
// Helper function to format duration in seconds to readable format
function formatDuration($seconds) {
    if (!$seconds) return '0 דקות';
    
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    
    $result = '';
    if ($hours > 0) {
        $result .= $hours . ' שעות ';
    }
    if ($minutes > 0 || $hours === 0) {
        $result .= $minutes . ' דקות';
    }
    
    return trim($result);
}

require_once '../includes/footer.php';
?>