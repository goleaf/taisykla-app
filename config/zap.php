<?php

use Carbon\CarbonInterface;

return [
    /*
    |--------------------------------------------------------------------------
    | Calendar Configuration
    |--------------------------------------------------------------------------
    |
    | Settings related to calendar display and behavior.
    |
    */
    'calendar' => [
        'week_start' => CarbonInterface::MONDAY,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Schedule Rules
    |--------------------------------------------------------------------------
    |
    | These are the default validation rules that will be applied to all
    | schedules unless overridden during creation.
    |
    */
    'default_rules' => [
        'no_overlap' => [
            'enabled' => true,
            'applies_to' => [
                // Which schedule types get this rule automatically
                \Zap\Enums\ScheduleTypes::APPOINTMENT,
                \Zap\Enums\ScheduleTypes::BLOCKED,
            ],
        ],
        'working_hours' => [
            'enabled' => true, // Enable working hours validation
            'start' => '08:00', // Taisykla start time
            'end' => '18:00',   // Taisykla end time
        ],
        'max_duration' => [
            'enabled' => true,
            'minutes' => 480, // Maximum 8 hours per period
        ],
        'no_weekends' => [
            'enabled' => true, // No weekend appointments by default
            'saturday' => true,
            'sunday' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Conflict Detection
    |--------------------------------------------------------------------------
    |
    | Configure how schedule conflicts are detected and handled.
    |
    */
    'conflict_detection' => [
        'enabled' => true,
        'buffer_minutes' => 15, // 15-minute buffer between appointments
    ],

    /*
    |--------------------------------------------------------------------------
    | Time Slots Configuration
    |--------------------------------------------------------------------------
    |
    | Default settings for time slot generation and availability checking.
    |
    */
    'time_slots' => [
        'buffer_minutes' => 15, // 15-minute buffer between sessions
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Configure custom validation rules and their settings.
    |
    */
    'validation' => [
        'require_future_dates' => true, // Schedules must be in the future
        'max_date_range' => 365, // Maximum days between start and end date
        'min_period_duration' => 15, // Minimum 15-minute appointments
        'max_periods_per_schedule' => 50, // Maximum periods per schedule
        'allow_overlapping_periods' => false, // No overlapping periods within same schedule
    ],

];
