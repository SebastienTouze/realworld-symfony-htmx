# Symfony / HTMX RealWorld demo

This repository was created to show a fullstack Symfony + HTMX website that adhere to the [RealWorld spec](https://github.com/gothinkster/realworld). 

## Project Overview 
Conduit is a social blogging site (i.e. a Medium.com clone). It uses a custom API for all requests, including authentication. 
See the [RealWorld documentation for more details](https://realworld-docs.netlify.app/) and other implementations.

## Installation

This repository is based on the [Symfony Docker template](https://github.com/dunglas/symfony-docker), 
dedicated doc is available in [docs](docs/README_SYMFONY_DOCKER.md).

Or you can simply clone this repository, then :

```shell
cd realworld-symfony-htmx
composer install
docker compose up
docker compose exec php bin/console doctrine:migration:migrate
docker compose exec php bin/console doctrine:fixtures:load
```

Then you can navigate to [localhost](https://localhost) on your browser, a user `nemo@email.com` with password `123456` is available to connect with, or you can create a new account. 
