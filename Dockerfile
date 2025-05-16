# =======================
# 📁 Dockerfile (جذر المشروع)
# =======================

# استخدم صورة رسمية من Node.js
FROM node:18

# إعداد مجلد العمل داخل الحاوية
WORKDIR /app

# نسخ package.json و package-lock.json أولاً لتثبيت التبعيات
COPY package*.json ./

# تثبيت التبعيات
RUN npm install

# نسخ باقي ملفات المشروع
COPY . .

# تحديد المنفذ الذي سيعمل عليه البوت (إن وُجد)
EXPOSE 3000

# الأمر الذي يتم تشغيله عند بدء الحاوية
CMD ["node", "index.js"]
