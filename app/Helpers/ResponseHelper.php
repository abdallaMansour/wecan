<?php

namespace App\Helpers;

class ResponseHelper
{
    public static function success($message, $data = null)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], 200);
    }

    public static function error($message, $statusCode = 500, $data = null)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }
}
