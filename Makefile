.DEFAULT_GOAL := help
.PHONY: help init start stop restart build install cc db-create db-diff db-migrate db-fixtures db-reset test lint

DOCKER   = docker compose
PHP      = $(DOCKER) exec app php
CONSOLE  = $(PHP) bin/console
COMPOSER = $(DOCKER) exec app composer

##
## —— Project ——————————————————————————————————————————————————
##

help: ## Show this help
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-20s\033[0m %s\n", $$1, $$2}' \
		| sed -e 's/\[32m##/[33m/'

init: build start install db-create db-migrate ## Bootstrap the project (build + Docker + deps + DB)
	@echo "\033[32m✓ Project ready — http://localhost:8080\033[0m"

build: ## Build Docker images
	$(DOCKER) build

start: ## Start Docker containers
	$(DOCKER) up -d

stop: ## Stop Docker containers
	$(DOCKER) down

restart: stop start ## Restart Docker containers

sh: ## Open a bash shell in the app container
	$(DOCKER) exec -it app bash

##
## —— Symfony ——————————————————————————————————————————————————
##

install: ## Install PHP dependencies (with dev)
	$(COMPOSER) install

cc: ## Clear cache
	$(CONSOLE) cache:clear

##
## —— Database —————————————————————————————————————————————————
##

db-create: ## Create the database
	$(CONSOLE) doctrine:database:create --if-not-exists

db-diff: ## Generate a new migration from entity changes
	$(CONSOLE) make:migration

db-migrate: ## Run migrations
	$(CONSOLE) doctrine:migrations:migrate --no-interaction --allow-no-migration

db-fixtures: ## Load fixtures
	$(CONSOLE) doctrine:fixtures:load --no-interaction

db-reset: ## Drop, create and migrate the database
	$(CONSOLE) doctrine:database:drop --force --if-exists
	$(CONSOLE) doctrine:database:create
	$(CONSOLE) doctrine:migrations:migrate --no-interaction --allow-no-migration

##
## —— Tests ————————————————————————————————————————————————————
##

test: ## Run tests
	$(PHP) bin/phpunit

##
## —— Code quality —————————————————————————————————————————————
##

lint: ## Lint twig, yaml and container
	$(CONSOLE) lint:twig templates/
	$(CONSOLE) lint:yaml config/
	$(CONSOLE) lint:container
