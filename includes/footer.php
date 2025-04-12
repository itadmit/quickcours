</main>
    
    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="container mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Logo and info -->
                <div class="col-span-1 md:col-span-1">
                    <a href="<?= SITE_URL ?>" class="text-xl font-bold text-blue-600 flex items-center">
                        <i class="ri-book-open-line mr-2"></i>
                        <?= SITE_NAME ?>
                    </a>
                    <p class="mt-3 text-gray-600 text-sm">
                        פלטפורמה מתקדמת למכירת והפצת קורסים אונליין בקלות ובמהירות.
                    </p>
                    <div class="mt-4 flex space-x-3">
                        <a href="#" class="text-gray-400 hover:text-blue-500">
                            <i class="ri-facebook-fill text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-blue-500 mr-3">
                            <i class="ri-twitter-fill text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-blue-500 mr-3">
                            <i class="ri-instagram-fill text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-blue-500 mr-3">
                            <i class="ri-linkedin-fill text-xl"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Links 1 -->
                <div class="col-span-1">
                    <h3 class="text-gray-900 font-medium mb-4">ניווט מהיר</h3>
                    <ul class="space-y-2">
                        <li><a href="<?= SITE_URL ?>" class="text-gray-600 hover:text-blue-600">דף הבית</a></li>
                        <li><a href="<?= SITE_URL ?>/courses" class="text-gray-600 hover:text-blue-600">חנות קורסים</a></li>
                        <li><a href="<?= SITE_URL ?>/pricing.php" class="text-gray-600 hover:text-blue-600">תוכניות מחירים</a></li>
                        <li><a href="<?= SITE_URL ?>/about.php" class="text-gray-600 hover:text-blue-600">אודות</a></li>
                        <li><a href="<?= SITE_URL ?>/contact.php" class="text-gray-600 hover:text-blue-600">צור קשר</a></li>
                    </ul>
                </div>
                
                <!-- Links 2 -->
                <div class="col-span-1">
                    <h3 class="text-gray-900 font-medium mb-4">מידע משפטי</h3>
                    <ul class="space-y-2">
                        <li><a href="<?= SITE_URL ?>/terms.php" class="text-gray-600 hover:text-blue-600">תנאי שימוש</a></li>
                        <li><a href="<?= SITE_URL ?>/privacy.php" class="text-gray-600 hover:text-blue-600">מדיניות פרטיות</a></li>
                        <li><a href="<?= SITE_URL ?>/refund.php" class="text-gray-600 hover:text-blue-600">מדיניות החזרים</a></li>
                        <li><a href="<?= SITE_URL ?>/cookies.php" class="text-gray-600 hover:text-blue-600">מדיניות עוגיות</a></li>
                    </ul>
                </div>
                
                <!-- Contact -->
                <div class="col-span-1">
                    <h3 class="text-gray-900 font-medium mb-4">צור קשר</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <i class="ri-mail-line text-blue-600 mt-1 ml-2"></i>
                            <a href="mailto:support@quickcourse.com" class="text-gray-600 hover:text-blue-600">support@quickcourse.com</a>
                        </li>
                        <li class="flex items-start">
                            <i class="ri-phone-line text-blue-600 mt-1 ml-2"></i>
                            <span class="text-gray-600">03-1234567</span>
                        </li>
                        <li class="flex items-start">
                            <i class="ri-map-pin-line text-blue-600 mt-1 ml-2"></i>
                            <span class="text-gray-600">רחוב הברוש 15, תל אביב</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-200 mt-8 pt-6 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-500 text-sm mb-4 md:mb-0">
                    &copy; <?= date('Y') ?> <?= SITE_NAME ?>. כל הזכויות שמורות.
                </p>
                <div class="flex items-center space-x-4">
                    <img src="<?= SITE_URL ?>/assets/images/payment-visa.svg" alt="Visa" class="h-8 ml-2">
                    <img src="<?= SITE_URL ?>/assets/images/payment-mastercard.svg" alt="Mastercard" class="h-8 ml-2">
                    <img src="<?= SITE_URL ?>/assets/images/payment-paypal.svg" alt="PayPal" class="h-8">
                </div>
            </div>
        </div>
    </footer>
    
    <script>
        // Common JS functions
        
        // Tooltip initialization
        $('[data-tooltip]').hover(
            function() {
                let tooltip = $(this).attr('data-tooltip');
                $('<div class="tooltip"></div>')
                    .text(tooltip)
                    .appendTo('body')
                    .css({
                        top: $(this).offset().top + $(this).outerHeight(),
                        left: $(this).offset().left + ($(this).outerWidth() / 2) - 100
                    })
                    .fadeIn('fast');
            },
            function() {
                $('.tooltip').remove();
            }
        );
    </script>
</body>
</html>