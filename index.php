<?php
ignore_user_abort(true);
set_time_limit(0);

// سجل البيانات
error_log("✅ البوت بدأ التشغيل...");
require_once 'config.php';
function sendRequest($url, $data = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 400) {
        error_log("❌ خطأ أثناء الاتصال بـ API: HTTP $httpCode");
        return false;
    }

    return $response;
}

// ملف config.php
//define('BASE_URL', 'https://www.okx.com');

# دالة لإرسال إشعار عبر Telegram



function sendTelegramMessage($message) {
    if (!TELEGRAM_ENABLED) {
        return; // إذا كانت الإشعارات معطلة، لا ترسل أي شيء
    }

    $url = TELEGRAM_BASE_URL . "/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
    $data = [
        "chat_id" => TELEGRAM_CHAT_ID,
        "text" => $message
    ];

    // إعداد طلب cURL لإرسال الرسالة
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $startTime = microtime(true);
    $response = curl_exec($ch);
    $endTime = microtime(true);
    curl_close($ch);
    $executionTime = $endTime - $startTime;
    echo "⏳ استغرق الإرسال إلى Telegram: " . round($executionTime, 2) . " ثانية\n";

   // if ($httpCode == 429) {
   //     echo "❌ تجاوز الحد المسموح به! سيتم إعادة المحاولة بعد 5 ثوانٍ...\n";
   //     sleep(5);
   //     return sendTelegramMessage($message);
   // }
    

    return $response;
    sendTelegramMessage("🚀 مرحبًا! هذا اختبار لإشعارات البوت داخل المجموعة.");
}

// تجربة إرسال رسالة


# دالة لإرسال ملف إلى Telegram
//function sendTelegramFile($filePath) {
//    $url = BASE_URL . "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendDocument";
//    $postFields = array(
//        "chat_id" => TELEGRAM_CHAT_ID,
//        "document" => new CURLFile($filePath)
//    );
//    $ch = curl_init();
//    curl_setopt($ch, CURLOPT_URL, $url);
//    curl_setopt($ch, CURLOPT_POST, true);
//    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
//    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//    $response = curl_exec($ch);
//    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//    curl_close($ch);
//    if ($httpCode == 200) {
//        echo "✅ تم إرسال الملف بنجاح: $filePath";
//    } else {
//        echo "❌ خطأ في إرسال الملف.";
//    }
//}
# دالة لإنشاء توقيع الطلب لـ OKX
function createHeaders() {
    if (!defined('API_KEY')) {
        error_log("❌ API_KEY غير معرف!");
        return [];
    }
    if (!defined('API_SECRET')) {
        error_log("❌ API_SECRET غير معرف!");
        return [];
    }
    if (!defined('API_PASSPHRASE')) {
        error_log("❌ API_PASSPHRASE غير معرف!");
        return [];
    }
    if (!defined('BASE_URL')) {
        error_log("❌ BASE_URL غير معرف!");
        return [];
    }

    $timestamp = (string)time(); // الحصول على الطابع الزمني
    $method = "GET";
    $requestPath = "/api/v5/market/tickers";
    $message = $timestamp . $method . $requestPath;

    // إنشاء التوقيع
    $signature = base64_encode(hash_hmac("sha256", $message, API_SECRET, true));

    // إرجاع رؤوس الطلب
    return array(
        "OK-ACCESS-KEY" => API_KEY,
        "OK-ACCESS-SIGN" => $signature,
        "OK-ACCESS-TIMESTAMP" => $timestamp,
        "OK-ACCESS-PASSPHRASE" => API_PASSPHRASE
    );
}
# دالة لجلب جميع الأسواق المتاحة
function getAllMarkets() {
    $url = BASE_URL . "/api/v5/market/tickers";
    $params = http_build_query(array("instType" => "SPOT")); // جلب الأسواق الفورية فقط
    $headers = createHeaders(); // استدعاء دالة إنشاء الرؤوس

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . "?" . $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "OK-ACCESS-KEY: " . $headers["OK-ACCESS-KEY"],
        "OK-ACCESS-SIGN: " . $headers["OK-ACCESS-SIGN"],
        "OK-ACCESS-TIMESTAMP: " . $headers["OK-ACCESS-TIMESTAMP"],
        "OK-ACCESS-PASSPHRASE: " . $headers["OK-ACCESS-PASSPHRASE"]
    ));

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo "❌ خطأ أثناء جلب الأسواق: " . curl_error($ch);
        curl_close($ch);
        return [];
    }

    curl_close($ch);

    $data = json_decode($response, true);
    if (isset($data["data"])) {
        $marketSymbols = array_map(function ($item) {
            return $item["instId"]; // استخراج رموز العملات
        }, $data["data"]);

        echo "✅ تم جلب " . count($marketSymbols) . " سوقًا.";
        return $marketSymbols;
    } else {
        echo "❌ خطأ في البيانات المستلمة.";
        return [];
    }
}
# دالة لمراقبة الصفقات الكبيرة في السوق
//function checkMarket($symbols) {
//    foreach ($symbols as $symbol) {
//        $url = BASE_URL . "/api/v5/market/trades";
//        $params = http_build_query(array("instId" => $symbol));
//        $headers = createHeaders();
//
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, $url . "?" . $params);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//            "OK-ACCESS-KEY: " . $headers["OK-ACCESS-KEY"],
//            "OK-ACCESS-SIGN: " . $headers["OK-ACCESS-SIGN"],
//            "OK-ACCESS-TIMESTAMP: " . $headers["OK-ACCESS-TIMESTAMP"],
//            "OK-ACCESS-PASSPHRASE: " . $headers["OK-ACCESS-PASSPHRASE"]
//        ));
//
//        $response = curl_exec($ch);
//        if (curl_errno($ch)) {
//            echo " خطأ أثناء الاتصال بـ API للسوق $symbol: " . curl_error($ch);
//            curl_close($ch);
//            continue;
//        }
//
//        curl_close($ch);
//
//        $data = json_decode($response, true)["data"] ?? [];
//        foreach ($data as $trade) {
//            $price = floatval($trade["px"]);
//            $quantity = floatval($trade["sz"]);
//            $totalValue = $price * $quantity;
//            $side = $trade["side"] === "buy" ? "شراء" : "بيع";
//
//            if ($totalValue >= 1000) {
//                $message = "📊 صفقة $side على $symbol:\nالقيمة: " . number_format($totalValue, 2) . " دولار";
//                echo $message;
//                sendTelegramMessage($message);
//            }
//        }
//    }
//}
function checkMarket($symbols) {
    foreach ($symbols as $symbol) {
        // جلب بيانات التداول
        $url = BASE_URL . "/api/v5/market/trades";
        $params = http_build_query(["instId" => $symbol]);
        $headers = createHeaders();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . "?" . $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "OK-ACCESS-KEY: " . $headers["OK-ACCESS-KEY"],
            "OK-ACCESS-SIGN: " . $headers["OK-ACCESS-SIGN"],
            "OK-ACCESS-TIMESTAMP: " . $headers["OK-ACCESS-TIMESTAMP"],
            "OK-ACCESS-PASSPHRASE: " . $headers["OK-ACCESS-PASSPHRASE"]
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true)["data"] ?? [];
        foreach ($data as $trade) {
            $price = floatval($trade["px"]);
            $quantity = floatval($trade["sz"]);
            $totalValue = $price * $quantity;
            $side = $trade["side"] === "buy" ? "شراء" : "بيع";

            if ($totalValue >= 1000) { // إذا كانت الصفقة كبيرة، أرسل إشعارًا
                $message = "📊 صفقة $side على $symbol:\nالقيمة: " . number_format($totalValue, 2) . " دولار";
                sendTelegramMessage($message);
            }
        }
    }
}
# دالة لجمع بيانات السوق وتحليلها للأسبوع الأخير
function collectWeeklyMarketData($symbols) {
    $data = [];
    $oneWeekAgo = (new DateTime())->modify('-7 days')->getTimestamp();

    foreach ($symbols as $symbol) {
        $url = BASE_URL . "/api/v5/market/trades";
        $params = http_build_query(["instId" => $symbol]);
        $headers = createHeaders();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . "?" . $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "OK-ACCESS-KEY: " . $headers["OK-ACCESS-KEY"],
            "OK-ACCESS-SIGN: " . $headers["OK-ACCESS-SIGN"],
            "OK-ACCESS-TIMESTAMP: " . $headers["OK-ACCESS-TIMESTAMP"],
            "OK-ACCESS-PASSPHRASE: " . $headers["OK-ACCESS-PASSPHRASE"]
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo "❌ خطأ أثناء جمع البيانات للسوق $symbol: " . curl_error($ch);
            curl_close($ch);
            continue;
        }

        curl_close($ch);

        $trades = json_decode($response, true)["data"] ?? [];
        $buyCount = 0;
        $sellCount = 0;
        $totalBuyPrice = 0;
        $totalSellPrice = 0;

        foreach ($trades as $trade) {
            $tradeTime = (int)($trade["ts"] / 1000);
            if ($tradeTime >= $oneWeekAgo) {
                $side = $trade["side"];
                $price = floatval($trade["px"]);

                if ($side === "buy") {
                    $totalBuyPrice += $price;
                    $buyCount++;
                } elseif ($side === "sell") {
                    $totalSellPrice += $price;
                    $sellCount++;
                }
            }
        }

        $avgBuyPrice = $buyCount > 0 ? $totalBuyPrice / $buyCount : 0;
        $avgSellPrice = $sellCount > 0 ? $totalSellPrice / $sellCount : 0;

        $data[] = [
            "رمز العملة" => $symbol,
            "عدد صفقات الشراء" => $buyCount,
            "عدد صفقات البيع" => $sellCount,
            "متوسط سعر الشراء" => round($avgBuyPrice, 2),
            "متوسط سعر البيع" => round($avgSellPrice, 2)
        ];
    }

    return $data;
}

# دالة لحفظ البيانات في ملف Excel
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function saveToExcel($data) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // إدراج البيانات في ورقة Excel
    $row = 1;
    foreach ($data as $entry) {
        $col = 'A';
        foreach ($entry as $value) {
            $sheet->setCellValue($col . $row, $value);
            $col++;
        }
        $row++;
    }

    // إنشاء اسم الملف وحفظه
    $fileName = "weekly_market_data_" . date('Y-m-d') . ".xlsx";
    $writer = new Xlsx($spreadsheet);
    $writer->save($fileName);

    echo "✅ تم حفظ البيانات في {$fileName}";
    return $fileName;
}

# دالة لجمع البيانات وإرسالها
function collectAndSendWeeklyReport() {
    echo "📊 يتم الآن جمع بيانات السوق للأسبوع الأخير...\n";
    $symbols = getAllMarkets(); // جلب جميع الأسواق

    if (!empty($symbols)) {
        $weeklyData = collectWeeklyMarketData($symbols); // جمع بيانات السوق
        $filePath = saveToExcel($weeklyData); // حفظ البيانات في ملف Excel
        sendTelegramFile($filePath); // إرسال الملف عبر Telegram
    } else {
        echo "❌ لم يتم العثور على أسواق لتحليلها.\n";
    }
}


echo "✅ بدأ تشغيل البوت...\n";

try {
    while (true) {
        // جلب جميع الأسواق
        $markets = getAllMarkets();
        if (!empty($markets)) {
            checkMarket($markets); // مراقبة السوق
        }

        // تنفيذ المهام المجدولة
        if (date('H:i') == '18:00' && date('l') == 'Sunday') {
            collectAndSendWeeklyReport();
        }

        sleep(30); // الانتظار 30 ثانية قبل التحقق مرة أخرى
    }
} catch (Exception $e) {
    echo "🔴 تم إيقاف البوت بسبب خطأ: " . $e->getMessage() . "\n";
}

while (true) {
    checkMarket();
    sleep(2); // انتظار دقيقة قبل التكرار التالي
}
