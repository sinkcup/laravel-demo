<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="Laravel Swagger OpenAPI 3 文档",
 *     version="0.1.0",
 *     description="这是一个 Laravel DEMO API 文档，仅做参考。",
 *     @OA\Contact(
 *         name="sinkcup",
 *         url="https://github.com/sinkcup/laravel-demo"
 *     )
 * )
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;
}
