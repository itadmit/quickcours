<?php
/**
 * Authentication Functions
 * User authentication and session management
 */

/**
 * Start session if not already started
 */
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Register a new user
 */
function registerUser($email, $password, $firstName, $lastName, $userType, $package = null) {
    // Check if email already exists
    $sql = "SELECT user_id FROM users WHERE email = ?";
    $user = Database::fetchOne($sql, [$email]);
    
    if ($user) {
        return ['success' => false, 'message' => 'כתובת האימייל כבר קיימת במערכת'];
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Set default package for instructors
    if ($userType == USER_INSTRUCTOR && $package === null) {
        $package = PACKAGE_TEACHER; // Default to lowest package
    }
    
    // Set subscription end date (30 days trial)
    $subscriptionEndDate = date('Y-m-d', strtotime('+30 days'));
    
    // Insert new user
    $sql = "INSERT INTO users (email, password, first_name, last_name, user_type, package_id, subscription_status, subscription_end_date) 
            VALUES (?, ?, ?, ?, ?, ?, 'trial', ?)";
    
    try {
        $userId = Database::insert($sql, [
            $email, 
            $hashedPassword, 
            $firstName, 
            $lastName, 
            $userType, 
            $package,
            $subscriptionEndDate
        ]);
        
        // Track current IP
        trackUserIP($userId);
        
        return ['success' => true, 'user_id' => $userId];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'שגיאה ברישום המשתמש'];
    }
}

/**
 * Login user
 */
function loginUser($email, $password) {
    // Get user by email
    $sql = "SELECT * FROM users WHERE email = ?";
    $user = Database::fetchOne($sql, [$email]);
    
    if (!$user || !password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'שם משתמש או סיסמה שגויים'];
    }
    
    // Update last login time
    $sql = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
    Database::update($sql, [$user['user_id']]);
    
    // Check subscription status
    if ($user['user_type'] == USER_INSTRUCTOR && $user['subscription_status'] != 'active') {
        // Check if subscription has expired
        if ($user['subscription_end_date'] < date('Y-m-d')) {
            $sql = "UPDATE users SET subscription_status = 'expired' WHERE user_id = ?";
            Database::update($sql, [$user['user_id']]);
            $user['subscription_status'] = 'expired';
        }
    }
    
    // Check IP if verification is enabled
    if (VERIFY_IP) {
        $ipVerified = verifyUserIP($user['user_id']);
        
        if (!$ipVerified['success']) {
            return [
                'success' => false, 
                'needs_verification' => true,
                'user_id' => $user['user_id'],
                'message' => 'זיהינו התחברות ממכשיר חדש. נשלח קוד אימות לדוא"ל שלך.'
            ];
        }
    }
    
    // Start session and set user data
    startSession();
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['user_type'] = $user['user_type'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['package_id'] = $user['package_id'];
    $_SESSION['subscription_status'] = $user['subscription_status'];
    
    return ['success' => true, 'user' => $user];
}

/**
 * Logout user
 */
function logoutUser() {
    startSession();
    session_unset();
    session_destroy();
    
    // Clear cookies
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 42000, '/');
    }
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    startSession();
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user type
 */
function getCurrentUserType() {
    startSession();
    return $_SESSION['user_type'] ?? null;
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return getCurrentUserType() === USER_ADMIN;
}

/**
 * Check if user is instructor
 */
function isInstructor() {
    return getCurrentUserType() === USER_INSTRUCTOR;
}

/**
 * Check if user is student
 */
function isStudent() {
    return getCurrentUserType() === USER_STUDENT;
}

/**
 * Get current user info
 */
function getCurrentUser() {
    $userId = getCurrentUserId();
    
    if (!$userId) {
        return null;
    }
    
    $sql = "SELECT * FROM users WHERE user_id = ?";
    return Database::fetchOne($sql, [$userId]);
}

/**
 * Reset password
 */
function resetPassword($userId, $newPassword) {
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    $sql = "UPDATE users SET password = ? WHERE user_id = ?";
    $result = Database::update($sql, [$hashedPassword, $userId]);
    
    return $result > 0;
}

/**
 * Generate password reset token
 */
function generateResetToken($email) {
    // Check if email exists
    $sql = "SELECT user_id FROM users WHERE email = ?";
    $user = Database::fetchOne($sql, [$email]);
    
    if (!$user) {
        return false;
    }
    
    // Generate token
    $token = generateRandomString(32);
    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Store token in database
    $sql = "INSERT INTO password_resets (user_id, token, expiry) VALUES (?, ?, ?)";
    
    try {
        Database::insert($sql, [$user['user_id'], $token, $expiry]);
        return $token;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Verify password reset token
 */
function verifyResetToken($token) {
    $sql = "SELECT user_id FROM password_resets WHERE token = ? AND expiry > NOW()";
    $result = Database::fetchOne($sql, [$token]);
    
    return $result ? $result['user_id'] : false;
}

/**
 * Track user IP for security
 */
function trackUserIP($userId) {
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Check if this IP is already tracked for this user
    $sql = "SELECT access_id FROM ip_access WHERE user_id = ? AND ip_address = ?";
    $result = Database::fetchOne($sql, [$userId, $ipAddress]);
    
    if ($result) {
        // Update last access time
        $sql = "UPDATE ip_access SET last_access = NOW() WHERE access_id = ?";
        Database::update($sql, [$result['access_id']]);
        return true;
    }
    
    // Count existing approved IPs for this user
    $sql = "SELECT COUNT(*) as count FROM ip_access WHERE user_id = ? AND is_approved = 1";
    $count = Database::fetchOne($sql, [$userId]);
    
    // If this is the first IP or we're under the limit, auto-approve
    $autoApprove = ($count['count'] < 1) || ($count['count'] < MAX_DEVICES);
    
    // Insert new IP
    $sql = "INSERT INTO ip_access (user_id, ip_address, device_info, is_approved) 
            VALUES (?, ?, ?, ?)";
    
    try {
        Database::insert($sql, [$userId, $ipAddress, $userAgent, $autoApprove]);
        return $autoApprove;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Verify if current IP is approved for user
 */
function verifyUserIP($userId) {
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    
    // Check if this IP is approved for this user
    $sql = "SELECT is_approved FROM ip_access WHERE user_id = ? AND ip_address = ?";
    $result = Database::fetchOne($sql, [$userId, $ipAddress]);
    
    if (!$result) {
        // New IP, track it
        $tracked = trackUserIP($userId);
        
        if (!$tracked) {
            // New IP and over the device limit, needs verification
            $verificationCode = generateVerificationCode($userId);
            sendVerificationEmail($userId, $verificationCode);
            
            return [
                'success' => false,
                'message' => 'זיהינו התחברות ממכשיר חדש. נשלח קוד אימות לדוא"ל שלך.'
            ];
        }
    } else if (!$result['is_approved']) {
        // IP exists but not approved
        $verificationCode = generateVerificationCode($userId);
        sendVerificationEmail($userId, $verificationCode);
        
        return [
            'success' => false,
            'message' => 'זיהינו התחברות ממכשיר חדש. נשלח קוד אימות לדוא"ל שלך.'
        ];
    }
    
    return ['success' => true];
}

/**
 * Generate verification code
 */
function generateVerificationCode($userId) {
    $code = sprintf("%06d", rand(0, 999999));
    $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    
    // Store code in database
    $sql = "INSERT INTO verification_codes (user_id, code, expiry) VALUES (?, ?, ?)";
    Database::insert($sql, [$userId, $code, $expiry]);
    
    return $code;
}

/**
 * Send verification email
 */
function sendVerificationEmail($userId, $code) {
    $sql = "SELECT email, first_name FROM users WHERE user_id = ?";
    $user = Database::fetchOne($sql, [$userId]);
    
    if (!$user) {
        return false;
    }
    
    $to = $user['email'];
    $subject = "קוד אימות להתחברות ל" . SITE_NAME;
    
    $message = "שלום " . $user['first_name'] . ",\n\n";
    $message .= "קוד האימות שלך להתחברות הוא: " . $code . "\n\n";
    $message .= "הקוד תקף ל-15 דקות בלבד.\n\n";
    $message .= "אם לא ביקשת קוד אימות, אנא התעלם מהודעה זו ובדוק את אבטחת החשבון שלך.\n\n";
    $message .= "בברכה,\n";
    $message .= "צוות " . SITE_NAME;
    
    // Headers
    $headers = "From: " . ADMIN_EMAIL . "\r\n";
    $headers .= "Reply-To: " . ADMIN_EMAIL . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Verify code
 */
function verifyCode($userId, $code) {
    $sql = "SELECT * FROM verification_codes 
            WHERE user_id = ? AND code = ? AND expiry > NOW() 
            ORDER BY created_at DESC LIMIT 1";
    
    $result = Database::fetchOne($sql, [$userId, $code]);
    
    if (!$result) {
        return false;
    }
    
    // Approve the current IP
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $sql = "UPDATE ip_access SET is_approved = 1 WHERE user_id = ? AND ip_address = ?";
    Database::update($sql, [$userId, $ipAddress]);
    
    // Delete used code
    $sql = "DELETE FROM verification_codes WHERE code_id = ?";
    Database::update($sql, [$result['code_id']]);
    
    return true;
}

/**
 * Check if user has active subscription
 */
function hasActiveSubscription($userId) {
    $sql = "SELECT subscription_status FROM users WHERE user_id = ?";
    $result = Database::fetchOne($sql, [$userId]);
    
    return $result && $result['subscription_status'] == 'active';
}

/**
 * Check if user has reached storage limit
 */
function hasReachedStorageLimit($userId) {
    $sql = "SELECT u.user_id, p.storage_limit FROM users u 
            JOIN packages p ON u.package_id = p.package_id 
            WHERE u.user_id = ?";
    
    $result = Database::fetchOne($sql, [$userId]);
    
    if (!$result) {
        return true; // No package, can't upload
    }
    
    $storageLimit = $result['storage_limit'] * 1024 * 1024 * 1024; // Convert GB to bytes
    $storageUsed = getUserStorageUsed($userId);
    
    return $storageUsed >= $storageLimit;
}

/**
 * Update user profile
 */
function updateUserProfile($userId, $data) {
    $allowedFields = [
        'first_name',
        'last_name',
        'phone',
        'profile_image'
    ];
    
    $updates = [];
    $params = [];
    
    foreach ($data as $field => $value) {
        if (in_array($field, $allowedFields)) {
            $updates[] = "$field = ?";
            $params[] = $value;
        }
    }
    
    if (empty($updates)) {
        return false;
    }
    
    $params[] = $userId;
    
    $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE user_id = ?";
    $result = Database::update($sql, $params);
    
    return $result > 0;
}