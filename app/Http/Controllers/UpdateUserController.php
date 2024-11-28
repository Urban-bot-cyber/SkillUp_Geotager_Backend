<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Repositories\AuthRepository;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class UpdateUserController extends Controller
{
    use ResponseTrait;

    protected $auth;

    public function __construct(AuthRepository $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @OA\Put(
     *     path="/api/update",
     *     tags={"Authentication"},
     *     summary="Update user profile",
     *     description="Update user details and optionally the profile picture",
     *     operationId="updateUser",
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
     *                 required={},
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User profile updated successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     )
     * )
     */
    public function update(UpdateUserRequest $request): JsonResponse
    {
        try {
            // Collecting form inputs along with the profile picture file
            $data = $request->only([
                'first_name',
                'last_name',
                'email',
                'password',
                'password_confirmation',
            ]);

            // Handle the profile picture upload if present
            if ($request->hasFile('profile_picture')) {
                // Store the profile picture and get the path
                $path = $request->file('profile_picture')->store('profile_pictures', 'public');
                $data['profile_picture'] = $path;  // Save the path to the database
            }

            // Update the user using the AuthRepository
            $response = $this->auth->update($data, $request->user()->id);

            return $this->responseSuccess($response, 'User profile updated successfully.');
        } catch (Exception $exception) {
            // Handle errors (including file upload issues)
            return $this->responseError([], $exception->getMessage());
        }
    }
}
