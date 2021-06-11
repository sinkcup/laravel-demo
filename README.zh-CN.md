# Laravel 6 Demo

[![CircleCI](https://circleci.com/gh/sinkcup/laravel-demo/tree/6.x.svg?style=svg)](https://circleci.com/gh/sinkcup/laravel-demo/tree/6.x)
[![Docker Cloud Build Status](https://img.shields.io/docker/cloud/build/sinkcup/laravel-demo.svg)](https://hub.docker.com/r/sinkcup/laravel-demo)
[![codecov](https://codecov.io/gh/sinkcup/laravel-demo/branch/6.x/graph/badge.svg)](https://codecov.io/gh/sinkcup/laravel-demo)
![coverage](https://raw.githubusercontent.com/sinkcup/laravel-demo/6.x/coverage.png)

[![CODING CI（Jenkins）构建状态](https://codes-farm.coding.net/badges/laravel-demo/job/88282/6.x/build.svg)](https://codes-farm.coding.net/p/laravel-demo/ci/job?id=88282)
[![CODING 测试覆盖率](https://codes-farm.coding.net/p/laravel-demo/git/raw/6.x/coverage.png)](https://80d543d6-1d38-46ef-9339-b584180ce012.cci-report.coding.io/)

本项目演示 Laravel CI、Docker、Lint、Tests。

*本文档提供这些文字版本：[English](README.md)、[汉字](README.zh-CN.md)。*

## Docker

本项目提供 2 种 Docker：生产环境 和 开发环境。

构建步骤：

```
# 默认生产环境 production
docker build -t laravel-demo:6 .

# 开发环境
docker build -t laravel-demo:6-dev --build-arg APP_ENV=local .

# 加速构建（自动修改 apt、composer、npm 的源）
docker build -t laravel-demo:6-dev --build-arg APP_ENV=local --build-arg SPEED=up .
```

构建结果 [hub.docker.com](https://hub.docker.com/r/sinkcup/laravel-demo):

```
docker pull sinkcup/laravel-demo:6
```

可切换3种运行模式 `CONTAINER_ROLE: app/scheduler/queue`:

```
# 默认 web app
docker run --rm --name laravel_demo -e DB_CONNECTION=sqlite -t sinkcup/laravel-demo:6

# 定时任务 scheduler(cron)
docker run --rm --name laravel_demo_scheduler -e DB_CONNECTION=sqlite -e CONTAINER_ROLE=scheduler -t sinkcup/laravel-demo:6

# 队列 queue
docker run --rm --name laravel_demo_queue -e DB_CONNECTION=sqlite -e CONTAINER_ROLE=queue -t sinkcup/laravel-demo:6
```

![docker run](https://user-images.githubusercontent.com/4971414/64695831-a0a50980-d4cf-11e9-978a-e1dbf96ea738.png)

细节：

- Laravel 默认配置把 log 写入本地文件，在 Docker 中不能这样，应该写入标准输出，所以我修改了 `config/logging.php`.
- Laravel 默认路由不能被缓存，报错："Unable to prepare route [/] for serialization. Uses Closure."，所以我修改了 `routes/web.php` and `routes/api.php`.
- Docker 可以在前台运行 cron，切记不要在后台运行。

## IDE Helper

只有开发环境需要 [ide-helper](https://github.com/barryvdh/laravel-ide-helper) ，那如何只在开发环境运行 composer 脚本呢？

如果在 `composer.json` 中加入脚本，运行这条命令会崩溃 `composer install --optimize-autoloader --no-dev`.

最佳方法是：

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

## 代码规范检查（Lint）

本项目使用 [PSR12 代码规范](https://www.php-fig.org/psr/psr-12/)，具体请看 `phpcs.xml`。

运行 `composer install` 时，`.git-pre-commit` 会被复制到 `.git/hooks/pre-commit`，本地提交时就会检查规范。

如果检查出代码不规范，可以尝试自动修复：

```
./lint.sh --fix
```

`lint.sh` 在 `.circleci/config.yml` 和 `Jenkinsfile` 中也被调用，所以推送和请求合并时也会检查代码规范。

## 测试（Tests）

运行 `./phpunit.sh` 会生成这些文件：

- `clover.xml`： Clover XML 格式的测试覆盖率报告，你可以注册 [codecov.io](https://codecov.io/) 获得一个 TOKEN，然后在 CI 中上传报告。
- `storage/app/public/coverage`： HTML 格式的测试覆盖率报告，用于本地访问 [http://laravel-demo.localhost/storage/coverage/](http://laravel-demo.localhost/storage/coverage/)
- `junit.xml`： JUnit XML 格式的测试执行结果，用于在 Jenkins 中收集。

## 持续集成（CI）

### CircleCI

环境变量：

名称 | 值
-----|--------------
CODECOV_TOKEN | 可选，注册 [codecov.io](https://codecov.io/) 来获得

![CircleCI Environment Variables](https://user-images.githubusercontent.com/4971414/70208756-539ca080-1769-11ea-95f8-de50a01eecbd.png)

### Jenkins\([免费的 CODING.net CI](https://coding.net/products/ci?cps_source=PIevZ6Jr)\)

环境变量：

名称 | 值
-----|--------------
SPEED | 可选，修改源地址用来加速构建，取值：up/down/keep
DOCKER_USER | 可选，Docker 用户名，如果要使用私有镜像才要配置此项
DOCKER_PASSWORD | 可选，Docker 密码
DOCKER_SERVER | 可选，Docker 服务器地址
DOCKER_PATH_PREFIX | 可选，Docker 路径前缀

![CODING CI 环境变量](https://user-images.githubusercontent.com/4971414/70208810-7c249a80-1769-11ea-979f-45a56e79a126.png)
