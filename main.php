<?php
require_once 'config.php';

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update || !isset($update['message'])) {
    exit("No message received");
}

$message = $update['message'];
$chat_id = $message['chat']['id'];
$text = $message['text'] ?? '';
$user_id = $message['from']['id'];
$first_name = $message['from']['first_name'] ?? '';

// التحقق من صلاحيات المستخدم
if ($user_id != ADMIN_ID) {
    sendMessage($chat_id, "🚫 هذا البوت مخصص فقط للمشرف.");
    exit;
}

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