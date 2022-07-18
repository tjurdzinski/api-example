# Build the container
build:
	@if [[ file1 -nt file2 ]]; then cp file1 file2; fi
	docker-compose build app

# Recreate database
refresh-database:
	docker-compose exec app php artisan migrate:fresh
	docker-compose exec app php artisan db:seed

test: ## Run container in development mode
	@XDEBUG_MODE=develop,debug,coverage
	docker-compose exec --env XDEBUG_MODE app php artisan test --coverage

# Build and run the container
up: ## Spin up the project
	docker-compose up -d

down: ## Stop running containers
	docker-compose stop

rm: down ## Stop and remove running containers
	docker-compose rm

prepare: build up refresh-database
