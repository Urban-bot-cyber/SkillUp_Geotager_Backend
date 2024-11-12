<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Repositories\AuthRepository;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    use ResponseTrait;

    protected $auth;

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
     *                     example="john.doe@gmail.com"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     description="User password",
     *                     type="string",
     *                     example="Test123!"
     *                 ),
     *                 @OA\Property(
     *                     property="password_confirmation",
     *                     description="Confirm password",
     *                     type="string",
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
     *         description="User registered successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     )
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            // Collecting form inputs along with the profile picture file
            $data = $request->only([
                'first_name',
                'last_name',
                'email',
                'password',
                'password_confirmation',
                'profile_picture'
            ]);

            $response = $this->auth->register($data);

            return $this->responseSuccess($response, 'User registered successfully.');
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage());
        }
    }
}
