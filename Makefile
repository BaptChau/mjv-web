install:
	docker compose build

build:
	docker compose up --build -d

up:
	docker compose up -d

stop:
	docker compose down

migrate:
	docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
shell:
	docker compose exec app sh