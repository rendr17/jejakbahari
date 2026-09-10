<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Validation\ValidationException;

trait ApiResponse
{
    protected function success(mixed $data = null, int $status = 200, array $meta = []): JsonResponse
    {
        $payload = ['success' => true];

        if ($data instanceof ResourceCollection) {
            $payload['data'] = $data->response()->getData(true)['data'];
            $payload['meta'] = $data->response()->getData(true)['meta'] ?? [];
        } elseif ($data instanceof JsonResource) {
            $payload['data'] = $data->response()->getData(true)['data'] ?? $data;
        } elseif ($data !== null) {
            $payload['data'] = $data;
        }

        if (! empty($meta)) {
            $payload['meta'] = array_merge($payload['meta'] ?? [], $meta);
        }

        return response()->json($payload, $status);
    }

    protected function error(string $code, string $message, int $status = 400, mixed $details = null): JsonResponse
    {
        $payload = [
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details,
            ],
        ];

        return response()->json($payload, $status);
    }

    protected function validationError(ValidationException $e): JsonResponse
    {
        return $this->error(
            'VALIDATION_ERROR',
            'The given data was invalid.',
            422,
            $e->errors(),
        );
    }
}
