#!/bin/bash

# Устанавливаем зависимости, если их ещё нет
composer install --no-interaction --prefer-dist --optimize-autoloader

# Генерируем ключ (только если он пустой)
if [ -z "$APP_KEY" ]; then
  php artisan key:generate
fi

# Запускаем Octane (Swoole)
php artisan octane:start --server="swoole" --host="0.0.0.0" --port=8000 --workers=1 --task-workers=1
