.PHONY: help up down restart logs cache-clear test test-unit test-functional test-setup test-cache-clear
.DEFAULT_GOAL := help

help: ## Show this help message
	@echo "Available commands:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  %-20s %s\n", $$1, $$2}'

up: ## Start all containers
	@echo "Starting containers."
	docker compose up -d --build

down: ## Stop all containers
	@echo "Stopping containers."
	docker compose down

restart: down up ## Restart all containers

logs: ## Show logs from all containers
	docker compose logs -f

cache-clear: ## Clear Symfony cache
	@echo "Clearing cache."
	docker compose exec php php bin/console cache:clear

composer-update: ## Update Composer dependencies
	docker compose exec php composer update

migration-migrate: ## Run migrations
	docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction

migration-generate: ## Generate new migration
	docker compose exec php php bin/console doctrine:migrations:generate

migration-diff: ## Generate migration
	docker compose exec php php bin/console doctrine:migrations:diff

test-setup: ## Create and migrate the test database
	docker compose exec mysql mysql -u root -p$${DB_ROOT_PASSWORD:-root} -e "CREATE DATABASE IF NOT EXISTS url_shortener_test; GRANT ALL PRIVILEGES ON url_shortener_test.* TO 'symfony'@'%'; FLUSH PRIVILEGES;"
	docker compose exec php php bin/console --env=test doctrine:migrations:migrate --no-interaction

test: ## Run the test suite
	docker compose exec -e APP_ENV=test php php bin/phpunit

test-unit: ## Run only unit tests
	docker compose exec -e APP_ENV=test php php bin/phpunit tests/Unit

test-functional: ## Run only functional tests
	docker compose exec -e APP_ENV=test php php bin/phpunit tests/Functional

test-cache-clear: ## Clear the test environment cache
	docker compose exec -e APP_ENV=test php php bin/console cache:clear