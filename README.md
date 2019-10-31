# Laravel 6 Demo

[![CircleCI](https://circleci.com/gh/sinkcup/laravel-demo/tree/6.x.svg?style=svg)](https://circleci.com/gh/sinkcup/laravel-demo/tree/6.x)
[![Docker Cloud Build Status](https://img.shields.io/docker/cloud/build/sinkcup/laravel-demo.svg)](https://hub.docker.com/r/sinkcup/laravel-demo)
[![codecov](https://codecov.io/gh/sinkcup/laravel-demo/branch/6.x/graph/badge.svg)](https://codecov.io/gh/sinkcup/laravel-demo)
[![coverage](./coverage.png)](https://github.com/sinkcup/laravel-demo)

This project provides CI, Docker, Lint, Tests for Laravel.

## Docker

This Laravel Docker is for production not local development.

It has 3 roles, you can switch by `CONTAINER_ROLE: app/scheduler/queue`:

```
# default web app
docker run --rm --name laravel_demo -e DB_CONNECTION=sqlite -t sinkcup/laravel-demo:6

# scheduler(cron)
docker run --rm --name laravel_demo_scheduler -e DB_CONNECTION=sqlite -e CONTAINER_ROLE=scheduler -t sinkcup/laravel-demo:6

# queue
docker run --rm --name laravel_demo_queue -e DB_CONNECTION=sqlite -e CONTAINER_ROLE=queue -t sinkcup/laravel-demo:6
```

![docker run](https://user-images.githubusercontent.com/4971414/64695831-a0a50980-d4cf-11e9-978a-e1dbf96ea738.png)

detail:

- Laravel default log config is saving to file, it's wrong in Docker, should output to stdout, so I have changed `config/logging.php`.
- Laravel default routes can't be cached, it has error: "Unable to prepare route [/] for serialization. Uses Closure.", so I have changed `routes/web.php` and `routes/api.php`.
- Docker can run cron in the foreground, should never run it in background.

## IDE Helper

[ide-helper](https://github.com/barryvdh/laravel-ide-helper) is only required in dev, how to run composer script only in dev?

If you add script to run it in `composer.json`, it will crash when to deploy using `composer install --optimize-autoloader --no-dev`.

the best way is:

```
"scripts":{
    "post-autoload-dump": [
        "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
        "@php artisan package:discover --ansi"
        "if (php artisan | grep 'ide-helper:generate'); then php artisan ide-helper:generate; fi",
        "if (php artisan | grep 'ide-helper:meta'); then php artisan ide-helper:meta; fi"
    ]
},
```

## CircleCI

Environment Variables:

Name | Value
-----|--------------
APP_ENV | testing
APP_KEY	| generate by `php artisan key:generate --show`
CODECOV_TOKEN | get from [codecov.io](https://codecov.io/)
DB_PASSWORD | Passw0rd!

![CircleCI Environment Variables](https://user-images.githubusercontent.com/4971414/64674927-80ac2080-d4a4-11e9-8448-6e9f4a67a128.png)

## Lint

This project use [PSR12 coding standard](https://www.php-fig.org/psr/psr-12/), you can find rules in `phpcs.xml`.

When you run `composer install`, the `.git-pre-commit` will be copyed to `.git/hooks/pre-commit`, so it will check locally when you commit codes.

If there are some format errors, you could try to fix automatically:

```
./lint.sh --fix
```

You can find `lint.sh` run in `.circleci/config.yml`, so it will check coding standard when codes pushed or a PR created.

## Tests

When you run `./phpunit.sh`, it will save coverage report to `clover.xml`.

You can register for a TOKEN on [codecov.io](https://codecov.io/), so CI can upload the report to it.
