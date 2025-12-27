<?php

namespace App\Traits;

trait ApiResponseTrait
{
    protected function successResponse($data = null, $message = null, $code = 200)
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    protected function errorResponse($message, $code = 400)
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
            'data'    => null,
        ], $code);
    }

    protected function lang(array $messages)
    {
        $lang = auth()->user()->authLanguage->language ?? 'uz';
        return $messages[$lang] ?? $messages['uz'];
    }
}
