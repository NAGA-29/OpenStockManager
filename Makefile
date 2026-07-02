SHELL := /bin/sh

COMPOSE := docker compose
API := $(COMPOSE) exec api
FRONTEND := $(COMPOSE) exec frontend

.PHONY: help up build down restart ps logs api shell composer-install key migrate fresh test lint pint phpstan frontend frontend-lint frontend-typecheck frontend-build

help:
	@printf '%s\n' \
		'Targets:' \
		'  make up                Start API, frontend, and supporting services' \
		'  make build             Build and start all services' \
		'  make down              Stop services' \
		'  make ps                Show service status' \
		'  make logs              Follow logs' \
		'  make shell             Open a shell in the API container' \
		'  make composer-install  Install PHP dependencies in the API container' \
		'  make key               Generate Laravel app key' \
		'  make migrate           Run migrations and seeders' \
		'  make test              Run API tests' \
		'  make lint              Run API composer lint' \
		'  make frontend-build    Build the frontend'

up:
	$(COMPOSE) up -d

build:
	$(COMPOSE) up -d --build

down:
	$(COMPOSE) down

restart:
	$(COMPOSE) restart

ps:
	$(COMPOSE) ps

logs:
	$(COMPOSE) logs -f

api:
	$(API) php artisan $(cmd)

shell:
	$(API) sh

composer-install:
	$(API) composer install

key:
	$(API) php artisan key:generate

migrate:
	$(API) php artisan migrate --seed

fresh:
	$(API) php artisan migrate:fresh --seed

test:
	$(API) php artisan test

lint:
	$(API) composer lint

pint:
	$(API) composer pint

phpstan:
	$(API) composer phpstan

frontend:
	$(FRONTEND) pnpm $(cmd)

frontend-lint:
	$(FRONTEND) pnpm run lint

frontend-typecheck:
	$(FRONTEND) pnpm run typecheck

frontend-build:
	$(FRONTEND) pnpm run build
