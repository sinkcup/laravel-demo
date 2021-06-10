<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     * @throws ValidationException
     *
     * @OA\Get(
     *     path="/api/users",
     *     tags={"用户"},
     *     summary="用户列表",
     *     @OA\Parameter(
     *         name="gender",
     *         description="性别",
     *         required=false,
     *         in="query",
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="total", type="integer", example=19),
     *             @OA\Property(property="per_page", type="integer", example=15),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User"))
     *         ),
     *     ),
     *     @OA\Response(response=422, description="The given data was invalid."),
     * )
     */
    public function index()
    {
        $validatedData = $this->validate(
            request(),
            ['gender' => 'integer']
        );
        return User::where($validatedData)
            ->paginate();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     *
     * @OA\Post(
     *     path="/api/users",
     *     tags={"用户"},
     *     summary="创建用户",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=123),
     *         ),
     *     ),
     *     @OA\Response(response=422, description="The given data was invalid."),
     * )
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'string|max:20',
            'email' => 'required|unique:users|max:100',
            'password' => 'required|max:50',
        ]);
        $validatedData['password'] = Hash::make($validatedData['password']);
        return User::create($validatedData);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param string $id
     * @return Response
     *
     * @todo return id. maybe #/components/requestBodies/UserBody no id, #/components/schemas/User has id?
     * @OA\Get(
     *     path="/api/users/{id}",
     *     tags={"用户"},
     *     summary="获取单个用户",
     *     @OA\Parameter(
     *         name="id",
     *         description="ID",
     *         required=true,
     *         in="path",
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             ref="#/components/schemas/User"
     *         ),
     *     )
     * )
     */
    public function show(Request $request, $id = 'me')
    {
        return $id == 'me' ? $request->user() : User::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User $user
     * @return User
     *
     * @todo 微信小程序技术差，不支持 HTTP PATCH，只能用 PUT
     * @link https://developers.weixin.qq.com/community/develop/doc/0002e4585d0aa87b5e670fa0b50800
     * @OA\Put(
     *     path="/api/users/{id}",
     *     tags={"用户"},
     *     summary="修改用户",
     *     @OA\Parameter(
     *         name="id",
     *         description="ID",
     *         required=true,
     *         in="path",
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             ref="#/components/schemas/User"
     *         ),
     *     ),
     *     @OA\Response(response=422, description="The given data was invalid."),
     * )
     */
    public function update(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'name' => 'string|max:20',
            'password' => 'required|max:50',
        ]);
        $validatedData['password'] = Hash::make($validatedData['password']);
        $user->update($validatedData);
        return $user;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @return bool
     *
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     tags={"用户"},
     *     summary="删除用户",
     *     @OA\Parameter(
     *         name="id",
     *         description="ID",
     *         required=true,
     *         in="path",
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *     )
     * )
     * @throws Exception
     */
    public function destroy(User $user)
    {
        return $user->delete();
    }
}
