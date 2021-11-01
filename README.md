## Laravel 8 Demo

如何创建一个优质的 Laravel 项目？

1、使用官方命令创建项目

```shell
composer create-project laravel/laravel example-app

cd example-app

git add .
git commit -m 'feat: init'
```

2、引入 lint，并修复官方的不规范代码

```shell
composer require --dev laravel-fans/lint

php artisan lint:publish
php artisan lint:code
git add .
git commit -m 'build: lint'

php artisan lint:code --fix
git add .
git commit -m 'style: clear code'
```

3、引入 Docker

```shell
composer require --dev laravel-fans/docker

php artisan docker:publish
docker build -t laravel-demo:debug .
docker run -p 8000:80 -it laravel-demo:debug
open http://localhost:8000

git add .
git commit -m 'build: docker'
```
