<?php

namespace App\Traits;

trait ApiResponse
{
    protected function sendResponse($data, $message = 'Success', $code = 200)
    {
        return response()->json([
            'status_code' => $code,
            'success'     => true,
            'message'     => $message,
            'data'        => $data,
        ], $code);
    }

    protected function sendError($error, $code = 404)
    {
        return response()->json([
            'status_code' => $code,
            'success'     => false,
            'message'     => $error,
        ], $code);
    }
}