vendor: composer.json
	composer install

.PHONY: test
test: vendor
	vendor/bin/phpunit
	composer run test:cs
