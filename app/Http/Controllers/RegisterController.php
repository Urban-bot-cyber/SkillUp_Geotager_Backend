<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Repositories\AuthRepository;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RegisterController extends Controller
{
    use ResponseTrait;

    protected AuthRepository $auth;

    public function __construct(AuthRepository $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Register a new user via JSON request.
     *
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Authentication"},
     *     summary="Register",
     *     description="Register a new user with an optional base64 profile picture",
     *     operationId="register",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="first_name", type="string", example="Jon"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@gmail.com"),
     *             @OA\Property(property="password", type="string", format="password", example="Test123!"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="Test123!"),
     *             @OA\Property(property="profile_picture", type="string", description="Base64 encoded image", example="data:image/png;base64,iVBORw0KGgo..."),
     *         )
     *     ),
     *     @OA\Response(response=200, description="User registered successfully"),
     *     @OA\Response(response=400, description="Invalid input"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            // Collect user input (JSON format)
            $data = $request->only([
                'first_name',
                'last_name',
                'email',
                'password',
            ]);
            Log::info('Raw request data:', $data);
            // Fix password confirmation key
            $data['password_confirmation'] = $request->input('password_confirmation');

            // Handle Base64 Profile Picture (if present)
            if ($request->filled('profile_picture')) {
                $profilePicture = $request->input('profile_picture');

                // Validate and store the image
                if (preg_match('/^data:image\/(\w+);base64,/', $profilePicture, $matches)) {
                    $imageType = $matches[1];
                    $imageData = substr($profilePicture, strpos($profilePicture, ',') + 1);
                    $imageData = base64_decode($imageData);

                    if ($imageData === false) {
                        throw new Exception('Invalid profile picture format.');
                    }

                    // Generate file path
                    $fileName = uniqid('profile_') . '.' . $imageType;
                    $filePath = "profile_pictures/{$fileName}";

                    // Store image in `storage/app/public/profile_pictures`
                    Storage::disk('public')->put($filePath, $imageData);

                    // Save path to database
                    $data['profile_picture'] = $filePath;
                } else {
                    throw new Exception('Invalid profile picture format.');
                }
            }

            // Register user in database
            $userData = $this->auth->register($data);

            return $this->responseSuccess($userData, 'User registered successfully.');
        } catch (Exception $exception) {
            Log::error('User registration failed: ' . $exception->getMessage());

            $statusCode = ($exception->getCode() >= 100 && $exception->getCode() <= 599) ? $exception->getCode() : 500;

            return $this->responseError($exception->getMessage(), $statusCode);
        }
    }
}