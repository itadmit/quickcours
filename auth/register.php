<?php
/**
 * QuickCourse - Registration Page
 * User registration functionality
 */

// Include required files
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Start session
startSession();

// Check if user is already logged in
if (isLoggedIn()) {
    // Redirect based on user type
    switch (getCurrentUserType()) {
        case USER_ADMIN:
            redirect('/admin');
            break;
        case USER_INSTRUCTOR:
            redirect('/instructor');
            break;
        case USER_STUDENT:
            redirect('/student/courses');
            break;
        default:
            redirect('/');
    }
}

// Get package ID from URL if provided
$packageId = isset($_GET['package']) ? (int)$_GET['package'] : null;

// Initialize variables
$firstName = '';
$lastName = '';
$email = '';
$userType = '';
$error = '';

// Get packages data for instructors
$sql = "SELECT * FROM packages WHERE is_active = 1 ORDER BY price_monthly ASC";
$packages = Database::fetchAll($sql);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $firstName = sanitize($_POST['first_name']);
    $lastName = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $userType = sanitize($_POST['user_type']);
    $selectedPackage = isset($_POST['package_id']) ? (int)$_POST['package_id'] : null;
    
    // Validate form data
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($userType)) {
        $error = 'נא למלא את כל השדות';
    } else if ($password != $confirmPassword) {
        $error = 'הסיסמאות אינן תואמות';
    } else if (strlen($password) < MIN_PASSWORD_LENGTH) {
        $error = 'הסיסמה חייבת להכיל לפחות ' . MIN_PASSWORD_LENGTH . ' תווים';
    } else if ($userType == USER_INSTRUCTOR && empty($selectedPackage)) {
        $error = 'נא לבחור חבילה';
    } else {
        // Register the user
        $result = registerUser($email, $password, $firstName, $lastName, $userType, $selectedPackage);
        
        if ($result['success']) {
            // Set flash message
            setFlashMessage('נרשמת בהצלחה! אנא התחבר עם הפרטים שלך.', 'success');
            
            // Redirect to login page
            redirect('/auth/login.php');
        } else {
            $error = $result['message'];
        }
    }
}

// Set page title
$pageTitle = 'הרשמה';
require_once '../includes/header.php';
?>

<div class="flex justify-center">
    <div class="w-full max-w-lg p-6 bg-white rounded-lg shadow-md">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">הרשמה למערכת</h1>
            <p class="text-gray-600 mt-2">צור חשבון חדש בקוויק קורס</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border-r-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                <p><?= $error ?></p>
            </div>
        <?php endif; ?>
        
        <form method="post" action="<?= SITE_URL ?>/auth/register.php<?= $packageId ? '?package=' . $packageId : '' ?>" class="space-y-6">
            <!-- User Type Selection -->
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">סוג משתמש</label>
                    <div class="flex space-x-4 space-x-reverse">
                        <label class="relative flex items-center p-3 rounded-lg border border-gray-300 bg-white shadow-sm cursor-pointer hover:border-blue-500 transition-all <?= $userType == USER_INSTRUCTOR ? 'border-blue-500 bg-blue-50' : '' ?>">
                            <input type="radio" name="user_type" value="<?= USER_INSTRUCTOR ?>" class="sr-only" <?= $userType == USER_INSTRUCTOR ? 'checked' : '' ?> required>
                            <span class="flex items-center">
                                <i class="ri-user-star-line text-2xl text-blue-600 ml-3"></i>
                                <span class="text-sm font-medium">
                                    <div class="font-semibold">מורה / מדריך</div>
                                    <div class="text-xs text-gray-500">אני רוצה ליצור ולמכור קורסים</div>
                                </span>
                            </span>
                        </label>
                        <label class="relative flex items-center p-3 rounded-lg border border-gray-300 bg-white shadow-sm cursor-pointer hover:border-blue-500 transition-all <?= $userType == USER_STUDENT ? 'border-blue-500 bg-blue-50' : '' ?>">
                            <input type="radio" name="user_type" value="<?= USER_STUDENT ?>" class="sr-only" <?= $userType == USER_STUDENT ? 'checked' : '' ?> required>
                            <span class="flex items-center">
                                <i class="ri-user-line text-2xl text-green-600 ml-3"></i>
                                <span class="text-sm font-medium">
                                    <div class="font-semibold">תלמיד</div>
                                    <div class="text-xs text-gray-500">אני רוצה לרכוש ולצפות בקורסים</div>
                                </span>
                            </span>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Personal Information -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">שם פרטי</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="ri-user-line text-gray-400"></i>
                        </div>
                        <input type="text" id="first_name" name="first_name" value="<?= $firstName ?>" class="block w-full pr-10 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                </div>
                
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">שם משפחה</label>
                    <input type="text" id="last_name" name="last_name" value="<?= $lastName ?>" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                
                <div class="col-span-2">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">דואר אלקטרוני</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="ri-mail-line text-gray-400"></i>
                        </div>
                        <input type="email" id="email" name="email" value="<?= $email ?>" class="block w-full pr-10 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="your@email.com" required>
                    </div>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">סיסמה</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="ri-lock-line text-gray-400"></i>
                        </div>
                        <input type="password" id="password" name="password" class="block w-full pr-10 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="••••••••" required>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">לפחות <?= MIN_PASSWORD_LENGTH ?> תווים</p>
                </div>
                
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">אימות סיסמה</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="ri-lock-line text-gray-400"></i>
                        </div>
                        <input type="password" id="confirm_password" name="confirm_password" class="block w-full pr-10 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="••••••••" required>
                    </div>
                </div>
            </div>
            
            <!-- Package Selection (for instructors only) -->
            <div id="package_selection" class="<?= $userType == USER_INSTRUCTOR ? '' : 'hidden' ?> border-t border-gray-200 pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">בחר חבילה</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <?php foreach ($packages as $package): ?>
                        <label class="relative flex flex-col p-4 rounded-lg border <?= ($packageId == $package['package_id'] || count($packages) == 1) ? 'border-blue-500 bg-blue-50' : 'border-gray-300' ?> shadow-sm cursor-pointer hover:border-blue-500 transition">
                            <input type="radio" name="package_id" value="<?= $package['package_id'] ?>" class="sr-only" <?= ($packageId == $package['package_id'] || count($packages) == 1) ? 'checked' : '' ?>>
                            
                            <?php if ($package['package_id'] == PACKAGE_COORDINATOR): ?>
                                <div class="absolute top-0 left-0 right-0 -mt-3 text-center">
                                    <span class="inline-block bg-blue-500 text-white text-xs font-semibold py-1 px-2 rounded">פופולרי</span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="text-center mb-2">
                                <h4 class="text-lg font-semibold text-gray-800"><?= $package['name'] ?></h4>
                                <div class="mt-1 flex justify-center items-baseline">
                                    <span class="text-2xl font-bold text-gray-900"><?= CURRENCY_SYMBOL ?><?= $package['price_monthly'] ?></span>
                                    <span class="text-gray-500 mr-1">/חודש</span>
                                </div>
                            </div>
                            
                            <ul class="mt-2 space-y-2 text-sm text-gray-700 flex-grow">
                                <li class="flex items-center">
                                    <i class="ri-check-line text-green-500 ml-1 text-lg"></i>
                                    <span>
                                        <?= $package['max_courses'] == 9999 ? 'קורסים ללא הגבלה' : 'עד ' . $package['max_courses'] . ' קורסים' ?>
                                    </span>
                                </li>
                                <li class="flex items-center">
                                    <i class="ri-check-line text-green-500 ml-1 text-lg"></i>
                                    <span>
                                        <?= $package['max_students'] == 9999 ? 'תלמידים ללא הגבלה' : 'עד ' . $package['max_students'] . ' תלמידים' ?>
                                    </span>
                                </li>
                                <li class="flex items-center">
                                    <i class="ri-check-line text-green-500 ml-1 text-lg"></i>
                                    <span><?= $package['storage_limit'] ?>GB אחסון</span>
                                </li>
                            </ul>
                            
                            <!-- Indicate Selected Plan -->
                            <div class="mt-4 pt-2 border-t border-gray-100">
                                <div class="h-5 flex items-center justify-center">
                                    <div class="package-indicator hidden text-blue-600 font-medium text-sm">נבחר</div>
                                </div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
                
                <p class="text-sm text-gray-500 mt-4">
                    מחיר חבילה מחויב חודשית. ניתן לשדרג או לבטל בכל עת.
                </p>
            </div>
            
            <!-- Terms & Submit Button -->
            <div class="border-t border-gray-200 pt-6">
                <div class="flex items-center mb-4">
                    <input id="terms" name="terms" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" required>
                    <label for="terms" class="mr-2 block text-sm text-gray-700">
                        אני מסכים ל<a href="<?= SITE_URL ?>/terms.php" class="text-blue-600 hover:text-blue-500">תנאי השימוש</a> ול<a href="<?= SITE_URL ?>/privacy.php" class="text-blue-600 hover:text-blue-500">מדיניות הפרטיות</a>
                    </label>
                </div>
                
                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    צור חשבון
                </button>
            </div>
        </form>
        
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                יש לך כבר חשבון?
                <a href="<?= SITE_URL ?>/auth/login.php" class="font-medium text-blue-600 hover:text-blue-500 mr-1">
                    התחבר
                </a>
            </p>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Toggle package selection based on user type
    $('input[name="user_type"]').change(function() {
        if ($(this).val() == '<?= USER_INSTRUCTOR ?>') {
            $('#package_selection').removeClass('hidden');
        } else {
            $('#package_selection').addClass('hidden');
        }
    });
    
    // Update package selection styling
    $('input[name="package_id"]').change(function() {
        $('input[name="package_id"]').each(function() {
            var label = $(this).closest('label');
            if ($(this).is(':checked')) {
                label.addClass('border-blue-500 bg-blue-50');
                label.find('.package-indicator').removeClass('hidden');
            } else {
                label.removeClass('border-blue-500 bg-blue-50');
                label.find('.package-indicator').addClass('hidden');
            }
        });
    });
    
    // Trigger change event on page load
    $('input[name="package_id"]:checked').change();
});
</script>

<?php require_once '../includes/footer.php'; ?>