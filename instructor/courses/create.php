<?php
/**
 * QuickCourse - Create/Edit Course
 * Allows instructors to create new courses or edit existing ones
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

// Check if this is an edit operation
$courseId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $courseId > 0;

// Check if user can create more courses
if (!$isEdit && !canCreateMoreCourses($userId)) {
    // Set flash message
    setFlashMessage('הגעת למגבלת הקורסים בחבילה שלך. אנא שדרג את החבילה כדי ליצור קורסים נוספים.', 'error');
    // Redirect to courses page
    redirect('/instructor/courses');
}

// Initialize variables
$course = [
    'title' => '',
    'slug' => '',
    'short_description' => '',
    'full_description' => '',
    'price' => 0,
    'status' => COURSE_DRAFT,
    'cover_image' => ''
];

// If editing, get course data
if ($isEdit) {
    // Fetch the course
    $sql = "SELECT * FROM courses WHERE course_id = ? AND user_id = ?";
    $fetchedCourse = Database::fetchOne($sql, [$courseId, $userId]);
    
    if (!$fetchedCourse) {
        // Course not found or doesn't belong to user
        setFlashMessage('הקורס המבוקש לא נמצא או שאינך מורשה לערוך אותו.', 'error');
        redirect('/instructor/courses');
    }
    
    $course = $fetchedCourse;
}

// Initialize error and success messages
$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $title = sanitize($_POST['title']);
    $shortDescription = sanitize($_POST['short_description']);
    $fullDescription = sanitize($_POST['full_description']);
    $price = (float)$_POST['price'];
    $status = sanitize($_POST['status']);
    
    // Generate slug from title if not editing
    $slug = $isEdit ? $course['slug'] : createSlug($title);
    
    // Validate form data
    if (empty($title)) {
        $errors[] = 'שם הקורס הוא שדה חובה';
    }
    
    if (empty($shortDescription)) {
        $errors[] = 'תיאור קצר הוא שדה חובה';
    }
    
    if ($price < 0) {
        $errors[] = 'המחיר אינו יכול להיות שלילי';
    }
    
    // If not editing, check if slug already exists
    if (!$isEdit) {
        $sql = "SELECT course_id FROM courses WHERE slug = ?";
        $existingCourse = Database::fetchOne($sql, [$slug]);
        
        if ($existingCourse) {
            $errors[] = 'שם הקורס כבר קיים במערכת. אנא בחר שם אחר.';
        }
    }
    
    // Process cover image if uploaded
    $coverImage = $isEdit ? $course['cover_image'] : '';
    
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['size'] > 0) {
        if (isAllowedFileType($_FILES['cover_image'], 'image')) {
            $uploadedImage = uploadFile($_FILES['cover_image'], COURSE_UPLOADS, 'image');
            
            if ($uploadedImage) {
                // If updating and had a previous image, delete it
                if ($isEdit && !empty($course['cover_image'])) {
                    deleteFile('assets/uploads/courses/' . $course['cover_image']);
                }
                
                $coverImage = $uploadedImage;
            } else {
                $errors[] = 'שגיאה בהעלאת תמונת הקורס. נסה שוב.';
            }
        } else {
            $errors[] = 'פורמט קובץ לא נתמך. נא להעלות קובץ תמונה (JPG, JPEG, PNG).';
        }
    }
    
    // If no errors, save course
    if (empty($errors)) {
        if ($isEdit) {
            // Update existing course
            $sql = "UPDATE courses SET 
                    title = ?, 
                    short_description = ?, 
                    full_description = ?, 
                    price = ?, 
                    status = ?, 
                    cover_image = ?,
                    updated_at = NOW()
                    WHERE course_id = ? AND user_id = ?";
            
            $params = [
                $title,
                $shortDescription,
                $fullDescription,
                $price,
                $status,
                $coverImage,
                $courseId,
                $userId
            ];
            
            $result = Database::update($sql, $params);
            
            if ($result) {
                $success = 'הקורס עודכן בהצלחה!';
                
                // Refresh course data
                $course = [
                    'course_id' => $courseId,
                    'title' => $title,
                    'slug' => $slug,
                    'short_description' => $shortDescription,
                    'full_description' => $fullDescription,
                    'price' => $price,
                    'status' => $status,
                    'cover_image' => $coverImage
                ];
            } else {
                $errors[] = 'אירעה שגיאה בעדכון הקורס. נסה שוב.';
            }
        } else {
            // Create new course
            $sql = "INSERT INTO courses (
                    user_id, 
                    title, 
                    slug, 
                    short_description, 
                    full_description, 
                    price, 
                    status, 
                    cover_image, 
                    created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $params = [
                $userId,
                $title,
                $slug,
                $shortDescription,
                $fullDescription,
                $price,
                $status,
                $coverImage
            ];
            
            $newCourseId = Database::insert($sql, $params);
            
            if ($newCourseId) {
                // Set success message
                setFlashMessage('הקורס נוצר בהצלחה! עכשיו אתה יכול להוסיף תוכן.', 'success');
                
                // Redirect to lessons page
                redirect('/instructor/courses/lessons.php?id=' . $newCourseId);
            } else {
                $errors[] = 'אירעה שגיאה ביצירת הקורס. נסה שוב.';
            }
        }
    }
}

// Set page title
$pageTitle = $isEdit ? 'עריכת קורס' : 'יצירת קורס חדש';
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
                    <a href="<?= SITE_URL ?>/instructor/courses/create.php" class="flex items-center px-4 py-2 text-blue-600 bg-blue-50 rounded-md sidebar-active">
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
            <h1 class="text-2xl font-bold text-gray-900">
                <?= $isEdit ? 'עריכת קורס: ' . $course['title'] : 'יצירת קורס חדש' ?>
            </h1>
            
            <?php if ($isEdit): ?>
            <div class="mt-4 md:mt-0 flex space-x-2 space-x-reverse">
                <a href="<?= SITE_URL ?>/instructor/courses/lessons.php?id=<?= $courseId ?>" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="ri-list-check ml-1"></i> ניהול תוכן
                </a>
                <?php if ($course['status'] == 'published'): ?>
                <a href="<?= SITE_URL ?>/courses/<?= $course['slug'] ?>" target="_blank" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="ri-eye-line ml-1"></i> צפה בקורס
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Form Errors -->
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border-r-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                <ul class="list-disc mr-5">
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <!-- Success Message -->
        <?php if (!empty($success)): ?>
            <div class="bg-green-100 border-r-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                <p><?= $success ?></p>
            </div>
        <?php endif; ?>
        
        <!-- Course Form -->
        <form method="post" action="<?= $isEdit ? SITE_URL . '/instructor/courses/create.php?id=' . $courseId : SITE_URL . '/instructor/courses/create.php' ?>" enctype="multipart/form-data" class="bg-white rounded-lg shadow-sm p-6">
            <!-- Basic Course Info Section -->
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">מידע בסיסי</h2>
                
                <div class="space-y-6">
                    <!-- Course Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">שם הקורס <span class="text-red-500">*</span></label>
                        <input type="text" id="title" name="title" value="<?= $course['title'] ?>" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                        <p class="text-xs text-gray-500 mt-1">בחר שם קליט וברור שמתאר את תוכן הקורס.</p>
                    </div>
                    
                    <!-- Course Short Description -->
                    <div>
                        <label for="short_description" class="block text-sm font-medium text-gray-700 mb-1">תיאור קצר <span class="text-red-500">*</span></label>
                        <input type="text" id="short_description" name="short_description" value="<?= $course['short_description'] ?>" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                        <p class="text-xs text-gray-500 mt-1">תיאור קצר שיופיע בעמוד הקטלוג (עד 160 תווים).</p>
                    </div>
                    
                    <!-- Course Full Description -->
                    <div>
                        <label for="full_description" class="block text-sm font-medium text-gray-700 mb-1">תיאור מלא</label>
                        <textarea id="full_description" name="full_description" rows="6" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"><?= $course['full_description'] ?></textarea>
                        <p class="text-xs text-gray-500 mt-1">תיאור מפורט של הקורס, כולל מה התלמידים ילמדו, דרישות קדם ויתרונות.</p>
                    </div>
                </div>
            </div>
            
            <!-- Media Section -->
            <div class="mb-8 border-t border-gray-200 pt-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">מדיה</h2>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">תמונת נושא</label>
                    
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-32 w-40 bg-gray-100 rounded-md overflow-hidden">
                            <?php if (!empty($course['cover_image'])): ?>
                                <img src="<?= getAssetUrl('assets/uploads/courses/' . $course['cover_image']) ?>" alt="<?= $course['title'] ?>" class="h-full w-full object-cover">
                            <?php else: ?>
                                <div class="h-full w-full flex items-center justify-center">
                                    <i class="ri-image-line text-gray-400 text-4xl"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mr-5">
                            <div class="relative bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm flex items-center cursor-pointer hover:bg-gray-50 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                <label for="cover_image" class="relative text-sm font-medium text-gray-700 pointer-events-none">
                                    <span>העלה תמונה</span>
                                </label>
                                <input id="cover_image" name="cover_image" type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer border-gray-300 rounded-md">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">PNG, JPG בגודל מינימלי של 600x400 פיקסלים.</p>
                            <p class="text-xs text-gray-500">תמונה איכותית מגדילה את סיכויי המכירה.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Price & Status Section -->
            <div class="mb-8 border-t border-gray-200 pt-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">מחיר וסטטוס</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Course Price -->
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-1">מחיר (₪)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500">₪</span>
                            </div>
                            <input type="number" id="price" name="price" value="<?= $course['price'] ?>" min="0" step="0.01" class="block w-full pr-8 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">הזן 0 עבור קורס חינמי.</p>
                    </div>
                    
                    <!-- Course Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">סטטוס</label>
                        <select id="status" name="status" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="draft" <?= $course['status'] == 'draft' ? 'selected' : '' ?>>טיוטה</option>
                            <option value="published" <?= $course['status'] == 'published' ? 'selected' : '' ?>>מפורסם</option>
                            <option value="archived" <?= $course['status'] == 'archived' ? 'selected' : '' ?>>בארכיון</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            <span class="font-medium">טיוטה:</span> נשמר אך לא גלוי לתלמידים.
                            <span class="font-medium">מפורסם:</span> גלוי וזמין לרכישה.
                            <span class="font-medium">בארכיון:</span> מוסתר מתלמידים חדשים.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="flex justify-end space-x-2 space-x-reverse border-t border-gray-200 pt-6">
                <a href="<?= SITE_URL ?>/instructor/courses" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    ביטול
                </a>
                <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <?= $isEdit ? 'שמור שינויים' : 'צור קורס' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // Preview uploaded image
    $('#cover_image').change(function() {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                $('.bg-gray-100 img').remove();
                $('.bg-gray-100 div').remove();
                $('.bg-gray-100').append('<img src="' + e.target.result + '" class="h-full w-full object-cover" />');
            }
            
            reader.readAsDataURL(this.files[0]);
        }
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>