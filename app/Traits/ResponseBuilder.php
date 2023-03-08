<?php
namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ResponseBuilder
{
    /**
     * @param $message
     * @param int $status
     * @param string $ex
     * @return JsonResponse
     */
    public function sendError(string $message, int $statusCode = 500, string $ex= ''): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'ex' => $ex
        ], $statusCode);
    }

    /**
     * @param $data
     * @param string $message
     * @param array $extra
     * @return JsonResponse
     */
    public function sendSuccess($data = null, string $message = '', int $statusCode = 200, array $extra = []): JsonResponse
    {
        $responseTemp = [
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'extra' => $extra
        ];

        // Merge pagination object with response so result doesn't have to be accessed as $response->data->data
        if ((is_array($data) && array_key_exists('data', $data)) || (is_object($data) && isset($data->data))) {
            $responseTemp = array_merge($responseTemp, $data);
        } else {
            $responseTemp['data'] = $data;
        }

        return response()->json($responseTemp, $statusCode);
    }
}
