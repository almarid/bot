<?php

require_once 'config.php';

// استلام التحديث من Telegram
$content = file_get_contents("php://input");
$update = json_decode($content, true);

// استخراج البيانات من التحديث
$chat_id    = $update['message']['chat']['id'] ?? null;
$user_id    = $update['message']['from']['id'] ?? null;
$first_name = $update['message']['from']['first_name'] ?? '';
$text       = $update['message']['text'] ?? '';
$chat_type  = $update['message']['chat']['type'] ?? '';

// فقط إذا كانت البيانات مكتملة
if ($chat_id && $text) {
    // تحقق هل المستخدم هو الأدمن
    if ($chat_type === 'private' && $user_id == ADMIN_ID) {
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
    } else {
        sendMessage($chat_id, "🚫 هذا البوت مخصص فقط للمشرف.");
    }
}
?>
