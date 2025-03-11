<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Repositories\AuthRepository;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UpdatePasswordController extends Controller
{
    use ResponseTrait;

    protected AuthRepository $auth;

    public function __construct(AuthRepository $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @OA\Put(
     *     path="/api/update-password",
     *     tags={"Authentication"},
     *     summary="Update user password",
     *     description="Update the user's password",
     *     operationId="updatePassword",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="current_password",
     *                 type="string",
     *                 example="CurrentPassword123!"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 example="NewPassword123!"
     *             ),
     *             @OA\Property(
     *                 property="password_confirmation",
     *                 type="string",
     *                 example="NewPassword123!"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password updated successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Passwords do not match."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        try {
            // 1. Verify the current password
            if (!Hash::check($request->input('current_password'), $request->user()->password)) {
                return $this->responseError([], 'Current password is incorrect.', 422);
            }

            // 2. Check if new password matches the confirmation
            if ($request->input('password') !== $request->input('password_confirmation')) {
                return $this->responseError([], 'Passwords do not match.', 422);
            }

            // 3. Update the password via AuthRepository
            $response = $this->auth->updatePassword(
                ['password' => $request->input('password')],
                $request->user()->id
            );

            return $this->responseSuccess($response, 'Password updated successfully.');
        } catch (Exception $exception) {
            // Return a generic error response (could log the exception as well)
            return $this->responseError([], $exception->getMessage());
        }
    }
}