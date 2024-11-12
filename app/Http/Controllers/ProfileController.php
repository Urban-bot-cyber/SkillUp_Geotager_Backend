<?php

namespace App\Http\Controllers;

use App\Repositories\AuthRepository;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    use ResponseTrait;

    protected $auth;

    public function __construct(AuthRepository $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @OA\Get(
     *     path="/api/profile",
     *     tags={"Profile"},
     *     summary="Get user profile",
     *     description="Fetch the authenticated user's profile information.",
     *     operationId="getProfile",
     *     security={{"bearer":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Profile data retrieved successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="User profile data!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function show(): JsonResponse
    {
        try {
            return $this->responseSuccess(Auth::guard()->user(), 'User profile data!');
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Authentication"},
     *     summary="User logout",
     *     description="Logs out the authenticated user.",
     *     operationId="logoutUser",
     *     security={{"bearer":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User logged out successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User logged out successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function logout(): JsonResponse
    {
        try {
            Auth::guard()->user()->token()->revoke();
            Auth::guard()->user()->token()->delete();
            return $this->responseSuccess('', 'User logged out successfully!');
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage());
        }
    }
}
