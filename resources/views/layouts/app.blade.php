<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Бронирование столиков</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    @livewireStyles
</head>
<body>
    <header class="site-header">
        <div class="site-header__inner">
            <h1 class="site-header__title">Сеть ресторанов</h1>
            <nav class="site-header__nav">
                <a href="/restaurants">Главная</a>
            </nav>
        </div>
    </header>

    <main class="container">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
