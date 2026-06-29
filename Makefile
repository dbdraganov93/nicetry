.PHONY: up down build test cs migrate jwt
up:
	docker compose up --build
build:
	docker compose build
down:
	docker compose down --remove-orphans
test:
	composer test
cs:
	composer cs:check
migrate:
	docker compose exec gateway php bin/console doctrine:migrations:migrate --no-interaction
jwt:
	mkdir -p config/jwt && openssl genrsa -out config/jwt/private.pem -aes256 -passout pass:change-me 4096 && openssl rsa -pubout -in config/jwt/private.pem -passin pass:change-me -out config/jwt/public.pem
