<?php
require_once 'config.php';

function getTimestamp() {
    return gmdate("Y-m-d\TH:i:s.000\Z");
}

function createSignature($timestamp, $method, $requestPath, $body = '') {
    $prehash = $timestamp . strtoupper($method) . $requestPath . $body;
    return base64_encode(hash_hmac('sha256', $prehash, API_SECRET, true));
}

function sendOkxRequest($method, $path, $body = '') {
    $timestamp = getTimestamp();
    $signature = createSignature($timestamp, $method, $path, $body);

    $headers = [
        'OK-ACCESS-KEY: ' . API_KEY,
        'OK-ACCESS-SIGN: ' . $signature,
        'OK-ACCESS-TIMESTAMP: ' . $timestamp,
        'OK-ACCESS-PASSPHRASE: ' . API_PASSPHRASE,
        'Content-Type: application/json'
    ];

    $url = BASE_URL . $path;

    $ch = curl_init();

    if ($method === 'GET' && !empty($body)) {
        $url .= '?' . $body;
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        logMessage('cURL error: ' . curl_error($ch));
        curl_close($ch);
        return false;
    }

    curl_close($ch);
    return json_decode($response, true);
}

function getAllMarkets() {
    $path = '/api/v5/public/instruments?instType=SPOT';
    $response = sendOkxRequest('GET', $path);
    if (!$response || !$response['data']) {
        logMessage("Failed to get markets");
        return [];
    }

    $symbols = [];
    foreach ($response['data'] as $market) {
        $symbols[] = $market['instId'];
    }
    return $symbols;
}

function getTradesForMarket($symbol) {
    $path = "/api/v5/market/trades?instId=" . urlencode($symbol);
    $response = sendOkxRequest('GET', $path);
    if (!$response || !$response['data']) {
        logMessage("Failed to get trades for $symbol");
        return [];
    }

    $trades = [];
    foreach ($response['data'] as $trade) {
        $trades[] = [
            'price' => floatval($trade['px']),
            'quantity' => floatval($trade['sz']),
            'side' => $trade['side']  // buy or sell
        ];
    }
    return $trades;
}
