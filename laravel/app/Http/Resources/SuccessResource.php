<?php
namespace App\Http\Resources;

use Illuminate\Http\JsonResponse;

class SuccessResource
{
    public static function toJson($data, string $messageKey = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $messageKey,
            'data' => $data
        ], $status);
    }
}

