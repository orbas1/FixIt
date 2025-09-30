<?php

namespace App\Observers;

use App\Models\Bid;
use App\Services\Feed\ServiceRequestFeedService;

class BidObserver
{
    public function __construct(private readonly ServiceRequestFeedService $feedService)
    {
    }

    public function saved(Bid $bid): void
    {
        $this->invalidateFeed($bid);
    }

    public function deleted(Bid $bid): void
    {
        $this->invalidateFeed($bid);
    }

    protected function invalidateFeed(Bid $bid): void
    {
        $bid->loadMissing('serviceRequest');
        $serviceRequest = $bid->serviceRequest;

        if ($serviceRequest === null) {
            return;
        }

        $buckets = ['global'];

        if ($serviceRequest->h3_index !== null) {
            $buckets[] = (string) $serviceRequest->h3_index;
        }

        $this->feedService->invalidateBuckets($buckets);
    }
}
