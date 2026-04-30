install:
	docker compose build

build:
	rm -f composer.lock
	docker compose up --build -d

up:
	docker compose up -d

stop:
	docker compose down

migrate:
	docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
shell:
	docker compose exec app sh

test-unit:
	docker compose exec app php vendor/bin/phpunit --testsuite=Unit

test-functional:
	docker compose exec app php vendor/bin/codecept run Functional

test:
	docker compose exec app php vendor/bin/phpunit --testsuite=Unit
	docker compose exec app php vendor/bin/codecept run Functional

test-coverage:
	docker compose exec app php vendor/bin/phpunit --testsuite=Unit --coverage-html=var/coverage