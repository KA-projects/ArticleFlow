.DEFAULT_GOAL := help

.PHONY: setup up down seed sass ps logs help

setup: ## Первичная развёртка: .env + up + зависимости + SCSS + сидинг
	cp -n .env.example .env || true
	@$(MAKE) up
	@$(MAKE) seed

up: ## Собрать и поднять контейнеры + зависимости + SCSS
	docker compose up -d --build
	docker compose run --rm app composer install
	@$(MAKE) sass

down: ## Остановить контейнеры
	docker compose down

seed: ## Засеять данные (идемпотентно)
	docker compose run --rm app php bin/seed.php

sass: ## Одноразовая сборка SCSS
	docker compose run --rm --entrypoint "" sass npx sass assets/scss:public/assets/css

ps: ## Статус контейнеров
	docker compose ps

logs: ## Логи приложения
	docker compose logs -f app

help: ## Справка
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-8s\033[0m %s\n", $$1, $$2}'