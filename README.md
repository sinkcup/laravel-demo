# Laravel 6.0 Demo

[![CircleCI](https://circleci.com/gh/sinkcup/laravel-demo/tree/6.0.svg?style=svg)](https://circleci.com/gh/sinkcup/laravel-demo/tree/6.0)
[![Docker Cloud Build Status](https://img.shields.io/docker/cloud/build/sinkcup/laravel-demo.svg)](https://hub.docker.com/r/sinkcup/laravel-demo)
[![codecov](https://codecov.io/gh/sinkcup/laravel-demo/branch/6.0/graph/badge.svg)](https://codecov.io/gh/sinkcup/laravel-demo)

This project provides CI(CircleCI), Docker, lint(phpcs), test(phpunit and codecov) for Laravel.

PS: this Docker is for production not local development.

## Docker

this Laravel Docker has 3 roles, you can switch by `CONTAINER_ROLE: app/scheduler/queue`:

```
# default web app
docker run --rm --name laravel_demo -e DB_CONNECTION=sqlite -t sinkcup/laravel-demo:6.0

# scheduler(cron)
docker run --rm --name laravel_demo_scheduler -e DB_CONNECTION=sqlite -e CONTAINER_ROLE=scheduler -t sinkcup/laravel-demo:6.0

# queue
docker run --rm --name laravel_demo_queue -e DB_CONNECTION=sqlite -e CONTAINER_ROLE=queue -t sinkcup/laravel-demo:6.0
```

![docker run](https://user-images.githubusercontent.com/4971414/64695831-a0a50980-d4cf-11e9-978a-e1dbf96ea738.png)

detail:

- It's wrong to save the log to file when using Docker, should output to stdout, so I have changed `config/logging.php`.
- Laravel default routes can't be cached, it has error: "Unable to prepare route [/] for serialization. Uses Closure.", so I have changed `route/web.php` and `route/api.php`.
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
