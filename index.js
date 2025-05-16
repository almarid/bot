import dotenv from 'dotenv';
dotenv.config();

import fetch from 'node-fetch';
import { Telegraf } from 'telegraf';
import crypto from 'crypto';
import fs from 'fs';

const bot = new Telegraf(process.env.TELEGRAM_BOT_TOKEN);

const STATE_FILE = './state.json';

// قراءة حالة التشغيل من الملف
function getState() {
  try {
    const data = fs.readFileSync(STATE_FILE, 'utf8');
    const json = JSON.parse(data);
    return json.running;
  } catch {
    return false; // إذا الملف غير موجود أو خطأ، نرجع false
  }
}

// حفظ حالة التشغيل في الملف
function saveState(runningState) {
  try {
    fs.writeFileSync(STATE_FILE, JSON.stringify({ running: runningState }));
  } catch (e) {
    console.error('خطأ في حفظ الحالة:', e);
  }
}

// نبدأ بالحالة المحفوظة أو false
let running = getState();

// دالة لإنشاء توقيع OKX حسب الوثائق
function createOkxSignature(timestamp, method, requestPath, body, secret) {
  const prehash = timestamp + method.toUpperCase() + requestPath + (body || '');
  const hmac = crypto.createHmac('sha256', secret);
  return hmac.update(prehash).digest('base64');
}

// جلب الأسواق من OKX
async function getAllMarkets() {
  const url = `${process.env.OKX_BASE_URL}/api/v5/market/tickers?instType=SPOT`;
  const res = await fetch(url);
  const data = await res.json();
  if (data.code === '0') {
    return data.data.map(item => item.instId);
  }
  return [];
}

// جلب الصفقات لكل سوق
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

// إرسال رسالة لتليجرام
async function sendTelegramMessage(text) {
  await bot.telegram.sendMessage(process.env.TELEGRAM_CHAT_ID, text);
}

// مراقبة السوق وإرسال صفقات كبيرة
async function checkMarketAndSend() {
  if (!running) return;

  const markets = await getAllMarkets();
  for (const symbol of markets) {
    const trades = await getTradesForMarket(symbol);
    for (const trade of trades) {
      const totalValue = trade.price * trade.quantity;
      if (totalValue >= 1000) { // شرط الصفقة الكبيرة
        const sideText = trade.side === 'buy' ? 'شراء' : 'بيع';
        const msg = `📊 صفقة ${sideText} على ${symbol}:\nالقيمة: ${totalValue.toFixed(2)} دولار`;
        await sendTelegramMessage(msg);
      }
    }
  }
}

// أوامر بوت تليجرام
bot.start((ctx) => ctx.reply('أهلاً! أرسل "s" لتفعيل الإرسال أو "p" لإيقافه.'));

bot.on('text', (ctx) => {
  const userId = ctx.message.from.id;
  if (userId != Number(process.env.AUTHORIZED_USER_ID)) {
    ctx.reply('🚫 غير مصرح لك باستخدام هذا البوت.');
    return;
  }

  const text = ctx.message.text.toLowerCase().trim();

  if (text === 's') {
    running = true;
    saveState(running);
    ctx.reply('✅ تم تفعيل الإرسال.');
  } else if (text === 'p') {
    running = false;
    saveState(running);
    ctx.reply('🛑 تم إيقاف الإرسال.');
  } else {
    ctx.reply("❓ أمر غير معروف، الرجاء إرسال 's' لتشغيل الإرسال أو 'p' لإيقافه.");
  }
});

// تشغيل البوت
bot.launch();

console.log('🚀 البوت شغال...');

// فحص السوق كل 30 ثانية
setInterval(() => {
  checkMarketAndSend().catch(console.error);
}, 30000);
