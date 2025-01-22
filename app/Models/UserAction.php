<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="UserAction",
 *     type="object",
 *     required={"id", "user_id", "action", "component_type", "new_value", "url", "created_at", "updated_at"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="The unique identifier of the user action",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         description="The ID of the user who performed the action",
 *         example=123
 *     ),
 *     @OA\Property(
 *         property="action",
 *         type="string",
 *         description="The action performed by the user",
 *         example="create"
 *     ),
 *     @OA\Property(
 *         property="component_type",
 *         type="string",
 *         description="The type of component involved in the action",
 *         example="post"
 *     ),
 *     @OA\Property(
 *         property="new_value",
 *         type="string",
 *         description="The new value after the action",
 *         example="New post content"
 *     ),
 *     @OA\Property(
 *         property="url",
 *         type="string",
 *         format="url",
 *         description="The URL related to the action",
 *         example="https://example.com/posts/1"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp when the action was created",
 *         example="2025-01-20T13:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp when the action was last updated",
 *         example="2025-01-20T13:00:00Z"
 *     )
 * )
 */
class UserAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'component_type',
        'new_value',
        'url',
    ];

    /**
     * Relationship with User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}