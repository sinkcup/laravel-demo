<?php

namespace Tests\Unit;

use App\User;
use Illuminate\Support\Facades\Log;
use Redis;
use RedisManager;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $this->assertTrue(true);
    }

    public function testDatabase()
    {
        $user1 = factory(User::class)->create();
        $name = 'user2';
        $email = 'user2@example.com';
        factory(User::class)->create(compact('name', 'email'));
        $this->assertEquals(2, User::count());
        $this->assertEquals($user1->toArray(), User::first()->toArray());
        $this->assertEquals($name, User::where('email', $email)->first()->name);
    }

    public function testPhpRedis()
    {
        $user = factory(User::class)->create();
        $redis = new Redis();
        try {
            $redis->connect(config('database.redis.default.host'));
        } catch (\Exception $e) {
            Log::warning($e->getMessage());
            return;
        }
        $key = 'user:profile:' . $this->faker->randomNumber();
        $redis->hMSet($key, $user->toArray());
        $this->assertEquals($user->toArray(), $redis->hGetAll($key));
        $this->assertEmpty(RedisManager::hgetall($key));
    }

    public function testRedisManager()
    {
        $user = factory(User::class)->create();
        $key = 'user:profile:' . $this->faker->randomNumber();
        try {
            RedisManager::hmset($key, $user->toArray());
        } catch (\Exception $e) {
            Log::warning($e->getMessage());
            return;
        }
        $this->assertEquals($user->toArray(), RedisManager::hgetall($key));
        $redis = new Redis();
        $redis->connect(config('database.redis.default.host'));
        // NOTICE: laravel RedisManager has prefix
        $this->assertEquals($user->toArray(), $redis->hGetAll(config('database.redis.options.prefix') . $key));
    }

    public function testLocale()
    {
        $locale = 'zh_CN';
        // https://www.php.net/manual/en/function.gettext.php
        setlocale(LC_ALL, $locale);
        $this->assertFalse(\App::isLocale($locale));

        // https://laravel.com/docs/6.x/localization#configuring-the-locale
        \App::setLocale($locale);
        $this->assertTrue(\App::isLocale($locale));
    }
}
