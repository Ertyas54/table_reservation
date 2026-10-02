# Table Reservation

Веб-приложение для бронирования столиков в ресторанах.

## Быстрый старт

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run dev
php artisan serve
```

## Требования

```bash
php -v        
composer -V
node -v
npm -v
```

### Обязательные PHP-расширения

```ini
extension=pdo_sqlite
extension=sqlite3
```

## Тестовые учётки

| Роль | Email | Пароль |
|---|---|---|
| Админ | admin@example.com | password |
| Клиент | petr@example.com | password |

## Проверка работы

http://127.0.0.1:8000
