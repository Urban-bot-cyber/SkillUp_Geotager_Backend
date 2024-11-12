<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Repositories\AuthRepository;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    use ResponseTrait; 

    protected $auth;

    public function __construct(AuthRepository $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Authentication"},
     *     summary="Login",
     *     description="User login with email and password",
     *     operationId="login",
     *     @OA\RequestBody(
     *         description="Login credentials",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="email",
     *                     description="User Email",
     *                     type="string",
     *                     example="john.doe@example.com"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     description="User password",
     *                     type="string",
     *                     example="YourPassword123!"
     *                 ),
     *                 required={"email", "password"}
     *             )
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", example={"access_token": "token", "token_type": "Bearer", "expires_at": "timestamp"}),
     *             @OA\Property(property="message", type="string", example="Logged in successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input or login failed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid credentials.")
     *         )
     *     )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $data = $this->auth->login($request->all());

            return $this->responseSuccess($data, 'Logged in successfully.');
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage()); 
        }
    }
}
