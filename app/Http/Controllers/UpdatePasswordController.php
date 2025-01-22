<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Repositories\AuthRepository;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;

class UpdatePasswordController extends Controller
{
    use ResponseTrait;

    protected $auth;

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
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password updated successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     )
     * )
     */
    public function update(UpdatePasswordRequest $request): JsonResponse
    {
        try {
            $data = $request->only(['password']);

            // Update the password using the AuthRepository
            $response = $this->auth->updatePassword($data, $request->user()->id);

            return $this->responseSuccess($response, 'Password updated successfully.');
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage());
        }
    }
}