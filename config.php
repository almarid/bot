<?php
// إعدادات Telegram
define('TELEGRAM_BOT_TOKEN', '7623523587:AAEkmroKyKMZTBolkUolHifxSu9adtln5Rw');
define('TELEGRAM_CHAT_ID', '7553454385'); // معرف المجموعة أو المستخدم
define('TELEGRAM_BASE_URL', 'https://api.telegram.org');

// معرف المستخدم المصرح فقط
define('AUTHORIZED_USER_ID', 7553454385);

// إعدادات OKX API (ضع بياناتك الحقيقية هنا)
define('API_KEY', '9a93ca2d-708c-4a20-8e19-4acbd982d093'); 
define('API_SECRET', 'B423E7CBDF24D39EC9F69C63EF77B1A0'); 
define('API_PASSPHRASE', '708768325148693385'); 
define('BASE_URL', 'https://www.okx.com');

// إعدادات عامة
define('TELEGRAM_ENABLED', true);
define('LOG_ENABLED', true);

// دالة لتسجيل الأخطاء
function logMessage($msg) {
    if (LOG_ENABLED) {
        error_log($msg);
    }
}
