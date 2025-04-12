<?php
/**
 * QuickCourse - Forgot Password Page
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
$email = '';
$message = '';
$messageType = '';
$emailSent = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    
    if (empty($email)) {
        $message = 'נא להזין כתובת דוא"ל';
        $messageType = 'error';
    } else {
        // Generate reset token
        $token = generateResetToken($email);
        
        if ($token) {
            // Send reset email
            $resetLink = SITE_URL . '/auth/reset-password.php?token=' . $token;
            
            $to = $email;
            $subject = "איפוס סיסמה ל" . SITE_NAME;
            
            $mailBody = "שלום,\n\n";
            $mailBody .= "קיבלנו בקשה לאיפוס הסיסמה שלך ב" . SITE_NAME . ".\n\n";
            $mailBody .= "לאיפוס הסיסמה, אנא לחץ על הקישור הבא או העתק אותו לדפדפן:\n";
            $mailBody .= $resetLink . "\n\n";
            $mailBody .= "קישור זה תקף למשך שעה אחת בלבד.\n\n";
            $mailBody .= "אם לא ביקשת לאפס את הסיסמה, אנא התעלם מהודעה זו.\n\n";
            $mailBody .= "בברכה,\n";
            $mailBody .= "צוות " . SITE_NAME;
            
            // Headers
            $headers = "From: " . ADMIN_EMAIL . "\r\n";
            $headers .= "Reply-To: " . ADMIN_EMAIL . "\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            
            // Send email
            $mailSent = mail($to, $subject, $mailBody, $headers);
            
            if ($mailSent) {
                $message = 'הוראות לאיפוס הסיסמה נשלחו לכתובת הדוא"ל שציינת.';
                $messageType = 'success';
                $emailSent = true;
            } else {
                $message = 'אירעה שגיאה בשליחת הדוא"ל. נסה שוב מאוחר יותר.';
                $messageType = 'error';
            }
        } else {
            // Don't reveal if email exists or not for security
            $message = 'אם כתובת הדוא"ל קיימת במערכת, הוראות לאיפוס הסיסמה יישלחו אליה.';
            $messageType = 'info';
            $emailSent = true;
        }
    }
}

// Set page title
$pageTitle = 'שחזור סיסמה';
require_once '../includes/header.php';
?>

<div class="flex justify-center">
    <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-md">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">שכחת את הסיסמה?</h1>
            <p class="text-gray-600 mt-2">הזן את כתובת הדוא"ל שלך ונשלח לך קישור לאיפוס הסיסמה</p>
        </div>
        
        <?php if (!empty($message)): ?>
            <?php
            $alertClass = 'bg-blue-100 border-blue-500 text-blue-700';
            if ($messageType == 'success') {
                $alertClass = 'bg-green-100 border-green-500 text-green-700';
            } else if ($messageType == 'error') {
                $alertClass = 'bg-red-100 border-red-500 text-red-700';
            }
            ?>
            <div class="<?= $alertClass ?> border-r-4 p-4 mb-6 rounded">
                <p><?= $message ?></p>
            </div>
        <?php endif; ?>
        
        <?php if (!$emailSent): ?>
            <form method="post" action="<?= SITE_URL ?>/auth/forgot-password.php" class="space-y-6">
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
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        שלח קישור לאיפוס
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 text-green-500 mb-4">
                    <i class="ri-mail-send-line text-3xl"></i>
                </div>
                <p class="text-gray-600">בדוק את תיבת הדואר הנכנס שלך (וגם את תיקיית דואר הזבל) לקישור איפוס הסיסמה.</p>
                <a href="<?= SITE_URL ?>/auth/login.php" class="mt-4 inline-block font-medium text-blue-600 hover:text-blue-500">
                    חזור לדף ההתחברות
                </a>
            </div>
        <?php endif; ?>
        
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                זוכר את הסיסמה?
                <a href="<?= SITE_URL ?>/auth/login.php" class="font-medium text-blue-600 hover:text-blue-500 mr-1">
                    התחבר
                </a>
            </p>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>