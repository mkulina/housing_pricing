<?php

namespace App\Services;

use App\Models\Prediction;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Log;

class PredictionService
{
    private string $pythonPath = 'python3';
    private string $scriptPath;

    public function __construct()
    {
        $this->scriptPath = base_path('ml/src/predict.py');
    }

    /**
     * Make a price prediction for the given inputs.
     */
    public function predict(int $squareFootage, int $bedrooms): array
    {
        $predictedPrice = $this->callPythonModel($squareFootage, $bedrooms);
        
        $prediction = $this->storePrediction($squareFootage, $bedrooms, $predictedPrice);

        return [
            'predicted_price' => $predictedPrice,
            'formatted_price' => number_format($predictedPrice, 2, '.', ','),
            'prediction_id' => $prediction->id,
        ];
    }

    /**
     * Call the Python ML model for prediction.
     */
    private function callPythonModel(int $squareFootage, int $bedrooms): float
    {
        $process = new Process([
            $this->pythonPath,
            $this->scriptPath,
            $squareFootage,
            $bedrooms
        ]);

        $process->run();

        if (!$process->isSuccessful()) {
            Log::error('Prediction failed', [
                'error' => $process->getErrorOutput(),
                'square_footage' => $squareFootage,
                'bedrooms' => $bedrooms,
            ]);
            throw new ProcessFailedException($process);
        }

        return floatval(trim($process->getOutput()));
    }

    /**
     * Store the prediction in the database.
     */
    private function storePrediction(int $squareFootage, int $bedrooms, float $predictedPrice): Prediction
    {
        return Prediction::create([
            'square_footage' => $squareFootage,
            'bedrooms' => $bedrooms,
            'predicted_price' => $predictedPrice,
        ]);
    }

    /**
     * Get prediction history.
     */
    public function getHistory(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Prediction::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}

