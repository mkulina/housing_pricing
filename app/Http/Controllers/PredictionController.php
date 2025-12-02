<?php

namespace App\Http\Controllers;

use App\Services\PredictionService;
use App\Models\Prediction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PredictionController extends Controller
{
    public function __construct(
        private PredictionService $predictionService
    ) {}

    /**
     * Make a price prediction based on house details.
     */
    public function predict(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'square_footage' => 'required|integer|min:100|max:10000',
            'bedrooms' => 'required|integer|min:1|max:10',
        ]);

        try {
            $result = $this->predictionService->predict(
                $validated['square_footage'],
                $validated['bedrooms']
            );

            return response()->json([
                'success' => true,
                'predicted_price' => $result['formatted_price'],
                'prediction_id' => $result['prediction_id'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Prediction failed. Please try again.',
            ], 500);
        }
    }

    /**
     * Get prediction history.
     */
    public function history(): JsonResponse
    {
        $predictions = $this->predictionService->getHistory(20);

        return response()->json([
            'success' => true,
            'predictions' => $predictions->map(function ($p) {
                return [
                    'id' => $p->id,
                    'square_footage' => number_format($p->square_footage),
                    'bedrooms' => $p->bedrooms,
                    'predicted_price' => '$' . number_format($p->predicted_price, 2),
                    'created_at' => $p->created_at->toISOString(),
                ];
            }),
        ]);
    }

    /**
     * Delete a prediction.
     */
    public function destroy(int $id): JsonResponse
    {
        $prediction = Prediction::find($id);

        if (!$prediction) {
            return response()->json([
                'success' => false,
                'message' => 'Prediction not found.',
            ], 404);
        }

        $prediction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Prediction deleted.',
        ]);
    }
}
