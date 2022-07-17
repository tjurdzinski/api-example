# Build the container
build:
	docker-compose build app

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
