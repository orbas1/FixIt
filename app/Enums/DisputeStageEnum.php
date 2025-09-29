<?php

namespace App\Enums;

enum DisputeStageEnum: string
{
    const DRAFT = 'draft';
    const EVIDENCE = 'evidence_collection';
    const MEDIATION = 'mediation';
    const ARBITRATION = 'awaiting_resolution';
    const RESOLVED = 'resolved';
    const CANCELLED = 'cancelled';
}
