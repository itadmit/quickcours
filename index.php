<?php
/**
 * QuickCourse - Home Page
 * Main landing page for the QuickCourse platform
 */

// Include required files
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Set page title
$pageTitle = "דף הבית";

// Helper function to get asset URL (supports S3 in production)

// Get current user if logged in
$currentUser = null;
if (isLoggedIn()) {
    $currentUser = getCurrentUser();
}

// Include header
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="py-12 md:py-20 bg-gradient-to-r from-blue-50 to-indigo-50">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center">
            <!-- Hero Content -->
            <div class="md:w-1/2 mb-10 md:mb-0">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">צור ומכור <span class="text-blue-600">קורסים אונליין</span> בקלות</h1>
                <p class="text-xl text-gray-600 mb-8">
                    פלטפורמה פשוטה וחזקה לבניית, שיווק ומכירת הידע שלך דרך קורסים אונליין.
                    בנה את העסק הדיגיטלי שלך ללא צורך בידע טכני.
                </p>
                <div class="flex flex-col sm:flex-row">
                    <a href="<?= SITE_URL ?>/auth/register.php" class="mb-4 sm:mb-0 sm:ml-4 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-md transition">
                        התחל בחינם
                    </a>
                    <a href="<?= SITE_URL ?>/courses" class="px-6 py-3 bg-white hover:bg-gray-50 text-blue-600 font-medium rounded-lg shadow-md border border-gray-200 transition">
                        גלה קורסים
                    </a>
                </div>
                <div class="mt-8 flex items-center text-gray-500">
                    <div class="flex items-center ml-6">
                        <i class="ri-check-line text-green-500 ml-2"></i>
                        <span>ללא הגבלת זמן</span>
                    </div>
                    <div class="flex items-center ml-6">
                        <i class="ri-check-line text-green-500 ml-2"></i>
                        <span>אפס עמלות</span>
                    </div>
                    <div class="flex items-center">
                        <i class="ri-check-line text-green-500 ml-2"></i>
                        <span>תמיכה 24/7</span>
                    </div>
                </div>
            </div>
            
            <!-- Hero Image -->
            <div class="md:w-1/2">
                <img src="https://plus.unsplash.com/premium_photo-1682974406904-15916a7501f1?w=900&auto=format&fit=crop&q=60&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1yZWxhdGVkfDh8fHxlbnwwfHx8fHw%3D" alt="קוויק קורס - פלטפורמת קורסים אונליין" class="w-full h-auto">
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">למה לבחור בקוויק קורס?</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                פלטפורמה המשלבת פשטות תפעול עם כל הכלים הדרושים לך להצלחה עם קורסים אונליין
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <div class="card bg-white p-6 text-center">
                <div class="w-16 h-16 mx-auto mb-6 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="ri-edit-box-line text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-3">יצירת קורס פשוטה</h3>
                <p class="text-gray-600">
                    ממשק פשוט ואינטואיטיבי המאפשר להעלות תכנים, לארגן פרקים ושיעורים בקלות וליצור חווית למידה נהדרת.
                </p>
            </div>
            
            <!-- Feature 2 -->
            <div class="card bg-white p-6 text-center">
                <div class="w-16 h-16 mx-auto mb-6 rounded-full bg-green-100 flex items-center justify-center">
                    <i class="ri-shopping-cart-2-line text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-3">חנות ותשלומים מובנית</h3>
                <p class="text-gray-600">
                    מערכת תשלומים אוטומטית, תמיכה בכרטיסי אשראי ו-PayPal, וחנות מותאמת אישית למכירת הקורסים.
                </p>
            </div>
            
            <!-- Feature 3 -->
            <div class="card bg-white p-6 text-center">
                <div class="w-16 h-16 mx-auto mb-6 rounded-full bg-purple-100 flex items-center justify-center">
                    <i class="ri-bar-chart-box-line text-purple-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-3">אנליטיקה מתקדמת</h3>
                <p class="text-gray-600">
                    מעקב אחר התקדמות התלמידים, סטטיסטיקות מכירה, וניתוח ביצועים לשיפור התוכן והמכירות.
                </p>
            </div>
            
            <!-- Feature 4 -->
            <div class="card bg-white p-6 text-center">
                <div class="w-16 h-16 mx-auto mb-6 rounded-full bg-red-100 flex items-center justify-center">
                    <i class="ri-shield-check-line text-red-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-3">אבטחה מתקדמת</h3>
                <p class="text-gray-600">
                    הגנה על התכנים שלך עם מערכת אבטחה מתקדמת, בקרת גישה לפי IP ואימות דו-שלבי.
                </p>
            </div>
            
            <!-- Feature 5 -->
            <div class="card bg-white p-6 text-center">
                <div class="w-16 h-16 mx-auto mb-6 rounded-full bg-yellow-100 flex items-center justify-center">
                    <i class="ri-customer-service-2-line text-yellow-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-3">תמיכה ושירות</h3>
                <p class="text-gray-600">
                    תמיכה טכנית זמינה, מרכז עזרה מקיף, וצוות שירות לקוחות מסור לפתרון כל בעיה.
                </p>
            </div>
            
            <!-- Feature 6 -->
            <div class="card bg-white p-6 text-center">
                <div class="w-16 h-16 mx-auto mb-6 rounded-full bg-pink-100 flex items-center justify-center">
                    <i class="ri-palette-line text-pink-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-3">מיתוג אישי</h3>
                <p class="text-gray-600">
                    התאמת חווית המשתמש למותג שלך עם התאמות צבעים, לוגו וממשק בעיצוב אישי.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Section -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">תוכניות מחירים פשוטות</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                בחר את התוכנית המתאימה לצרכים שלך, ללא עלויות נסתרות
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Pricing Card 1 -->
            <div class="card bg-white rounded-lg overflow-hidden shadow-lg transition-all hover:shadow-xl">
                <div class="p-6 text-center border-b border-gray-100">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">מורה פרטי</h3>
                    <p class="text-gray-500 mb-6">מיועד למורים פרטיים או מרצים עצמאיים</p>
                    <div class="flex justify-center items-baseline mb-4">
                        <span class="text-4xl font-bold text-gray-900"><?= CURRENCY_SYMBOL ?>49</span>
                        <span class="text-gray-500 mr-2">/חודש</span>
                    </div>
                    <p class="text-green-600 text-sm mb-4">או <?= CURRENCY_SYMBOL ?>490 לשנה (חיסכון של 17%)</p>
                </div>
                <div class="p-6">
                    <ul class="space-y-3">
                        <li class="flex items-center">
                            <i class="ri-check-line text-green-500 ml-2"></i>
                            <span>עד 5 קורסים פעילים</span>
                        </li>
                        <li class="flex items-center">
                            <i class="ri-check-line text-green-500 ml-2"></i>
                            <span>עד 100 תלמידים רשומים</span>
                        </li>
                        <li class="flex items-center">
                            <i class="ri-check-line text-green-500 ml-2"></i>
                            <span>10GB אחסון למדיה</span>
                        </li>
                        <li class="flex items-center">
                            <i class="ri-check-line text-green-500 ml-2"></i>
                            <span>תמיכה בדוא"ל</span>
                        </li>
                    </ul>
                    <a href="<?= SITE_URL ?>/auth/register.php?package=<?= PACKAGE_TEACHER ?>" class="block w-full text-center mt-8 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-md transition">
                        הצטרף עכשיו
                    </a>
                </div>
            </div>
            
            <!-- Pricing Card 2 -->
            <div class="card bg-white rounded-lg overflow-hidden shadow-lg transition-all hover:shadow-xl transform scale-105 z-10 border-2 border-blue-500">
                <div class="bg-blue-500 text-white py-2 text-center text-sm font-medium">
                    פופולרי ביותר
                </div>
                <div class="p-6 text-center border-b border-gray-100">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">רכז שכבה</h3>
                    <p class="text-gray-500 mb-6">מיועד למרצים מקצועיים או עסקים קטנים</p>
                    <div class="flex justify-center items-baseline mb-4">
                        <span class="text-4xl font-bold text-gray-900"><?= CURRENCY_SYMBOL ?>149</span>
                        <span class="text-gray-500 mr-2">/חודש</span>
                    </div>
                    <p class="text-green-600 text-sm mb-4">או <?= CURRENCY_SYMBOL ?>1,490 לשנה (חיסכון של 17%)</p>
                </div>
                <div class="p-6">
                    <ul class="space-y-3">
                        <li class="flex items-center">
                            <i class="ri-check-line text-green-500 ml-2"></i>
                            <span>עד 15 קורסים פעילים</span>
                        </li>
                        <li class="flex items-center">
                            <i class="ri-check-line text-green-500 ml-2"></i>
                            <span>עד 500 תלמידים רשומים</span>
                        </li>
                        <li class="flex items-center">
                            <i class="ri-check-line text-green-500 ml-2"></i>
                            <span>50GB אחסון למדיה</span>
                        </li>
                        <li class="flex items-center">
                            <i class="ri-check-line text-green-500 ml-2"></i>
                            <span>דומיין משנה מותאם אישית</span>
                        </li>
                        <li class="flex items-center">
                            <i class="ri-check-line text-green-500 ml-2"></i>
                            <span>דוחות מתקדמים</span>
                        </li>
                        <li class="flex items-center">
                            <i class="ri-check-line text-green-500 ml-2"></i>
                            <span>תמיכה בדוא"ל וצ'אט</span>
                        </li>
                        <li class="flex items-center">
                            <i class="ri-check-line text-green-500 ml-2"></i>
                            <span>מותג לבן (white label) בסיסי</span>
                        </li>
                    </ul>
                    <a href="<?= SITE_URL ?>/auth/register.php?package=<?= PACKAGE_COORDINATOR ?>" class="block w-full text-center mt-8 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-md transition">
                        הצטרף עכשיו
                    </a>
                </div>
            </div>
            
            <!-- Pricing Card 3 -->
            <div class="card bg-white rounded-lg overflow-hidden shadow-lg transition-all hover:shadow-xl">
                <div class="p-6 text-center border-b border-gray-100">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">מנהל בית ספר</h3>
                    <p class="text-gray-500 mb-6">מיועד לארגונים ובתי ספר וירטואליים</p>
                    <div class="flex justify-center items-baseline mb-4">
                        <span class="text-4xl font-bold text-gray-900"><?= CURRENCY_SYMBOL ?>349</span>
                        <span class="text-gray-500 mr-2">/חודש</span>
                    </div>
                    <p class="text-green-600 text-sm mb-4">או <?= CURRENCY_SYMBOL ?>3,490 לשנה (חיסכון של 17%)</p>
                </div>
                <div class="p-6">
                    <ul class="space-y-3">
                        <li class="flex items-center">
                            <i class="ri-check-line text-green-500 ml-2"></i>
                            <span>קורסים בלתי מוגבלים</span>
                        </li>
                        <li class="flex items-center">
                            <i class="ri-check-line text-green-500 ml-2"></i>
                            <span>תלמידים בלתי מוגבלים</span>
                        </li>
                        <li class="flex items-center">
                            <i class="ri-check-line text-green-500 ml-2"></i>
                            <span>200GB אחסון למדיה</span>
                        </li>
                        <li class="flex items-center">
                            <i class="ri-check-line text-green-500 ml-2"></i>
                            <span>דומיין מותאם אישית מלא</span>
                        </li>
                        <li class="flex items-center">
                            <i class="ri-check-line text-green-500 ml-2"></i>
                            <span>ניהול משתמשים מתקדם עם הרשאות</span>
                        </li>
                        <li class="flex items-center">
                            <i class="ri-check-line text-green-500 ml-2"></i>
                            <span>דוחות מפורטים וניתוח נתונים</span>
                        </li>
                        <li class="flex items-center">
                            <i class="ri-check-line text-green-500 ml-2"></i>
                            <span>אפיליאייט (תוכנית שותפים) מתקדם</span>
                        </li>
                        <li class="flex items-center">
                            <i class="ri-check-line text-green-500 ml-2"></i>
                            <span>תמיכת VIP בטלפון ודוא"ל</span>
                        </li>
                        <li class="flex items-center">
                            <i class="ri-check-line text-green-500 ml-2"></i>
                            <span>מותג לבן (white label) מלא</span>
                        </li>
                    </ul>
                    <a href="<?= SITE_URL ?>/auth/register.php?package=<?= PACKAGE_PRINCIPAL ?>" class="block w-full text-center mt-8 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-md transition">
                        הצטרף עכשיו
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">מה אומרים עלינו</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                יוצרי תוכן ומורים שכבר משתמשים בפלטפורמה שלנו
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Testimonial 1 -->
            <div class="card bg-white p-6 text-center">
                <div class="w-20 h-20 mx-auto mb-4 rounded-full overflow-hidden">
                    <img src="<?= getAssetUrl('assets/images/testimonial-1.jpg') ?>" alt="יוסי כהן" class="w-full h-full object-cover">
                </div>
                <div class="flex justify-center mb-4">
                    <i class="ri-star-fill text-yellow-400"></i>
                    <i class="ri-star-fill text-yellow-400"></i>
                    <i class="ri-star-fill text-yellow-400"></i>
                    <i class="ri-star-fill text-yellow-400"></i>
                    <i class="ri-star-fill text-yellow-400"></i>
                </div>
                <p class="text-gray-600 mb-6">
                    "פלטפורמה נהדרת שעזרה לי להפוך את הקורסים שלי למקור הכנסה רציני. הממשק פשוט לשימוש ומאפשר לי להתמקד בתוכן."
                </p>
                <h4 class="text-lg font-semibold text-gray-800">יוסי כהן</h4>
                <p class="text-gray-500">מרצה לשיווק דיגיטלי</p>
            </div>
            
            <!-- Testimonial 2 -->
            <div class="card bg-white p-6 text-center">
                <div class="w-20 h-20 mx-auto mb-4 rounded-full overflow-hidden">
                    <img src="<?= getAssetUrl('assets/images/testimonial-2.jpg') ?>" alt="מיכל לוי" class="w-full h-full object-cover">
                </div>
                <div class="flex justify-center mb-4">
                    <i class="ri-star-fill text-yellow-400"></i>
                    <i class="ri-star-fill text-yellow-400"></i>
                    <i class="ri-star-fill text-yellow-400"></i>
                    <i class="ri-star-fill text-yellow-400"></i>
                    <i class="ri-star-fill text-yellow-400"></i>
                </div>
                <p class="text-gray-600 mb-6">
                    "החלטתי לעבור לקוויק קורס אחרי שניסיתי כמה פלטפורמות אחרות. השילוב של פשטות וכוח, יחד עם המחיר התחרותי, עשה את ההחלטה קלה מאוד."
                </p>
                <h4 class="text-lg font-semibold text-gray-800">מיכל לוי</h4>
                <p class="text-gray-500">מורה ליוגה</p>
            </div>
            
            <!-- Testimonial 3 -->
            <div class="card bg-white p-6 text-center">
                <div class="w-20 h-20 mx-auto mb-4 rounded-full overflow-hidden">
                    <img src="<?= getAssetUrl('assets/images/testimonial-3.jpg') ?>" alt="אלון ברק" class="w-full h-full object-cover">
                </div>
                <div class="flex justify-center mb-4">
                    <i class="ri-star-fill text-yellow-400"></i>
                    <i class="ri-star-fill text-yellow-400"></i>
                    <i class="ri-star-fill text-yellow-400"></i>
                    <i class="ri-star-fill text-yellow-400"></i>
                    <i class="ri-star-half-fill text-yellow-400"></i>
                </div>
                <p class="text-gray-600 mb-6">
                    "כבעל עסק קטן, המערכת מאפשרת לי להרחיב את ההצעה שלי ולהגיע לקהל גדול יותר. דוחות המכירות והאנליטיקה עוזרים לי לשפר כל הזמן."
                </p>
                <h4 class="text-lg font-semibold text-gray-800">אלון ברק</h4>
                <p class="text-gray-500">יועץ עסקי</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-16 bg-blue-600">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">מוכנים להתחיל?</h2>
        <p class="text-xl text-blue-100 max-w-3xl mx-auto mb-8">
            הצטרפו עוד היום וקבלו 14 יום ניסיון חינם, ללא התחייבות וללא צורך בכרטיס אשראי
        </p>
        <a href="<?= SITE_URL ?>/auth/register.php" class="inline-block px-8 py-4 bg-white hover:bg-gray-100 text-blue-600 font-medium rounded-lg shadow-lg transition">
            פתח חשבון חינם
        </a>
        <p class="text-blue-200 mt-4">אין צורך בכרטיס אשראי • בטל בכל עת</p>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>