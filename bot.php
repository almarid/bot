<?php
require_once 'config.php';
require_once 'okx_api.php';

define('STATE_FILE', __DIR__ . '/state.json');

function getState() {
    if (!file_exists(STATE_FILE)) return ['running' => false];
    $data = file_get_contents(STATE_FILE);
    $json = json_decode($data, true);
    if (!$json) return ['running' => false];
    return $json;
}

function sendTelegramMessage($message) {
    if (!TELEGRAM_ENABLED) return;

    $url = TELEGRAM_BASE_URL . "/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
    $data = [
        "chat_id" => TELEGRAM_CHAT_ID,
        "text" => $message
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

function checkMarketAndSend() {
    $state = getState();
    if (!$state['running']) {
        echo "🚫 الإرسال معطل. لن يتم إرسال الصفقات.\n";
        return;
    }

    $markets = getAllMarkets();

    if (empty($markets)) {
        echo "❌ لم يتم جلب الأسواق.\n";
        return;
    }

    foreach ($markets as $symbol) {
        $trades = getTradesForMarket($symbol);

        foreach ($trades as $trade) {
            $totalValue = $trade['price'] * $trade['quantity'];
            if ($totalValue >= 1000) { // فقط الصفقات الكبيرة
                $side = $trade['side'] == 'buy' ? 'شراء' : 'بيع';
                $msg = "📊 صفقة $side على $symbol:\nالقيمة: " . number_format($totalValue, 2) . " دولار";
                sendTelegramMessage($msg);
            }
        }
    }
}

while (true) {
    checkMarketAndSend();
    sleep(30);
}
