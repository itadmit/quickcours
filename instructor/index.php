<?php
/**
 * QuickCourse - Instructor Dashboard
 * Main dashboard for instructors
 */

// Include required files
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Start session if not already started
startSession();

// Check if user is logged in and is an instructor
if (!isLoggedIn() || !isInstructor()) {
    // Redirect to login page
    redirect('/auth/login.php');
}

// Get user info
$user = getCurrentUser();

// Check subscription status
if ($user['subscription_status'] == 'expired') {
    // Set flash message
    setFlashMessage('המנוי שלך פג תוקף. אנא חדש את המנוי כדי להמשיך.', 'warning');
}

// Get dashboard statistics
$userId = getCurrentUserId();

// Get course count
$sql = "SELECT COUNT(*) as count FROM courses WHERE user_id = ?";
$courseCount = Database::fetchOne($sql, [$userId])['count'] ?? 0;

// Get student count
$sql = "SELECT COUNT(DISTINCT user_id) as count FROM enrollments e 
        JOIN courses c ON e.course_id = c.course_id 
        WHERE c.user_id = ?";
$studentCount = Database::fetchOne($sql, [$userId])['count'] ?? 0;

// Get total earnings
$sql = "SELECT COALESCE(SUM(p.amount), 0) as total 
        FROM payments p 
        JOIN courses c ON p.course_id = c.course_id 
        WHERE c.user_id = ? AND p.status = 'completed'";
$totalEarnings = Database::fetchOne($sql, [$userId])['total'] ?? 0;

// Get recent sales
$sql = "SELECT p.payment_id, p.amount, p.payment_date, c.title as course_title, u.first_name, u.last_name 
        FROM payments p 
        JOIN courses c ON p.course_id = c.course_id 
        JOIN users u ON p.user_id = u.user_id 
        WHERE c.user_id = ? AND p.status = 'completed' 
        ORDER BY p.payment_date DESC 
        LIMIT 5";
$recentSales = Database::fetchAll($sql, [$userId]);

// Get recent students
$sql = "SELECT e.enrollment_id, e.enrollment_date, c.title as course_title, 
        u.user_id, u.first_name, u.last_name, u.profile_image 
        FROM enrollments e 
        JOIN courses c ON e.course_id = c.course_id 
        JOIN users u ON e.user_id = u.user_id 
        WHERE c.user_id = ? 
        ORDER BY e.enrollment_date DESC 
        LIMIT 5";
$recentStudents = Database::fetchAll($sql, [$userId]);

// Set page title
$pageTitle = 'דשבורד מדריך';
include_once '../includes/header.php';
?>

<!-- Instructor Dashboard -->
<div class="flex flex-col md:flex-row">
    <!-- Sidebar -->
    <div class="w-full md:w-64 bg-white md:min-h-screen md:border-l border-gray-200">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">דשבורד מדריך</h2>
        </div>
        <nav class="p-4">
            <ul class="space-y-2">
                <li>
                    <a href="<?= SITE_URL ?>/instructor" class="flex items-center px-4 py-2 text-blue-600 bg-blue-50 rounded-md sidebar-active">
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
                <li>
                    <a href="<?= SITE_URL ?>/instructor/settings/billing.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md">
                        <i class="ri-bank-card-line ml-2"></i>
                        <span>חיוב ותשלומים</span>
                    </a>
                </li>
                <?php if (hasPackagePermission($userId, 'white_label')): ?>
                <li>
                    <a href="<?= SITE_URL ?>/instructor/settings/branding.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md">
                        <i class="ri-palette-line ml-2"></i>
                        <span>מיתוג</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="flex-1 p-6 bg-gray-50">
        <!-- Package Info Alert -->
        <?php if ($user['subscription_status'] != 'active'): ?>
            <div class="bg-yellow-50 border-r-4 border-yellow-400 p-4 mb-6 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="ri-error-warning-line text-yellow-400 text-xl"></i>
                    </div>
                    <div class="mr-3">
                        <?php if ($user['subscription_status'] == 'trial'): ?>
                            <p class="text-sm text-yellow-700">
                                אתה נמצא בתקופת ניסיון. התקופה תסתיים ב-<?= formatDate($user['subscription_end_date']) ?>.
                                <a href="<?= SITE_URL ?>/instructor/settings/billing.php" class="font-medium underline">שדרג עכשיו</a>
                            </p>
                        <?php else: ?>
                            <p class="text-sm text-yellow-700">
                                המנוי שלך הסתיים. אנא חדש את המנוי כדי להמשיך להשתמש בכל התכונות.
                                <a href="<?= SITE_URL ?>/instructor/settings/billing.php" class="font-medium underline">חדש מנוי</a>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Page Heading -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">שלום, <?= $user['first_name'] ?></h1>
            <p class="text-gray-600">ברוך הבא לדשבורד שלך. הנה סיכום של הפעילות שלך.</p>
        </div>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Total Courses -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                        <i class="ri-book-open-line text-2xl"></i>
                    </div>
                    <div class="mr-4">
                        <h3 class="text-gray-500 text-sm">קורסים</h3>
                        <p class="text-2xl font-semibold text-gray-900"><?= $courseCount ?></p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="<?= SITE_URL ?>/instructor/courses" class="text-blue-600 hover:text-blue-700 text-sm flex items-center">
                        צפה בכל הקורסים
                        <i class="ri-arrow-left-line mr-1"></i>
                    </a>
                </div>
            </div>
            
            <!-- Total Students -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-500">
                        <i class="ri-user-star-line text-2xl"></i>
                    </div>
                    <div class="mr-4">
                        <h3 class="text-gray-500 text-sm">תלמידים</h3>
                        <p class="text-2xl font-semibold text-gray-900"><?= $studentCount ?></p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="<?= SITE_URL ?>/instructor/students" class="text-green-600 hover:text-green-700 text-sm flex items-center">
                        צפה בכל התלמידים
                        <i class="ri-arrow-left-line mr-1"></i>
                    </a>
                </div>
            </div>
            
            <!-- Total Earnings -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-500">
                        <i class="ri-money-shekel-circle-line text-2xl"></i>
                    </div>
                    <div class="mr-4">
                        <h3 class="text-gray-500 text-sm">סה"כ הכנסות</h3>
                        <p class="text-2xl font-semibold text-gray-900"><?= formatPrice($totalEarnings) ?></p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="<?= SITE_URL ?>/instructor/sales" class="text-purple-600 hover:text-purple-700 text-sm flex items-center">
                        צפה בכל המכירות
                        <i class="ri-arrow-left-line mr-1"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Recent Sales -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">מכירות אחרונות</h2>
                </div>
                
                <div class="p-4">
                    <?php if (empty($recentSales)): ?>
                        <div class="text-center py-4">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 text-gray-400 mb-3">
                                <i class="ri-shopping-cart-line text-xl"></i>
                            </div>
                            <p class="text-gray-500">אין מכירות עדיין</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="text-right">
                                        <th class="px-4 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider">תלמיד</th>
                                        <th class="px-4 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider">קורס</th>
                                        <th class="px-4 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider">סכום</th>
                                        <th class="px-4 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider">תאריך</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach ($recentSales as $sale): ?>
                                        <tr>
                                            <td class="px-4 py-3">
                                                <span class="text-sm font-medium text-gray-900">
                                                    <?= $sale['first_name'] . ' ' . $sale['last_name'] ?>
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="text-sm text-gray-900 truncate max-w-xs block">
                                                    <?= $sale['course_title'] ?>
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="text-sm text-gray-900">
                                                    <?= formatPrice($sale['amount']) ?>
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="text-sm text-gray-500">
                                                    <?= formatDate($sale['payment_date'], 'd/m/Y') ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4 text-center">
                            <a href="<?= SITE_URL ?>/instructor/sales" class="text-sm text-blue-600 hover:text-blue-700">
                                צפה בכל המכירות
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            </div>
            
            <!-- Recent Students -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">תלמידים חדשים</h2>
                </div>
                
                <div class="p-4">
                    <?php if (empty($recentStudents)): ?>
                        <div class="text-center py-4">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 text-gray-400 mb-3">
                                <i class="ri-user-star-line text-xl"></i>
                            </div>
                            <p class="text-gray-500">אין תלמידים עדיין</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($recentStudents as $student): ?>
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <?php if (!empty($student['profile_image'])): ?>
                                            <img src="<?= getAssetUrl('assets/uploads/profiles/' . $student['profile_image']) ?>" alt="Profile" class="h-10 w-10 rounded-full">
                                        <?php else: ?>
                                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                <span class="text-blue-600 font-medium">
                                                    <?= substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mr-3 min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            <?= $student['first_name'] . ' ' . $student['last_name'] ?>
                                        </p>
                                        <p class="text-sm text-gray-500 truncate">
                                            נרשם לקורס: <?= $student['course_title'] ?>
                                        </p>
                                    </div>
                                    <div class="flex-shrink-0 text-sm text-gray-500">
                                        <?= formatDate($student['enrollment_date'], 'd/m/Y') ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-4 text-center">
                            <a href="<?= SITE_URL ?>/instructor/students" class="text-sm text-blue-600 hover:text-blue-700">
                                צפה בכל התלמידים
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="mt-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">פעולות מהירות</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="<?= SITE_URL ?>/instructor/courses/create.php" class="bg-white p-4 rounded-lg shadow text-center hover:shadow-md transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-100 text-blue-600 mb-3">
                        <i class="ri-add-circle-line text-xl"></i>
                    </div>
                    <h3 class="font-medium text-gray-900">צור קורס חדש</h3>
                </a>
                
                <a href="<?= SITE_URL ?>/instructor/sales/coupons.php" class="bg-white p-4 rounded-lg shadow text-center hover:shadow-md transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-100 text-green-600 mb-3">
                        <i class="ri-coupon-line text-xl"></i>
                    </div>
                    <h3 class="font-medium text-gray-900">צור קופון</h3>
                </a>
                
                <a href="<?= SITE_URL ?>/instructor/students/message.php" class="bg-white p-4 rounded-lg shadow text-center hover:shadow-md transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-purple-100 text-purple-600 mb-3">
                        <i class="ri-mail-send-line text-xl"></i>
                    </div>
                    <h3 class="font-medium text-gray-900">שלח הודעה</h3>
                </a>
                
                <a href="<?= SITE_URL ?>/instructor/settings/profile.php" class="bg-white p-4 rounded-lg shadow text-center hover:shadow-md transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-yellow-100 text-yellow-600 mb-3">
                        <i class="ri-user-settings-line text-xl"></i>
                    </div>
                    <h3 class="font-medium text-gray-900">עדכון פרופיל</h3>
                </a>
            </div>
        </div>
        
        <!-- Resources -->
        <div class="mt-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">משאבים ועזרה</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded-lg shadow hover:shadow-md transition-all">
                    <div class="flex items-center mb-2">
                        <div class="flex-shrink-0 p-2 rounded-md bg-blue-100 text-blue-600">
                            <i class="ri-book-3-line text-lg"></i>
                        </div>
                        <h3 class="font-medium text-gray-900 mr-3">מדריך למשתמש</h3>
                    </div>
                    <p class="text-gray-600 text-sm">למד איך ליצור ולשווק קורסים באופן אפקטיבי.</p>
                    <a href="#" class="mt-2 text-sm text-blue-600 hover:text-blue-700 inline-block">קרא עוד</a>
                </div>
                
                <div class="bg-white p-4 rounded-lg shadow hover:shadow-md transition-all">
                    <div class="flex items-center mb-2">
                        <div class="flex-shrink-0 p-2 rounded-md bg-green-100 text-green-600">
                            <i class="ri-vidicon-line text-lg"></i>
                        </div>
                        <h3 class="font-medium text-gray-900 mr-3">סרטוני הדרכה</h3>
                    </div>
                    <p class="text-gray-600 text-sm">צפה בסרטונים קצרים על האפשרויות השונות של הפלטפורמה.</p>
                    <a href="#" class="mt-2 text-sm text-blue-600 hover:text-blue-700 inline-block">צפה בסרטונים</a>
                </div>
                
                <div class="bg-white p-4 rounded-lg shadow hover:shadow-md transition-all">
                    <div class="flex items-center mb-2">
                        <div class="flex-shrink-0 p-2 rounded-md bg-purple-100 text-purple-600">
                            <i class="ri-customer-service-2-line text-lg"></i>
                        </div>
                        <h3 class="font-medium text-gray-900 mr-3">תמיכה</h3>
                    </div>
                    <p class="text-gray-600 text-sm">צריך עזרה? צור קשר עם צוות התמיכה שלנו בכל שאלה.</p>
                    <a href="#" class="mt-2 text-sm text-blue-600 hover:text-blue-700 inline-block">צור קשר</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>