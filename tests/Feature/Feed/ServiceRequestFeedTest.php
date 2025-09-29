<?php

namespace Tests\Feature\Feed;

use App\Models\ServiceRequest;
use App\Services\Feed\ServiceRequestFeedService;
use App\Http\Requests\API\Feed\ServiceRequestFeedRequest;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceRequestFeedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        ServiceRequest::unsetEventDispatcher();
        ServiceRequest::flushEventListeners();
    }

    /** @test */
    public function it_returns_paginated_service_requests(): void
    {
        ServiceRequest::factory()->count(3)->create();

        $service = app(ServiceRequestFeedService::class);
        $request = ServiceRequestFeedRequest::createFromBase(HttpRequest::create(
            '/feed/service-requests',
            'GET',
            ['per_page' => 2]
        ));
        $request->setContainer(app());

        $paginator = $service->serviceRequests($request);

        $this->assertSame(2, $paginator->perPage());
        $this->assertSame(3, $paginator->total());
        $this->assertCount(2, $paginator->items());
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

        $service = app(ServiceRequestFeedService::class);
        $request = ServiceRequestFeedRequest::createFromBase(HttpRequest::create(
            '/feed/service-requests',
            'GET',
            [
                'latitude' => 40.713,
                'longitude' => -74.006,
                'radius' => 20,
            ]
        ));
        $request->setContainer(app());

        $paginator = $service->serviceRequests($request);
        $items = $paginator->items();

        $this->assertCount(1, $items);
        $this->assertTrue($items[0]->is($nearby));
        $this->assertNotNull($items[0]->distance_km ?? null);
    }
}
