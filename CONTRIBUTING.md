# Contributing

Welcome et thanks to contribute to this project.  
First, please describe your needs in a new [issue](https://github.com/litesaml/lightsaml/issues).

## How to write code

1. Respect [PSR-1](https://www.php-fig.org/psr/psr-1/) and [PSR-12](https://www.php-fig.org/psr/psr-12/)
2. Test your code
3. Make small code for easy review

## How to run test

```shell
docker run --rm -it -w /app -v $PWD:/app webdevops/php:8.1 composer update
docker run --rm -it -w /app -v $PWD:/app webdevops/php:8.1 composer test
docker run --rm -it -w /app -v $PWD:/app webdevops/php:8.1 composer phpcs
docker run --rm -it -w /app -v $PWD:/app webdevops/php:8.1 composer phpstan
```
