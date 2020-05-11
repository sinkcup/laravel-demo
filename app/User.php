<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Class User
 * @package App
 * @OA\Schema(
 *     title="用户",
 *     required={"name", "email", "password"},
 *      @OA\Property(type="string", property="name", description="昵称", example="马猴"),
 *      @OA\Property(type="string", property="email", description="邮箱", example="ma.hou@example.com"),
 *      @OA\Property(type="string", property="password", description="密码", example="1234asdf"),
 * )
 */
class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
