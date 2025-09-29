<?php

return [
    'sla_hours' => [
        'evidence_collection' => (int) env('DISPUTE_EVIDENCE_SLA_HOURS', 48),
        'mediation' => (int) env('DISPUTE_MEDIATION_SLA_HOURS', 72),
        'awaiting_resolution' => (int) env('DISPUTE_ARBITRATION_SLA_HOURS', 96),
    ],
];
