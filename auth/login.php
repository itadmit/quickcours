<?php
/**
 * QuickCourse - Login Page
 * User login functionality
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

// Initialize variables
$email = '';
$error = '';
$verificationNeeded = false;
$userId = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if it's a verification code submission
    if (isset($_POST['verification_code']) && isset($_POST['user_id'])) {
        $code = sanitize($_POST['verification_code']);
        $userId = (int)$_POST['user_id'];
        
        if (verifyCode($userId, $code)) {
            // Get user data for login
            $sql = "SELECT * FROM users WHERE user_id = ?";
            $user = Database::fetchOne($sql, [$userId]);
            
            if ($user) {
                // Set session data
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['package_id'] = $user['package_id'];
                $_SESSION['subscription_status'] = $user['subscription_status'];
                
                // Redirect based on user type
                switch ($user['user_type']) {
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
        } else {
            $verificationNeeded = true;
            $error = 'קוד האימות שגוי. נסה שנית.';
        }
    } else {
        // Regular login
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];
        
        if (empty($email) || empty($password)) {
            $error = 'נא למלא את כל השדות';
        } else {
            $result = loginUser($email, $password);
            
            if ($result['success']) {
                // Already redirected in loginUser function
            } else if (isset($result['needs_verification'])) {
                $verificationNeeded = true;
                $userId = $result['user_id'];
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Set page title
$pageTitle = 'התחברות';
require_once '../includes/header.php';
?>

<div class="flex justify-center">
    <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-md">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">התחברות למערכת</h1>
            <p class="text-gray-600 mt-2">הזן את פרטיך כדי להתחבר לחשבונך</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border-r-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                <p><?= $error ?></p>
            </div>
        <?php endif; ?>
        
        <?php if ($verificationNeeded): ?>
            <!-- Verification Code Form -->
            <form method="post" action="<?= SITE_URL ?>/auth/login.php" class="space-y-6">
                <input type="hidden" name="user_id" value="<?= $userId ?>">
                
                <div>
                    <label for="verification_code" class="block text-sm font-medium text-gray-700 mb-1">קוד אימות</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="ri-shield-keyhole-line text-gray-400"></i>
                        </div>
                        <input type="text" id="verification_code" name="verification_code" class="block w-full pr-10 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="הזן את קוד האימות שנשלח לדוא''ל שלך" required>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">קוד אימות נשלח לכתובת הדוא"ל שלך. תקף ל-15 דקות בלבד.</p>
                </div>
                
                <div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        אמת ואתחבר
                    </button>
                </div>
            </form>
        <?php else: ?>
            <!-- Login Form -->
            <form method="post" action="<?= SITE_URL ?>/auth/login.php" class="space-y-6">
                <div>
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
                </div>
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember_me" name="remember_me" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="remember_me" class="mr-2 block text-sm text-gray-700">
                            זכור אותי
                        </label>
                    </div>
                    
                    <div class="text-sm">
                        <a href="<?= SITE_URL ?>/auth/forgot-password.php" class="font-medium text-blue-600 hover:text-blue-500">
                            שכחת את הסיסמה?
                        </a>
                    </div>
                </div>
                
                <div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        התחבר
                    </button>
                </div>
            </form>
        <?php endif; ?>
        
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                אין לך חשבון עדיין?
                <a href="<?= SITE_URL ?>/auth/register.php" class="font-medium text-blue-600 hover:text-blue-500 mr-1">
                    הירשם עכשיו
                </a>
            </p>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>