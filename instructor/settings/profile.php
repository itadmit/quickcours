<?php
/**
 * QuickCourse - Profile Settings
 * Allows instructors to edit their profile information
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

// Initialize variables
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $firstName = sanitize($_POST['first_name']);
    $lastName = sanitize($_POST['last_name']);
    $phone = sanitize($_POST['phone']);
    $bio = sanitize($_POST['bio']);
    
    // Validate form data
    if (empty($firstName) || empty($lastName)) {
        $error = 'שם פרטי ושם משפחה הם שדות חובה';
    } else {
        // Upload profile image if provided
        $profileImage = $user['profile_image'];
        
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['size'] > 0) {
            if (isAllowedFileType($_FILES['profile_image'], 'image')) {
                $uploadedImage = uploadFile($_FILES['profile_image'], PROFILE_UPLOADS, 'image');
                
                if ($uploadedImage) {
                    // If updating, delete old image
                    if ($profileImage) {
                        deleteFile('assets/uploads/profiles/' . $profileImage);
                    }
                    
                    $profileImage = $uploadedImage;
                } else {
                    $error = 'שגיאה בהעלאת התמונה. נסה שוב.';
                }
            } else {
                $error = 'פורמט קובץ לא נתמך. נא להעלות קובץ תמונה (JPG, JPEG, PNG).';
            }
        }
        
        if (empty($error)) {
            // Update user data
            $data = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $phone,
                'profile_image' => $profileImage
            ];
            
            // Update user bio in the database
            $sql = "UPDATE instructor_profiles SET bio = ? WHERE user_id = ?";
            Database::update($sql, [$bio, $userId]);
            
            // Update user profile
            $updated = updateUserProfile($userId, $data);
            
            if ($updated) {
                $success = 'הפרופיל עודכן בהצלחה';
                
                // Update session data
                $_SESSION['user_name'] = $firstName . ' ' . $lastName;
                
                // Refresh user data
                $user = getCurrentUser();
            } else {
                $error = 'אירעה שגיאה בעדכון הפרופיל. נסה שוב.';
            }
        }
    }
}

// Get instructor profile data
$sql = "SELECT * FROM instructor_profiles WHERE user_id = ?";
$profile = Database::fetchOne($sql, [$userId]);

if (!$profile) {
    // Create empty profile if not exists
    $sql = "INSERT INTO instructor_profiles (user_id, bio) VALUES (?, '')";
    Database::insert($sql, [$userId]);
    
    $profile = [
        'bio' => '',
    ];
}

// Set page title
$pageTitle = 'הגדרות פרופיל';
require_once '../../includes/header.php';
?>

<div class="flex flex-col md:flex-row">
    <!-- Sidebar -->
    <div class="w-full md:w-64 bg-white md:min-h-screen md:border-l border-gray-200">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">הגדרות</h2>
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
                
                <li class="border-t border-gray-200 pt-2 mt-2">
                    <a href="<?= SITE_URL ?>/instructor/settings/profile.php" class="flex items-center px-4 py-2 text-blue-600 bg-blue-50 rounded-md sidebar-active">
                        <i class="ri-user-settings-line ml-2"></i>
                        <span>פרופיל</span>
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
                <li>
                    <a href="<?= SITE_URL ?>/instructor/settings/security.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md">
                        <i class="ri-shield-keyhole-line ml-2"></i>
                        <span>אבטחה</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="flex-1 p-6 bg-gray-50">
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border-r-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                <p><?= $error ?></p>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="bg-green-100 border-r-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                <p><?= $success ?></p>
            </div>
        <?php endif; ?>
        
        <!-- Profile Settings Form -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">הגדרות פרופיל</h2>
            
            <form method="post" action="<?= SITE_URL ?>/instructor/settings/profile.php" enctype="multipart/form-data" class="space-y-6">
                <!-- Profile Picture -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">תמונת פרופיל</label>
                    
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <?php if (!empty($user['profile_image'])): ?>
                                <img src="<?= getAssetUrl('assets/uploads/profiles/' . $user['profile_image']) ?>" alt="Profile" class="h-20 w-20 rounded-full object-cover">
                            <?php else: ?>
                                <div class="h-20 w-20 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="text-blue-600 font-medium text-xl">
                                        <?= substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mr-5">
                            <div class="relative bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm flex items-center cursor-pointer hover:bg-gray-50 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                <label for="profile_image" class="relative text-sm font-medium text-gray-700 pointer-events-none">
                                    <span>שנה תמונה</span>
                                </label>
                                <input id="profile_image" name="profile_image" type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer border-gray-300 rounded-md">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">PNG, JPG, GIF עד 2MB</p>
                        </div>
                    </div>
                </div>
                
                <!-- Basic Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">שם פרטי</label>
                        <input type="text" id="first_name" name="first_name" value="<?= $user['first_name'] ?>" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">שם משפחה</label>
                        <input type="text" id="last_name" name="last_name" value="<?= $user['last_name'] ?>" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">דואר אלקטרוני</label>
                    <input type="email" id="email" value="<?= $user['email'] ?>" class="block w-full border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-500" disabled>
                    <p class="text-xs text-gray-500 mt-1">לא ניתן לשנות את כתובת הדוא"ל. צור קשר עם תמיכה לשינוי.</p>
                </div>
                
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">טלפון (אופציונלי)</label>
                    <input type="tel" id="phone" name="phone" value="<?= $user['phone'] ?? '' ?>" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <!-- Instructor Bio -->
                <div>
                    <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">ביוגרפיה (אופציונלי)</label>
                    <textarea id="bio" name="bio" rows="5" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"><?= $profile['bio'] ?? '' ?></textarea>
                    <p class="text-xs text-gray-500 mt-1">תאר את עצמך, את הניסיון שלך ומה הסטודנטים יכולים ללמוד ממך. מידע זה יוצג בעמוד הקורס.</p>
                </div>
                
                <div class="border-t border-gray-200 pt-6">
                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            שמור שינויים
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Preview uploaded image
    $('#profile_image').change(function() {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                $('.rounded-full').attr('src', e.target.result);
                $('.rounded-full').removeClass('bg-blue-100').addClass('object-cover');
                $('.rounded-full span').hide();
            }
            
            reader.readAsDataURL(this.files[0]);
        }
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>