<?php

// معلومات الاتصال بالبوت
define('BOT_TOKEN', '7623523587:AAEkmroKyKMZTBolkUolHifxSu9adtln5Rw');
define('API_URL', 'https://api.telegram.org/bot' . BOT_TOKEN . '/');
define('ADMIN_ID', 7553454385); // ← معرف الأدمن تم إدخاله بنجاح
function sendMessage($chat_id, $text, $params = []) {
    $data = array_merge([
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ], $params);

    $response = file_get_contents(API_URL . 'sendMessage?' . http_build_query($data));
    return json_decode($response, true);
}
?>
