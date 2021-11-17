SHELL=bash

DOCKER_COMPOSE  = docker-compose

EXEC_JS         = $(DOCKER_COMPOSE) exec -T node entrypoint
EXEC_DB         = $(DOCKER_COMPOSE) exec -T database
EXEC_QA         = $(DOCKER_COMPOSE) exec -T --env=APP_ENV=test php entrypoint
EXEC_PHP        = $(DOCKER_COMPOSE) exec -T php entrypoint

SYMFONY_CONSOLE = $(EXEC_PHP) php bin/console
COMPOSER        = $(EXEC_PHP) composer
YARN             = $(EXEC_JS) yarn

TEST_DBNAME = test_agate_portal
PORTAL_DBNAME = main
DB_USER = root
DB_PWD = root

CURRENT_DATE = `date "+%Y-%m-%d_%H-%M-%S"`

# Helper variables
_TITLE := "\033[32m[%s]\033[0m %s\n" # Green text
_ERROR := "\033[31m[%s]\033[0m %s\n" # Red text

##
## Project
## -------
##

.DEFAULT_GOAL := help
help: ## Show this help message
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-25s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
.PHONY: help

install: ## Install and start the project
install: build node_modules start vendor db test-db assets map-tiles
.PHONY: install

build: ## Build the Docker images
	@$(DOCKER_COMPOSE) pull --include-deps
	@$(DOCKER_COMPOSE) build --force-rm --compress
.PHONY: build

start: ## Start all containers and the PHP server
	@$(DOCKER_COMPOSE) up -d --remove-orphans --no-recreate
	@$(MAKE) start-php
.PHONY: start

stop: ## Stop all containers and the PHP server
	@$(DOCKER_COMPOSE) stop
	@$(MAKE) stop-php
.PHONY: stop

restart: ## Restart the containers & the PHP server
	@$(MAKE) stop
	@$(MAKE) start
.PHONY: restart

kill: ## Stop all containers
	$(DOCKER_COMPOSE) kill
	$(DOCKER_COMPOSE) down --volumes --remove-orphans
.PHONY: kill

reset: ## Stop and start a fresh install of the project
reset: kill install
.PHONY: reset

clean: ## Stop the project and remove generated files and configuration
clean: kill
	rm -rf vendor node_modules build var/cache/* var/log/* var/sessions/*
.PHONY: clean

##
## Tools
## -----
##

cc: ## Clear and warmup PHP cache
	$(SYMFONY_CONSOLE) cache:clear --no-warmup
	$(SYMFONY_CONSOLE) cache:warmup
.PHONY: cc

db: ## Reset the development database
db: dev-db migrations fixtures
.PHONY: db

dev-db: wait-for-db ## Drop & create development database
	-$(SYMFONY_CONSOLE) doctrine:database:drop --if-exists --force
	-$(SYMFONY_CONSOLE) doctrine:database:create --if-not-exists

.PHONY: dev-db

test-db: wait-for-db ## Create a proper database for testing
	@echo "doctrine:database:drop"
	@APP_ENV=test $(SYMFONY_CONSOLE) --env=test doctrine:database:drop --if-exists --force
	@echo "doctrine:database:create"
	@APP_ENV=test $(SYMFONY_CONSOLE) --env=test doctrine:database:create
	@echo "doctrine:schema:create"
	@APP_ENV=test $(SYMFONY_CONSOLE) --env=test doctrine:migrations:migrate --no-interaction
	@echo "doctrine:fixtures:load"
	@APP_ENV=test $(SYMFONY_CONSOLE) --env=test doctrine:fixtures:load --append --no-interaction
.PHONY: test-db

prod-db: ## Installs production database if it has been saved in "var/dump.sql". You have to download it manually.
prod-db: var/dump.sql dev-db
	@if [ -f var/dump.sql ]; then \
		$(EXEC_DB) mysql -u$(DB_USER) -p$(DB_PWD) $(PORTAL_DBNAME) -e "source /srv/dump.sql" ;\
	else \
		echo "No prod database to process. Download it and save it to var/dump.sql." ;\
	fi;
.PHONY: prod-db

var/dump.sql: ## Tries to download a database from production environment
	@if [ "${ESTEREN_MAPS_DEPLOY_REMOTE}" = "" ]; then \
		echo "[ERROR] Please specify the ESTEREN_MAPS_DEPLOY_REMOTE env var to connect to a remote" ;\
		exit 1 ;\
	fi; \
	if [ "${ESTEREN_MAPS_DEPLOY_DIR}" = "" ]; then \
		echo "[ERROR] Please specify the ESTEREN_MAPS_DEPLOY_DIR env var to determine which directory to use in prod" ;\
		exit 1 ;\
	fi; \
	ssh ${ESTEREN_MAPS_DEPLOY_REMOTE} ${ESTEREN_MAPS_DEPLOY_DIR}/../dump_db.bash > var/dump.sql

migrations: ## Reset the database
	$(SYMFONY_CONSOLE) doctrine:migrations:migrate --no-interaction
.PHONY: migrations

fixtures: ## Install all dev fixtures in the database
	$(SYMFONY_CONSOLE) doctrine:fixtures:load --append --no-interaction
	@[[ -d public/uploads/portal/ ]] || $(EXEC_PHP) mkdir -p public/uploads/portal/
	@[[ -d var/uploads/products/ ]] || $(EXEC_PHP) mkdir -p var/uploads/products/
.PHONY: fixtures

watch: ## Run Webpack to compile assets on change
	$(YARN) run watch
.PHONY: watch

assets: ## Run Webpack to compile assets
assets: node_modules
	@mkdir -p public/build/
	$(YARN) run dev
.PHONY: assets

vendor: ## Install PHP vendors
	$(COMPOSER) install
.PHONY: vendor

node_modules: ## Install JS vendors
node_modules: yarn.lock
	@mkdir -p public/build/
	$(DOCKER_COMPOSE) run --rm --entrypoint=/bin/entrypoint node yarn install
	$(DOCKER_COMPOSE) up -d node
.PHONY: node_modules

wait-for-db:
	@echo " Waiting for database..."
	@for i in {1..5}; do $(EXEC_DB) mysql -u$(DB_USER) -p$(DB_PWD) -e "SELECT 1;" > /dev/null 2>&1 && sleep 1 || echo " Unavailable..." ; done;
.PHONY: wait-for-db

start-php:
	$(DOCKER_COMPOSE) up --force-recreate --no-deps -d php
.PHONY: start-php

stop-php:
	$(DOCKER_COMPOSE) stop php
.PHONY: stop-php

full-reset:
	@printf $(_ERROR) "WARNING" "This will remove ALL containers, data, cache, to make a fresh project! Use at your own risk!"

	@if [[ -z "$(RESET)" ]]; then \
		printf $(_ERROR) "WARNING" "If you are 100% sure of what you are doing, re-run with $(MAKE) -e RESET=1 full-reset" ; \
		exit 0 ; \
	fi ; \
	\
	$(DOCKER_COMPOSE) down --volumes --remove-orphans && \
	rm -rf \
		"var/cache/*" \
		"var/uploads/*" \
		"var/log/*" \
		"var/sessions/*" \
		public/build \
		public/bundles \
		public/uploads \
		node_modules \
		vendor \
	&& \
	\
	printf $(_TITLE) "OK" "Done!"
.PHONY: full-reset

##
## Tests
## -----
##

ci-vendor:
	$(COMPOSER) install --no-interaction --classmap-authoritative --prefer-dist
.PHONY: ci-vendor

install-php: ## Prepare environment to execute PHP tests
install-php: build start ci-vendor db test-db assets
.PHONY: install-php

install-node: ## Prepare environment to execute NodeJS tests
install-node: build node_modules start
.PHONY: install-node

php-tests: ## Execute qa & tests
php-tests: start-php qa phpstan cs-dry-run phpunit
.PHONY: php-tests

phpstan: ## Execute phpstan
phpstan: start-php check-phpunit
	@echo "Clear & warmup test environment cache because phpstan may use it..."
	$(EXEC_QA) bin/console cache:clear --no-warmup
	$(EXEC_QA) bin/console cache:warmup
	$(EXEC_QA) phpstan analyse -c phpstan.neon
.PHONY: phpstan

check-phpunit:
	$(EXEC_PHP) bin/phpunit --version
.PHONY: check-phpunit

cs: ## Execute php-cs-fixer
cs:
	$(EXEC_QA) php-cs-fixer fix
.PHONY: cs

cs-dry-run: ## Execute php-cs-fixer with a simple dry run
cs-dry-run:
	$(EXEC_QA) php-cs-fixer fix --dry-run -vvv --diff --show-progress=dots
.PHONY: cs-dry-run

node-tests: ## Execute checks & tests
node-tests: start
	$(EXEC_JS) yarn run test --verbose -LLLL
.PHONY: node-tests

qa: ## Execute CS, linting, security checks, etc
qa:
	$(EXEC_QA) bin/console lint:twig templates src
	$(EXEC_QA) bin/console lint:yaml --parse-tags config
	$(EXEC_QA) bin/console lint:yaml --parse-tags src
.PHONY: qa

setup-phpunit: ## Setup PHPUnit before running it
setup-phpunit: check-phpunit

phpunit-unit: ## Execute all PHPUnit unit tests
phpunit-unit:
	$(EXEC_QA) bin/phpunit --group=unit
.PHONY: phpunit-unit

phpunit-integration: ## Execute all PHPUnit integration tests
phpunit-integration:
	$(EXEC_QA) bin/phpunit --group=integration
.PHONY: phpunit-integration

phpunit-functional: ## Execute all PHPUnit functional tests
phpunit-functional:
	$(EXEC_QA) bin/phpunit --group=functional
.PHONY: phpunit-functional

phpunit-ux: ## Execute all PHPUnit ux tests
phpunit-ux:
	$(EXEC_QA) bin/phpunit --group=ux
.PHONY: phpunit-ux

phpunit: ## Execute all PHPUnit tests
phpunit: check-phpunit
	$(EXEC_QA) bin/phpunit
.PHONY: phpunit

coverage: ## Retrieves the code coverage of the phpunit suite
coverage:
	$(EXEC_QA) php -dextension=pcov -dpcov.enabled=1 bin/phpunit --coverage-html=build/coverage/$(CURRENT_DATE) --coverage-clover=build/coverage.xml
.PHONY: coverage

##
## Agate
## -----
##

map-tiles: ## Dump built-in EsterenMap maps tiles to the public directory
	@-$(SYMFONY_CONSOLE) esterenmaps:map:generate-tiles 1 --no-interaction
.PHONY: map-tiles
