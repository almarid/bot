FROM php:8.1-cli

WORKDIR /app

COPY . .

CMD ["php", "bot.php"]


