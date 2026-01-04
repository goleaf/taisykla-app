<?php

return [
    'cache_ttl' => 300,
    'cache_prefix' => 'reports',
    'export' => [
        'formats' => ['csv', 'json', 'xml', 'xlsx', 'pdf'],
        'max_sync_rows' => 750,
        'storage_disk' => 'local',
    ],
    'costs' => [
        'labor_rate_per_hour' => 85,
        'travel_rate_per_hour' => 35,
        'overhead_rate' => 0.15,
    ],
    'predictive' => [
        'lookback_months' => 6,
        'forecast_periods' => 3,
        'anomaly_zscore' => 2.0,
    ],
];
