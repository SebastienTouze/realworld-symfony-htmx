# Makefile heavily inspired by the Symfony docker example see docs/README_SYMFONY_DOCKER.md

# Executables (local)
DOCKER_COMP = docker compose

# Docker containers
PHP_CONT = $(DOCKER_COMP) exec php

# Executables
PHP      = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer
SYMFONY  = $(PHP) bin/console

# Misc
.DEFAULT_GOAL = help
.PHONY        : help build up start down logs sh composer vendor sf cc test

## —— 🎵 🐳 The Symfony Docker Makefile 🐳 🎵 ——————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9\./_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
build: ## Builds the Docker images
	@$(DOCKER_COMP) build --pull --no-cache

up: ## Start the docker hub in detached mode (no logs)
	@$(DOCKER_COMP) up --detach

start: build up ## Build and start the containers

down: ## Stop the docker hub
	@$(DOCKER_COMP) down --remove-orphans

logs: ## Show live logs
	@$(DOCKER_COMP) logs --tail=0 --follow

sh: ## Connect to the FrankenPHP container
	@$(PHP_CONT) sh

bash: ## Connect to the FrankenPHP container via bash so up and down arrows go to previous commands
	@$(PHP_CONT) bash

## —— Composer 🧙 ——————————————————————————————————————————————————————————————
composer: ## Run composer, pass the parameter "c=" to run a given command, example: make composer c='req symfony/orm-pack'
	@$(eval c ?=)
	@$(COMPOSER) $(c)

install-prod: ## Install vendors according to the current composer.lock file
install-prod: c=install --prefer-dist --no-dev --no-progress --no-scripts --no-interaction
install-prod: composer

## —— Symfony 🎵 ———————————————————————————————————————————————————————————————
sf: ## List all Symfony commands or pass the parameter "c=" to run a given command, example: make sf c=about
	@$(eval c ?=)
	@$(SYMFONY) $(c)

cc: c=ca:cl ## Clear the cache
cc: sf

## —— Code quality 💯 ———————————————————————————————————————————————————————————————
test-unit: ## Start all tests with phpunit
	@$(DOCKER_COMP) exec -e APP_ENV=test php bin/phpunit --testsuite Unit

test-e2e: ## Start all tests with phpunit
	@$(DOCKER_COMP) exec -e APP_ENV=test php bin/phpunit --testsuite E2E

test: ## Start all tests with phpunit, pass the parameter "c=" to add options to phpunit, example: make test c="--group e2e --stop-on-failure"
	@$(eval c ?=)
	@$(DOCKER_COMP) exec -e APP_ENV=test php bin/phpunit $(c)

cscheck: ## Run PHP-CS-Fixer checker only
	@$(DOCKER_COMP) exec php vendor/bin/php-cs-fixer check

csfix: ## Run PHP-CS-Fixer to fix coding style issues
	@$(DOCKER_COMP) exec php vendor/bin/php-cs-fixer fix

phpstan: ## Run PHPStan to check for static analysis issues
	@$(DOCKER_COMP) exec php vendor/bin/phpstan analyse --memory-limit=512M
