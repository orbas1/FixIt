<?php

namespace App\Enums;

enum CustomOfferEnum:string {
  const REQUESTED = 'requested';
  const ACCEPTED = 'accepted';
  const REJECTED = 'rejected';
  const EXPIRED = 'expired';
}
