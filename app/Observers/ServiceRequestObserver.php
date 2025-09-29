<?php

namespace App\Observers;

use App\Events\FeedUpdated;
use App\Http\Resources\Feed\ServiceRequestFeedResource;
use App\Models\SearchSnapshot;
use App\Models\ServiceRequest;
use App\Services\Feed\ServiceRequestFeedService;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class ServiceRequestObserver
{
    public function saved(ServiceRequest $serviceRequest): void
    {
        $serviceRequest->loadMissing([
            'user.media',
            'service:id,title,price,user_id',
            'zones:id,name',
            'bids' => function ($builder) {
                $builder->latest('created_at')
                    ->select(['id', 'service_request_id', 'provider_id', 'amount', 'status', 'created_at'])
                    ->with(['provider:id,name']);
            },
        ]);

        $httpRequest = HttpRequest::createFromBase(SymfonyRequest::create('/feed/service-requests'));

        $document = (new ServiceRequestFeedResource($serviceRequest))
            ->toArray($httpRequest);

        SearchSnapshot::updateOrCreate(
            [
                'subject_type' => ServiceRequest::class,
                'subject_id' => $serviceRequest->id,
            ],
            ['document' => $document]
        );

        $this->flushFeedCache();

        event(new FeedUpdated(
            $serviceRequest->wasRecentlyCreated ? 'created' : 'updated',
            ServiceRequest::class,
            $serviceRequest->id,
            $document
        ));
    }

    public function deleted(ServiceRequest $serviceRequest): void
    {
        SearchSnapshot::where([
            'subject_type' => ServiceRequest::class,
            'subject_id' => $serviceRequest->id,
        ])->delete();

        $this->flushFeedCache();

        event(new FeedUpdated(
            'deleted',
            ServiceRequest::class,
            $serviceRequest->id
        ));
    }

    protected function flushFeedCache(): void
    {
        Cache::forget(ServiceRequestFeedService::CACHE_VERSION_KEY);
    }
}
