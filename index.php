<?php
require_once 'config.php';

define('STATE_FILE', __DIR__ . '/state.json');

function getState() {
    if (!file_exists(STATE_FILE)) return ['running' => false];
    $data = file_get_contents(STATE_FILE);
    $json = json_decode($data, true);
    if (!$json) return ['running' => false];
    return $json;
}

function saveState($state) {
    file_put_contents(STATE_FILE, json_encode($state));
}

function sendTelegramMessage($chat_id, $text) {
    if (!TELEGRAM_ENABLED) return;
    $url = TELEGRAM_BASE_URL . "/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
    $data = ['chat_id' => $chat_id, 'text' => $text];
    @file_get_contents($url . "?" . http_build_query($data));
}

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update || !isset($update['message'])) exit;

$message = $update['message'];
$chat_id = $message['chat']['id'];
$text = strtolower(trim($message['text'] ?? ''));
$user_id = $message['from']['id'];

if ($user_id != AUTHORIZED_USER_ID) {
    sendTelegramMessage($chat_id, "🚫 غير مصرح لك باستخدام هذا البوت.");
    exit;
}

$state = getState();

if ($text === 's') {
    $state['running'] = true;
    saveState($state);
    sendTelegramMessage($chat_id, "✅ تم تفعيل الإرسال.");
} elseif ($text === 'p') {
    $state['running'] = false;
    saveState($state);
    sendTelegramMessage($chat_id, "🛑 تم إيقاف الإرسال.");
} else {
    sendTelegramMessage($chat_id, "❓ أمر غير معروف، الرجاء إرسال 's' لتشغيل الإرسال أو 'p' لإيقافه.");
}
