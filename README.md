# Table Reservation

Веб-приложение для бронирования столиков в ресторанах.

## Быстрый старт (Docker)

```
docker compose up
```

## Ручной запуск (без Docker)

### Требования

`php -v`
`composer -V`
`node -v` |
`npm -v` |

### Обязательные PHP-расширения

```ini
extension=pdo_sqlite
extension=sqlite3
```
### Запуск (два терминала)

**Терминал 1 - Backend:**
```bash
cd backend
php artisan serve
# → http://127.0.0.1:8000
```

**Терминал 2 - Frontend:**
```bash
cd frontend
npm run dev
# → http://localhost:5173
```

## Проверка работы
Открой http://localhost:5173 - увидишь React-приложение, которое общается с Laravel API через прокси `/api`.




