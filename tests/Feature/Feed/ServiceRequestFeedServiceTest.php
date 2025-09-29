<?php

namespace Tests\Feature\Feed;

use App\Events\FeedUpdated;
use App\Http\Requests\API\Feed\ServiceRequestFeedRequest;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\Feed\ServiceRequestFeedService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ServiceRequestFeedServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        ServiceRequest::unsetEventDispatcher();
        ServiceRequest::flushEventListeners();
    }

    public function test_feed_filters_by_location_and_flushes_cache_on_update(): void
    {
        Event::fake([FeedUpdated::class]);

        $provider = User::factory()->create([
            'status' => 1,
            'type' => 'provider',
        ]);

        $near = ServiceRequest::factory()->create([
            'latitude' => 40.7128,
            'longitude' => -74.0060,
        ]);

        $far = ServiceRequest::factory()->create([
            'latitude' => 34.0522,
            'longitude' => -118.2437,
        ]);

        $service = app(ServiceRequestFeedService::class);

        $request = ServiceRequestFeedRequest::createFromBase(Request::create('/api/feed/service-requests', 'GET', [
            'per_page' => 50,
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'radius' => 10,
        ]));
        $request->setContainer(app());
        $request->setUserResolver(fn () => $provider);

        $firstResult = $service->serviceRequests($request);
        $this->assertCount(1, $firstResult->items());
        $this->assertTrue($firstResult->items()[0]->is($near));

        $far->update([
            'latitude' => 40.7130,
            'longitude' => -74.0050,
        ]);

        Event::assertDispatched(FeedUpdated::class);

        $secondResult = $service->serviceRequests($request);
        $this->assertCount(2, $secondResult->items());
    }
}
