<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$pageTitle|default:'Блог'}</title>
    {block name=styles}{/block}
</head>
<body>
<header>
    <nav>
        <a href="/">Главная</a>
    </nav>
</header>
<main>
    {block name=content}{/block}
</main>
<footer>
    <p>&copy; Блог</p>
</footer>
</body>
</html>