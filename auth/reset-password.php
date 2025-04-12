<?php
/**
 * QuickCourse - Reset Password Page
 * Password reset functionality
 */

// Include required files
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Start session
startSession();

// Check if user is already logged in
if (isLoggedIn()) {
    // Redirect to home page
    redirect('/');
}

// Initialize variables
$token = sanitize($_GET['token'] ?? '');
$error = '';
$resetComplete = false;

// Verify token
$userId = verifyResetToken($token);

if (!$userId) {
    $error = 'הקישור לאיפוס הסיסמה אינו תקף או שפג תוקפו. אנא בקש קישור חדש.';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $userId) {
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (empty($password)) {
        $error = 'נא להזין סיסמה חדשה';
    } else if ($password != $confirmPassword) {
        $error = 'הסיסמאות אינן תואמות';
    } else if (strlen($password) < MIN_PASSWORD_LENGTH) {
        $error = 'הסיסמה חייבת להכיל לפחות ' . MIN_PASSWORD_LENGTH . ' תווים';
    } else {
        // Reset password
        $result = resetPassword($userId, $password);
        
        if ($result) {
            // Delete used token
            $sql = "DELETE FROM password_resets WHERE user_id = ?";
            Database::update($sql, [$userId]);
            
            $resetComplete = true;
        } else {
            $error = 'אירעה שגיאה באיפוס הסיסמה. נסה שוב מאוחר יותר.';
        }
    }
}

// Set page title
$pageTitle = 'איפוס סיסמה';
require_once '../includes/header.php';
?>

<div class="flex justify-center">
    <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-md">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">איפוס סיסמה</h1>
            <p class="text-gray-600 mt-2">צור סיסמה חדשה לחשבונך</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border-r-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                <p><?= $error ?></p>
            </div>
        <?php endif; ?>
        
        <?php if ($resetComplete): ?>
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 text-green-500 mb-4">
                    <i class="ri-check-line text-3xl"></i>
                </div>
                <h2 class="text-xl font-semibold text-gray-800 mb-2">הסיסמה אופסה בהצלחה!</h2>
                <p class="text-gray-600 mb-6">תוכל כעת להתחבר עם הסיסמה החדשה שלך.</p>
                <a href="<?= SITE_URL ?>/auth/login.php" class="inline-block px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition">
                    עבור לדף ההתחברות
                </a>
            </div>
        <?php elseif ($userId): ?>
            <form method="post" action="<?= SITE_URL ?>/auth/reset-password.php?token=<?= $token ?>" class="space-y-6">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">סיסמה חדשה</label>
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
                
                <div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        אפס סיסמה
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 text-red-500 mb-4">
                    <i class="ri-error-warning-line text-3xl"></i>
                </div>
                <p class="text-gray-600 mb-6">הקישור לאיפוס הסיסמה אינו תקף או שפג תוקפו.</p>
                <a href="<?= SITE_URL ?>/auth/forgot-password.php" class="inline-block px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition">
                    בקש קישור חדש
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>