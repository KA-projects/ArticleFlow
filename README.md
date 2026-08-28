# Blog

Тестовый блог на чистом PHP (без фреймворков) + Smarty + MySQL. Всё окружение — Docker.

## Стек

- PHP 8.3 (php-fpm), чистый код, без фреймворков
- Smarty 4 (шаблонизатор)
- MySQL 8 (PDO, только prepared statements)
- Nginx (clean URLs → front controller)
- SCSS → CSS (node-контейнер)
- Docker Compose

## Быстрая развёртка

```bash
make setup
```

Команда копирует `.env.example` в `.env` (если его нет), собирает и поднимает контейнеры, устанавливает зависимости, собирает SCSS и засеивает данные.

Ручной эквивалент:

```bash
cp .env.example .env
docker compose up -d --build
docker compose run --rm app composer install
docker compose run --rm --entrypoint "" sass npx -y sass assets/scss:public/assets/css
docker compose run --rm app php bin/seed.php
```

После этого блог доступен на `http://localhost:8080`.

## Команды (Makefile)

| Команда | Действие |
|---|---|
| `make setup` | Полная первичная развёртка (см. выше) |
| `make up` | Собрать и поднять контейнеры + зависимости + SCSS |
| `make seed` | Сидинг данных (идемпотентный, чистит таблицы перед вставкой) |
| `make sass` | Одноразовая сборка SCSS |
| `make logs` | Логи приложения |
| `make ps` | Статус контейнеров |
| `make down` | Остановить контейнеры |

## Структура проекта

```
├── docker-compose.yml
├── Dockerfile                  # php-fpm 8.3 + pdo_mysql + composer
├── docker/
│   ├── nginx/default.conf      # vhost, rewrite → index.php
│   └── node/compile.sh         # сборка SCSS (--watch)
├── db/init.sql                 # схема БД
├── public/
│   ├── index.php               # front controller
│   └── assets/css/             # скомпилированный CSS
├── src/
│   ├── Router.php
│   ├── Database.php            # PDO singleton
│   ├── Dto/                    # Category, Article
│   ├── Contracts/              # интерфейсы репозиториев
│   ├── Controller/             # Home, Category, Article
│   ├── Repository/             # CategoryRepository, ArticleRepository
│   └── Service/PaginationService.php
├── templates/                  # Smarty (layout.tpl, home.tpl, category.tpl, article.tpl, partials/*)
├── bin/seed.php                # сидер
├── assets/scss/                # main.scss, _variables.scss
└── config/config.php           # настройки БД из переменных окружения
```

## Маршруты

| URL | Контроллер | Действие |
|---|---|---|
| `/` | HomeController | категории (только с постами) + 3 последних поста + «Все статьи» |
| `/category/{slug}` | CategoryController | название, описание, список статей, сортировка, пагинация |
| `/article/{slug}` | ArticleController | вся инфо о статье, +1 просмотр, 3 похожих (рандомных) |

Параметры категории: `?sort=views|date`, `?page=N`. По умолчанию `sort=date`, `page=1`.

## Переменные окружения

Скопируйте `.env.example` в `.env` и при необходимости измените:

- `MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD`, `MYSQL_ROOT_PASSWORD` — параметры MySQL
- `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` — подключение приложения к БД
- `APP_DEBUG` — `true` показывает трейс исключений, `false` — аккуратные 404/500 (обязательно `false` на проде)
- `SEED_CATEGORIES`, `SEED_ARTICLES_MIN`, `SEED_ARTICLES_MAX` — параметры сидера

Параметры сидера можно задавать и CLI-аргументами (имеют приоритет):

```bash
docker compose run --rm app php bin/seed.php --categories=50 --articles-min=20 --articles-max=40
```

## Проверка работы (локально)

1. `make setup`
2. Открыть `http://localhost:8080` — главная; проверить `/category/{slug}` (сортировка, пагинация), `/article/{slug}` (просмотры, похожие).