<?php

namespace App\Http\Controllers;

use App\Models\UserAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserActionController extends Controller
{
    /**
     * Store user action.
     *
     * @OA\Post(
     *     path="/api/user-actions",
     *     tags={"User Actions"},
     *     summary="Log a user action",
     *     description="Stores a user action such as clicks, scrolls, or input changes.",
     *     operationId="storeUserAction",
     *     security={{"bearer":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"action", "url"},
     *             @OA\Property(property="action", type="string", description="Type of user action (e.g., click, scroll, input change)"),
     *             @OA\Property(property="component_type", type="string", description="Component type (e.g., link, button, input type)"),
     *             @OA\Property(property="new_value", type="string", description="New value entered (if applicable)"),
     *             @OA\Property(property="url", type="string", description="URL where the action occurred")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Action logged successfully"
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function store(Request $request)
    {
        // Validate the request
        $validatedData = $request->validate([
            'action' => 'required|string',
            'component_type' => 'nullable|string',
            'new_value' => 'nullable|string',
            'url' => 'required|string|url', // Ensures the URL format is valid
        ]);

        // Create the action log
        $action = UserAction::create([
            'user_id' => Auth::id(), // Get the authenticated user ID or null for guests
            'action' => $validatedData['action'],
            'component_type' => $validatedData['component_type'] ?? null,
            'new_value' => $validatedData['new_value'] ?? null,
            'url' => $validatedData['url'],
        ]);

        // Return a JSON response
        return response()->json([
            'message' => 'Action logged successfully.',
            'data' => $action,
        ], 201); // Status 201 Created
    }

    /**
     * Retrieve the last 100 user actions (admin only).
     *
     * @OA\Get(
     *     path="/api/admin/user-actions",
     *     tags={"User Actions"},
     *     summary="Get the last 100 user actions",
     *     description="Retrieves the most recent 100 logged user actions. Restricted to admin users.",
     *     operationId="getRecentUserActions",
     *     security={{"bearer":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of recent user actions",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/UserAction")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function recent()
    {
        // Retrieve the latest 100 actions
        $actions = UserAction::with('user')->latest()->limit(100)->get();

        // Return the actions in a JSON response
        return response()->json([
            'message' => 'Recent user actions retrieved successfully.',
            'data' => $actions,
        ], 200); // Status 200 OK
    }
}