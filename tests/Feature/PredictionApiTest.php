<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Prediction;

class PredictionApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the predict endpoint with valid data.
     */
    public function test_predict_endpoint_returns_successful_response(): void
    {
        $response = $this->postJson('/api/predict', [
            'square_footage' => 1500,
            'bedrooms' => 3,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'predicted_price',
                'prediction_id',
            ]);
    }

    /**
     * Test prediction is stored in the database.
     */
    public function test_prediction_is_stored_in_database(): void
    {
        $this->postJson('/api/predict', [
            'square_footage' => 2000,
            'bedrooms' => 4,
        ]);

        $this->assertDatabaseHas('predictions', [
            'square_footage' => 2000,
            'bedrooms' => 4,
        ]);
    }

    /**
     * Test validation fails when square footage is missing.
     */
    public function test_predict_fails_without_square_footage(): void
    {
        $response = $this->postJson('/api/predict', [
            'bedrooms' => 3,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['square_footage']);
    }

    /**
     * Test validation fails when bedrooms is missing.
     */
    public function test_predict_fails_without_bedrooms(): void
    {
        $response = $this->postJson('/api/predict', [
            'square_footage' => 1500,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bedrooms']);
    }

    /**
     * Test validation fails when square footage is below minimum.
     */
    public function test_predict_fails_with_square_footage_below_minimum(): void
    {
        $response = $this->postJson('/api/predict', [
            'square_footage' => 50,
            'bedrooms' => 3,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['square_footage']);
    }

    /**
     * Test validation fails when bedrooms exceeds maximum.
     */
    public function test_predict_fails_with_bedrooms_above_maximum(): void
    {
        $response = $this->postJson('/api/predict', [
            'square_footage' => 1500,
            'bedrooms' => 15,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bedrooms']);
    }

    /**
     * Test the history endpoint returns empty array when no predictions.
     */
    public function test_history_endpoint_returns_empty_when_no_predictions(): void
    {
        $response = $this->getJson('/api/history');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'predictions' => [],
            ]);
    }

    /**
     * Test the history endpoint returns predictions.
     */
    public function test_history_endpoint_returns_predictions(): void
    {
        // Create some predictions
        Prediction::create([
            'square_footage' => 1500,
            'bedrooms' => 3,
            'predicted_price' => 250000.00,
        ]);

        Prediction::create([
            'square_footage' => 2000,
            'bedrooms' => 4,
            'predicted_price' => 320000.00,
        ]);

        $response = $this->getJson('/api/history');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(2, 'predictions')
            ->assertJsonStructure([
                'success',
                'predictions' => [
                    '*' => [
                        'id',
                        'square_footage',
                        'bedrooms',
                        'predicted_price',
                        'created_at',
                    ],
                ],
            ]);
    }

    /**
     * Test history returns predictions in descending order by created_at.
     */
    public function test_history_returns_predictions_in_descending_order(): void
    {
        $older = Prediction::create([
            'square_footage' => 1000,
            'bedrooms' => 2,
            'predicted_price' => 150000.00,
        ]);

        // Small delay to ensure different timestamps
        sleep(1);

        $newer = Prediction::create([
            'square_footage' => 2000,
            'bedrooms' => 4,
            'predicted_price' => 320000.00,
        ]);

        $response = $this->getJson('/api/history');

        $response->assertStatus(200);
        
        $predictions = $response->json('predictions');
        // Newer prediction should be first (descending order)
        $this->assertEquals($newer->id, $predictions[0]['id']);
        $this->assertEquals($older->id, $predictions[1]['id']);
    }

    /**
     * Test deleting a prediction.
     */
    public function test_can_delete_prediction(): void
    {
        $prediction = Prediction::create([
            'square_footage' => 1500,
            'bedrooms' => 3,
            'predicted_price' => 250000.00,
        ]);

        $response = $this->deleteJson("/api/predictions/{$prediction->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Prediction deleted.',
            ]);

        $this->assertDatabaseMissing('predictions', ['id' => $prediction->id]);
    }

    /**
     * Test deleting non-existent prediction returns 404.
     */
    public function test_delete_nonexistent_prediction_returns_404(): void
    {
        $response = $this->deleteJson('/api/predictions/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Prediction not found.',
            ]);
    }

    /**
     * Test rate limiting on predict endpoint.
     */
    public function test_predict_endpoint_is_rate_limited(): void
    {
        // Make 31 requests (limit is 30 per minute)
        for ($i = 0; $i < 30; $i++) {
            $this->postJson('/api/predict', [
                'square_footage' => 1500,
                'bedrooms' => 3,
            ]);
        }

        // The 31st request should be rate limited
        $response = $this->postJson('/api/predict', [
            'square_footage' => 1500,
            'bedrooms' => 3,
        ]);

        $response->assertStatus(429);
    }
}

