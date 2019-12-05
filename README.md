# Laravel 6 Demo

[![CircleCI](https://circleci.com/gh/sinkcup/laravel-demo/tree/6.x.svg?style=svg)](https://circleci.com/gh/sinkcup/laravel-demo/tree/6.x)
[![Docker Cloud Build Status](https://img.shields.io/docker/cloud/build/sinkcup/laravel-demo.svg)](https://hub.docker.com/r/sinkcup/laravel-demo)
[![codecov](https://codecov.io/gh/sinkcup/laravel-demo/branch/6.x/graph/badge.svg)](https://codecov.io/gh/sinkcup/laravel-demo)
![coverage](https://raw.githubusercontent.com/sinkcup/laravel-demo/6.x/coverage.png)

[![CODING CI（Jenkins）构建状态](https://codes-farm.coding.net/badges/laravel-demo/job/88282/6.x/build.svg)](https://codes-farm.coding.net/p/laravel-demo/ci/job)
[![CODING 测试覆盖率](https://codes-farm.coding.net/p/laravel-demo/git/raw/6.x/coverage.png)](https://m6zlsd.coding-pages.com/coverage/)

This project provides CI, Docker, Lint, Tests for Laravel.

*Read this in these languages: [English](README.md), [汉字](README.zh-CN.md).*

## Docker

This project build two Dockers for production and development.

build steps:

```
# default production
docker build -t laravel-demo:6 .

# development
docker build -t laravel-demo:6-dev --build-arg APP_ENV=local .

# speed up docker build by change apt, composer, npm mirror
docker build -t laravel-demo:6-dev --build-arg APP_ENV=local --build-arg SPEED=up .
```

build result on [hub.docker.com](https://hub.docker.com/r/sinkcup/laravel-demo):

```
docker pull sinkcup/laravel-demo:6
```

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

## Lint

This project use [PSR12 coding standard](https://www.php-fig.org/psr/psr-12/), you can find rules in `phpcs.xml`.

When you run `composer install`, the `.git-pre-commit` will be copyed to `.git/hooks/pre-commit`, so it will check locally when you commit codes.

If there are some format errors, you could try to fix automatically:

```
./lint.sh --fix
```

You can find `lint.sh` run in `.circleci/config.yml` and `Jenkinsfile`, so it will check coding standard when codes pushed or a PR created.

## Tests

When you run `./phpunit.sh`, it will generate:

- `clover.xml`: coverage report in Clover XML format, you can register for a TOKEN on [codecov.io](https://codecov.io/), so CI can upload the report to it.
- `storage/app/public/coverage`: coverage report in HTML format, you can access by [http://laravel-demo.localhost/storage/coverage/](http://laravel-demo.localhost/storage/coverage/)
- `junit.xml`: test execution in JUnit XML format, it can be collected by Jenkins.

## Continuous integration(CI)

### CircleCI

Environment Variables:

Name | Value
-----|--------------
CODECOV_TOKEN | optional, get from [codecov.io](https://codecov.io/)

![CircleCI Environment Variables](https://user-images.githubusercontent.com/4971414/70208756-539ca080-1769-11ea-95f8-de50a01eecbd.png)

### Jenkins\([CODING.net free CI](https://coding.net/products/ci?cps_source=PIevZ6Jr)\)

Environment Variables:

Name | Value
-----|--------------
DOCKER_USER | optional, Docker username, set this if you want to use private docker image
DOCKER_PASSWORD | optional, Docker password
DOCKER_SERVER | optional, Docker server
DOCKER_PATH_PREFIX | optional, Docker path prefix
SPEED | optional, change mirror to speed up docker build, values: up/down/keep

![CODING CI Environment Variables](https://user-images.githubusercontent.com/4971414/70208810-7c249a80-1769-11ea-979f-45a56e79a126.png)
