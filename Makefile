vendor: composer.json
	composer install

.PHONY: test
test: vendor
	vendor/bin/phpunit
	vendor/bin/phpcs --standard=PSR12 --exclude=Generic.Files.LineLength ./src
