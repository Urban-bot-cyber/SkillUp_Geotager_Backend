<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Repositories\AuthRepository;
use App\Traits\ResponseTrait;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;


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
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
    
        $user = User::where('email', $credentials['email'])->first();
    
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }
    
        // Debugging logs
        Log::info('User login successful', ['user_id' => $user->id]);
    
        $user->tokens()->delete();
        $token = $user->createToken('Personal Access Token')->accessToken;
    
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => now()->addDays(7),
            ],
            'message' => 'Logged in successfully.',
        ]);
    }
}