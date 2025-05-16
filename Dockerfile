# استخدم صورة PHP مع Apache (تدعم curl و json)
FROM php:8.1-apache

# تثبيت curl و الأدوات اللازمة
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    unzip \
    && docker-php-ext-install curl \
    && docker-php-ext-enable curl

# نسخ ملفات المشروع إلى مجلد الويب في الحاوية
WORKDIR /var/www/html
COPY . .

# اضبط أذونات الملفات إذا لزم الأمر
RUN chown -R www-data:www-data /var/www/html

# ارفع البورت 80
EXPOSE 80

# تشغيل Apache في المقدمة (foreground)
CMD ["apache2-foreground"]
