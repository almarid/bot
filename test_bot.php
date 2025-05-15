<?php
require_once 'config.php';

// إرسال رسالة اختبارية إلى الأدمن
$response = sendMessage(ADMIN_ID, "✅ تم إرسال الرسالة بنجاح من ملف test_bot.php");

if ($response && $response['ok']) {
    echo "✅ تم الإرسال بنجاح.";
} else {
    echo "❌ فشل في الإرسال.";
    print_r($response);
}
?>