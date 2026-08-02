<?php

namespace App\Domains\Shared\Presentation\Http;

use Illuminate\Http\JsonResponse;

/**
 * API Standards (pamora-foundation/04-engineering/07-api-specification.md) mandate a
 * {success, data} envelope for every successful API response. This is the one place
 * that shape is built, so every domain's API controller stays consistent by
 * construction instead of by convention. Error responses are handled centrally in
 * bootstrap/app.php's exception renderer — controllers only ever build success shapes.
 */
class ApiResponse
{
    public static function success(mixed $data = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
        ], $status);
    }
}
