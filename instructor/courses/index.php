<?php
/**
 * QuickCourse - Instructor Courses List
 * Shows all courses created by the instructor
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
$user = getCurrentUser();

// Get courses list
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Base query for courses
$countSql = "SELECT COUNT(*) as total FROM courses WHERE user_id = ?";
$totalCourses = Database::fetchOne($countSql, [$userId])['total'] ?? 0;

$totalPages = ceil($totalCourses / $limit);
$page = max(1, min($page, $totalPages));

// Status filter
$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$whereClause = "WHERE user_id = ?";
$params = [$userId];

if ($statusFilter && in_array($statusFilter, ['draft', 'published', 'archived'])) {
    $whereClause .= " AND status = ?";
    $params[] = $statusFilter;
}

// Get courses
$sql = "SELECT c.*, 
        (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.course_id) as student_count,
        (SELECT COALESCE(SUM(p.amount), 0) FROM payments p WHERE p.course_id = c.course_id AND p.status = 'completed') as earnings
        FROM courses c 
        $whereClause 
        ORDER BY c.created_at DESC 
        LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;

$courses = Database::fetchAll($sql, $params);

// Count courses by status
$statusCounts = [
    'all' => $totalCourses,
    'draft' => 0,
    'published' => 0,
    'archived' => 0
];

$countBySql = "SELECT status, COUNT(*) as count FROM courses WHERE user_id = ? GROUP BY status";
$statusResults = Database::fetchAll($countBySql, [$userId]);

foreach ($statusResults as $result) {
    $statusCounts[$result['status']] = $result['count'];
}

// Set page title
$pageTitle = 'הקורסים שלי';
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
                    <a href="<?= SITE_URL ?>/instructor/courses" class="flex items-center px-4 py-2 text-blue-600 bg-blue-50 rounded-md sidebar-active">
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
            <h1 class="text-2xl font-bold text-gray-900">הקורסים שלי</h1>
            
            <div class="mt-4 md:mt-0">
                <a href="<?= SITE_URL ?>/instructor/courses/create.php" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="ri-add-line ml-1"></i> צור קורס חדש
                </a>
            </div>
        </div>
        
        <!-- Status Tabs -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="flex flex-wrap border-b border-gray-200">
                <a href="<?= SITE_URL ?>/instructor/courses" class="px-6 py-3 text-sm font-medium text-center <?= !$statusFilter ? 'text-blue-600 border-b-2 border-blue-500' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' ?>">
                    כל הקורסים (<?= $statusCounts['all'] ?>)
                </a>
                <a href="<?= SITE_URL ?>/instructor/courses?status=published" class="px-6 py-3 text-sm font-medium text-center <?= $statusFilter == 'published' ? 'text-blue-600 border-b-2 border-blue-500' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' ?>">
                    מפורסמים (<?= $statusCounts['published'] ?>)
                </a>
                <a href="<?= SITE_URL ?>/instructor/courses?status=draft" class="px-6 py-3 text-sm font-medium text-center <?= $statusFilter == 'draft' ? 'text-blue-600 border-b-2 border-blue-500' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' ?>">
                    טיוטות (<?= $statusCounts['draft'] ?>)
                </a>
                <a href="<?= SITE_URL ?>/instructor/courses?status=archived" class="px-6 py-3 text-sm font-medium text-center <?= $statusFilter == 'archived' ? 'text-blue-600 border-b-2 border-blue-500' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' ?>">
                    בארכיון (<?= $statusCounts['archived'] ?>)
                </a>
            </div>
        </div>
        
        <!-- Courses List -->
        <?php if (empty($courses)): ?>
            <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 text-blue-600 mb-4">
                    <i class="ri-book-open-line text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">אין קורסים</h3>
                <p class="text-gray-600 mb-4">
                    <?php if ($statusFilter): ?>
                        לא נמצאו קורסים עם סטטוס "<?= $statusFilter ?>".
                    <?php else: ?>
                        עדיין לא יצרת קורסים. התחל ליצור את הקורס הראשון שלך עכשיו!
                    <?php endif; ?>
                </p>
                <a href="<?= SITE_URL ?>/instructor/courses/create.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                    <i class="ri-add-line ml-1"></i> צור קורס חדש
                </a>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    קורס
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    סטטוס
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    מחיר
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    תלמידים
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    הכנסות
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    תאריך יצירה
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    פעולות
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <?php if (!empty($course['cover_image'])): ?>
                                                    <img class="h-10 w-10 rounded object-cover" src="<?= getAssetUrl('assets/uploads/courses/' . $course['cover_image']) ?>" alt="<?= $course['title'] ?>">
                                                <?php else: ?>
                                                    <div class="h-10 w-10 rounded bg-blue-100 flex items-center justify-center">
                                                        <i class="ri-book-open-line text-blue-600"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="mr-4">
                                                <div class="text-sm font-medium text-gray-900 truncate max-w-xs">
                                                    <?= $course['title'] ?>
                                                </div>
                                                <div class="text-xs text-gray-500 truncate max-w-xs">
                                                    <?= $course['short_description'] ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($course['status'] == 'published'): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                מפורסם
                                            </span>
                                        <?php elseif ($course['status'] == 'draft'): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                טיוטה
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                בארכיון
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= $course['price'] > 0 ? formatPrice($course['price']) : 'חינם' ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= $course['student_count'] ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= formatPrice($course['earnings']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= formatDate($course['created_at'], 'd/m/Y') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                                        <div class="flex space-x-2 space-x-reverse">
                                            <a href="<?= SITE_URL ?>/instructor/courses/create.php?id=<?= $course['course_id'] ?>" class="text-blue-600 hover:text-blue-900" title="ערוך קורס">
                                                <i class="ri-edit-line"></i>
                                            </a>
                                            <a href="<?= SITE_URL ?>/instructor/courses/lessons.php?id=<?= $course['course_id'] ?>" class="text-green-600 hover:text-green-900" title="נהל שיעורים">
                                                <i class="ri-list-check"></i>
                                            </a>
                                            <?php if ($course['status'] == 'published'): ?>
                                                <a href="<?= SITE_URL ?>/courses/<?= $course['slug'] ?>" target="_blank" class="text-purple-600 hover:text-purple-900" title="צפה בקורס">
                                                    <i class="ri-eye-line"></i>
                                                </a>
                                            <?php endif; ?>
                                            <button type="button" class="text-red-600 hover:text-red-900 delete-course" data-id="<?= $course['course_id'] ?>" data-title="<?= $course['title'] ?>" title="מחק קורס">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="mt-6">
                    <?= pagination($page, $totalPages, "?status={$statusFilter}&page=%d") ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Course Modal -->
<div id="delete-modal" class="hidden fixed z-10 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="ri-error-warning-line text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:mr-4 sm:text-right">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            מחיקת קורס
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="modal-description">
                                האם אתה בטוח שברצונך למחוק את הקורס "<span id="course-title"></span>"? פעולה זו היא בלתי הפיכה והתלמידים לא יוכלו לגשת לקורס יותר.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <form id="delete-form" method="post" action="<?= SITE_URL ?>/instructor/courses/delete.php">
                    <input type="hidden" id="course-id" name="course_id" value="">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        מחק קורס
                    </button>
                </form>
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm close-modal">
                    ביטול
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Delete course modal
    $('.delete-course').click(function() {
        const courseId = $(this).data('id');
        const courseTitle = $(this).data('title');
        
        $('#course-id').val(courseId);
        $('#course-title').text(courseTitle);
        $('#delete-modal').removeClass('hidden');
    });
    
    $('.close-modal').click(function() {
        $('#delete-modal').addClass('hidden');
    });
    
    // Close modal when clicking outside
    $(window).click(function(event) {
        if ($(event.target).hasClass('fixed')) {
            $('#delete-modal').addClass('hidden');
        }
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>