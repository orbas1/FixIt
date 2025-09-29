<?php

namespace Tests\Feature\Feed;

use App\Models\Bid;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceRequestFeedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_paginated_service_requests(): void
    {
        ServiceRequest::factory()->count(3)->has(Bid::factory()->count(2))->create();

        $response = $this->getJson('/api/feed/service-requests?per_page=2');

        $response->assertOk()
            ->assertJson(
                [
                    'success' => true,
                    'meta' => [
                        'current_page' => 1,
                        'per_page' => 2,
                        'last_page' => 2,
                        'total' => 3,
                    ],
                ]
            );

        $this->assertCount(2, $response->json('data'));
        $this->assertArrayHasKey('bids', $response->json('data')[0]);
        $this->assertArrayHasKey('location', $response->json('data')[0]);
    }

    /** @test */
    public function it_filters_by_location_radius(): void
    {
        $nearby = ServiceRequest::factory()->create([
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'location_coordinates' => ['lat' => 40.7128, 'lng' => -74.0060],
        ]);

        ServiceRequest::factory()->create([
            'latitude' => 34.0522,
            'longitude' => -118.2437,
            'location_coordinates' => ['lat' => 34.0522, 'lng' => -118.2437],
        ]);

        $response = $this->getJson('/api/feed/service-requests?latitude=40.713&longitude=-74.006&radius=20');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertSame($nearby->id, $data[0]['id']);
        $this->assertNotNull($data[0]['distance_km']);
    }
}
