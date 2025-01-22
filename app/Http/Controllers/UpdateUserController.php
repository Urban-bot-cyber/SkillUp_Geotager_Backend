<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Repositories\AuthRepository;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateUserController extends Controller
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
        $this->middleware('auth:api'); // Ensure the user is authenticated
    }

    /**
     * @OA\Put(
     *     path="/api/update",
     *     tags={"Authentication"},
     *     summary="Update user profile",
     *     description="Update user details and optionally the profile picture",
     *     operationId="updateUser",
     *     security={{"bearer":{}}},
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
     *                     format="password",
     *                     example="newpassword123"
     *                 ),
     *                 @OA\Property(
     *                     property="password_confirmation",
     *                     description="Confirm user password",
     *                     type="string",
     *                     format="password",
     *                     example="newpassword123"
     *                 ),
     *                 @OA\Property(
     *                     property="profile_picture",
     *                     description="Profile picture of the user",
     *                     type="string",
     *                     format="binary"
     *                 ),
     *                 required={}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User profile updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User profile updated successfully."),
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
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not found."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred while updating the profile."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function update(UpdateUserRequest $request): JsonResponse
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

            // Update the user using the AuthRepository
            $response = $this->auth->update($data, $request->user()->id);

            return $this->responseSuccess($response, 'User profile updated successfully.');
        } catch (Exception $exception) {
            // Log the error for debugging
            Log::error('User update failed: ' . $exception->getMessage());

            // Determine the appropriate status code
            $statusCode = ($exception->getCode() >= 100 && $exception->getCode() <= 599) ? $exception->getCode() : 500;

            // Return a standardized error response
            return $this->responseError($exception->getMessage(), $statusCode);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/users/{id}/add-points",
     *     tags={"Users"},
     *     summary="Add 10 points to a user",
     *     description="Adds 10 points to the user identified by the given user ID.",
     *     operationId="addPointsToUser",
     *     security={{"bearer":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user to award points",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Points added successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="10 points added to the user successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="new_points", type="integer", example=50)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid user ID."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You do not have permission to perform this action."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not found."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred while adding points."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function addPoints(int $id): JsonResponse
    {
        // Validate the user ID
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->responseError('Invalid user ID.', 400);
        }

        try {
            // Add points using the AuthRepository's addPoints method
            $pointsData = $this->auth->addPoints($id, 10); // Adds 10 points

            return $this->responseSuccess($pointsData, '10 points added to the user successfully.');
        } catch (Exception $e) {
            // Determine the appropriate status code
            $statusCode = ($e->getCode() >= 100 && $e->getCode() <= 599) ? $e->getCode() : 500;

            // Log the error for debugging
            Log::error('Failed to add points to user ID ' . $id . ': ' . $e->getMessage());

            // Return a standardized error response
            return $this->responseError('An error occurred while adding points.', $statusCode);
        }
    }
}