<?php
require_once 'config.php';
// استقبال التحديث من Telegram
$update = file_get_contents('php://input');
$update = json_decode($update, true);
// التأكد من وجود بيانات أساسية
if (!$update || !isset($update['message'])) {
    echo "No message received";
    exit;
}
// استخراج البيانات
$message   = $update['message'];
$text      = $message['text'] ?? '';
$chat_id   = $message['chat']['id'] ?? '';
$user_id   = $message['from']['id'] ?? '';
$first_name = $message['from']['first_name'] ?? '';
$chat_type = $message['chat']['type'] ?? '';
// ✅ تحقق إذا كان المستخدم هو الأدمن فقط
if ($user_id != ADMIN_ID) {
    sendMessage($chat_id, "🚫 هذا البوت مخصص فقط للمشرف.");
    exit;
}
// ✅ أوامر البوت للأدمن فقط
switch ($text) {
    case '/start':
        sendMessage($chat_id, "👋 مرحبًا بك $first_name!\nاستخدم الأوامر للتحكم بالبوت.");
        break;

    case '/id':
        sendMessage($chat_id, "🆔 معرفك: <b>$user_id</b>");
        break;

    default:
        sendMessage($chat_id, "❓ أمر غير معروف: <code>$text</code>");
        break;
}
?>
