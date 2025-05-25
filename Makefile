.PHONY: up down test validate-schema backup-db

up:
	docker compose up -d --remove-orphans

down:
	docker compose down

test:
	docker compose exec php vendor/bin/phpunit

validate-schema:
	docker compose exec php bin/console doctrine:schema:validate

backup-db:
	docker compose exec -T database pg_dump -U app app > backup_$$(date +%Y-%m-%d_%H-%M-%S).sql