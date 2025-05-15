<?php

define('TELEGRAM_BOT_TOKEN', '7623523587:AAEkmroKyKMZTBolkUolHifxSu9adtln5Rw'); // ضع رمز البوت هنا
define('TELEGRAM_CHAT_ID', '7553454385'); // ضع معرف المجموعة أو المستخدم هنا
define('TELEGRAM_BASE_URL', 'https://api.telegram.org');

// ✅ إعدادات الاتصال بـ OKX API (إذا كنت تستخدمها)
define('API_KEY', '9a93ca2d-708c-4a20-8e19-4acbd982d093'); 
define('API_SECRET', 'B423E7CBDF24D39EC9F69C63EF77B1A0'); 
define('API_PASSPHRASE', '708768325148693385'); 
define('BASE_URL', 'https://www.okx.com');

// ✅ إعدادات تشغيل البوت
define('TELEGRAM_ENABLED', true); // تفعيل أو تعطيل إرسال الرسائل إلى Telegram
define('LOG_ENABLED', true); // تفعيل أو تعطيل تسجيل الأخطاء

// ✅ دالة لتسجيل الأخطاء إذا كان LOG_ENABLED مفعل
function logMessage($message) {
    if (LOG_ENABLED) {
        error_log($message);
    }
}
?>
