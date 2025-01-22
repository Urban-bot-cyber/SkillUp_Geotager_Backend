<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Repositories\AuthRepository;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    use ResponseTrait;

    protected AuthRepository $auth;

    /**
     * Constructor to inject the AuthRepository.
     *
     * @param AuthRepository $auth
     */
    public function __construct(AuthRepository $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Authentication"},
     *     summary="Register",
     *     description="Register a new user with an optional profile picture",
     *     operationId="register",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="first_name",
     *                     description="Your first name",
     *                     type="string",
     *                     example="Jon"
     *                 ),
     *                 @OA\Property(
     *                     property="last_name",
     *                     description="Your last name",
     *                     type="string",
     *                     example="Doe"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     description="User email",
     *                     type="string",
     *                     format="email",
     *                     example="john.doe@gmail.com"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     description="User password",
     *                     type="string",
     *                     format="password",
     *                     example="Test123!"
     *                 ),
     *                 @OA\Property(
     *                     property="password_confirmation",
     *                     description="Confirm password",
     *                     type="string",
     *                     format="password",
     *                     example="Test123!"
     *                 ),
     *                 @OA\Property(
     *                     property="profile_picture",
     *                     description="Profile picture of the user",
     *                     type="string",
     *                     format="binary"
     *                 ),
     *                 required={"first_name", "last_name", "email", "password", "password_confirmation"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User registered successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="access_token", type="string"),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="expires_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid input."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred while registering the user."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            // Collecting form inputs (excluding password_confirmation)
            $data = $request->only([
                'first_name',
                'last_name',
                'email',
                'password',
            ]);

            // Include the profile_picture file if present
            if ($request->hasFile('profile_picture')) {
                $data['profile_picture'] = $request->file('profile_picture');
            }

            // Register the user using the AuthRepository
            $userData = $this->auth->register($data);

            return $this->responseSuccess($userData, 'User registered successfully.');
        } catch (Exception $exception) {
            // Log the error for debugging purposes
            Log::error('User registration failed: ' . $exception->getMessage());

            // Determine the appropriate status code
            $statusCode = ($exception->getCode() >= 100 && $exception->getCode() <= 599) ? $exception->getCode() : 500;

            // Return a standardized error response
            return $this->responseError($exception->getMessage(), $statusCode);
        }
    }
}