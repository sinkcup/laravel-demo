# Laravel 6.0 Demo

[![CircleCI](https://circleci.com/gh/sinkcup/laravel-demo/tree/6.0.svg?style=svg)](https://circleci.com/gh/sinkcup/laravel-demo/tree/6.0)
[![Docker Cloud Build Status](https://img.shields.io/docker/cloud/build/sinkcup/laravel-demo.svg)](https://hub.docker.com/r/sinkcup/laravel-demo)
[![codecov](https://codecov.io/gh/sinkcup/laravel-demo/branch/6.0/graph/badge.svg)](https://codecov.io/gh/sinkcup/laravel-demo)

This project provides CI(CircleCI), Docker, lint(phpcs), test(phpunit and codecov) for Laravel.

PS: this Docker is for production not local development.

## IDE Helper

How to run composer script only in dev? such as [ide-helper](https://github.com/barryvdh/laravel-ide-helper), it's required in dev, if you run it in `composer.json`, it will crash when to deploy using `composer install --optimize-autoloader --no-dev`.

the best way is:

```
"scripts":{
    "post-install-cmd": [
        "if (php artisan | grep 'ide-helper:generate'); then php artisan ide-helper:generate; fi",
        "if (php artisan | grep 'ide-helper:meta'); then php artisan ide-helper:meta; fi"
    ]
},
```
