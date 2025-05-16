import dotenv from 'dotenv';
dotenv.config();

import fetch from 'node-fetch';
import { Telegraf } from 'telegraf';
import crypto from 'crypto';

const bot = new Telegraf(process.env.TELEGRAM_BOT_TOKEN);

let running = false;

// دالة لإنشاء توقيع OKX حسب الوثائق
function createOkxSignature(timestamp, method, requestPath, body, secret) {
  const prehash = timestamp + method.toUpperCase() + requestPath + (body || '');
  const hmac = crypto.createHmac('sha256', secret);
  return hmac.update(prehash).digest('base64');
}

// دالة لطلب الأسواق من OKX (API حقيقية)
async function getAllMarkets() {
  const url = `${process.env.OKX_BASE_URL}/api/v5/market/tickers?instType=SPOT`;
  const res = await fetch(url);
  const data = await res.json();
  if (data.code === '0') {
    return data.data.map(item => item.instId);
  }
  return [];
}

// دالة للحصول على الصفقات السوقية (مبسطة، ممكن تحتاج تعديل حسب API OKX)
async function getTradesForMarket(symbol) {
  const url = `${process.env.OKX_BASE_URL}/api/v5/market/trades?instId=${symbol}`;
  const res = await fetch(url);
  const data = await res.json();
  if (data.code === '0') {
    return data.data.map(trade => ({
      price: parseFloat(trade.px),
      quantity: parseFloat(trade.sz),
      side: trade.side
    }));
  }
  return [];
}

// دالة لإرسال رسالة تليجرام
async function sendTelegramMessage(text) {
  await bot.telegram.sendMessage(process.env.TELEGRAM_CHAT_ID, text);
}

// وظيفة مراقبة السوق وإرسال صفقات كبيرة
async function checkMarketAndSend() {
  if (!running) return;

  const markets = await getAllMarkets();
  for (const symbol of markets) {
    const trades = await getTradesForMarket(symbol);
    for (const trade of trades) {
      const totalValue = trade.price * trade.quantity;
      if (totalValue >= 200) { // شرط الصفقة الكبيرة
        const sideText = trade.side === 'buy' ? 'شراء' : 'بيع';
        const msg = 
          `📊 صفقة ${sideText} على ${symbol}:\n` +
          `الكمية: ${trade.quantity}\n` +
          `السعر: ${trade.price.toFixed(8)}\n` +
          `القيمة: ${totalValue.toFixed(2)} دولار`;
        await sendTelegramMessage(msg);
      }
    }
  }
}

// أمر /start (اختياري)
bot.start((ctx) => ctx.reply('أهلاً! أرسل "s" لتفعيل الإرسال أو "p" لإيقافه.'));

// استقبال رسائل المستخدم وتشغيل / إيقاف الإرسال
bot.on('text', (ctx) => {
  const userId = ctx.message.from.id;
  if (userId != Number(process.env.AUTHORIZED_USER_ID)) {
    ctx.reply('🚫 غير مصرح لك باستخدام هذا البوت.');
    return;
  }

  const text = ctx.message.text.toLowerCase().trim();

  if (text === 's') {
    running = true;
    ctx.reply('✅ تم تفعيل الإرسال.');
  } else if (text === 'p') {
    running = false;
    ctx.reply('🛑 تم إيقاف الإرسال.');
  } else {
    ctx.reply("❓ أمر غير معروف، الرجاء إرسال 's' لتشغيل الإرسال أو 'p' لإيقافه.");
  }
});

// تشغيل البوت
bot.launch();

console.log('🚀 البوت شغال...');

setInterval(() => {
  checkMarketAndSend().catch(console.error);
}, 30000);  // كل 30 ثانية يفحص الأسواق ويرسل الصفقات
