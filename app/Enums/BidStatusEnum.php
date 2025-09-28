<?php

namespace App\Enums;

enum BidStatusEnum:string {
  const REQUESTED = 'requested';
  const ACCEPTED = 'accepted';
  const REJECTED = 'rejected';
}
