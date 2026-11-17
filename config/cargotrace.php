<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CargoTrace Configurable Delay Thresholds
    |--------------------------------------------------------------------------
    |
    | Defines the SLA tolerance intervals for maritime shipment delays
    | in hours (PROJECT_SPEC.md 3.3).
    |
    | - LOW: Minor delay, easily absorbable at terminal (0 - 6 hours)
    | - MEDIUM: Moderate delay requiring port schedule adjustment (6 - 24 hours)
    | - HIGH: Major delay disrupting downstream supply chain (> 24 hours)
    |
    */
    'delay_thresholds' => [
        'low' => [
            'min_hours' => 0,
            'max_hours' => 6,
            'label' => 'LOW',
        ],
        'medium' => [
            'min_hours' => 6,
            'max_hours' => 24,
            'label' => 'MEDIUM',
        ],
        'high' => [
            'min_hours' => 24,
            'label' => 'HIGH',
        ],
    ],
];
