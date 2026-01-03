<?php

return [
    'lockout' => [
        'attempts' => (int) env('AUTH_LOCKOUT_ATTEMPTS', 5),
        'minutes' => (int) env('AUTH_LOCKOUT_MINUTES', 15),
    ],
];
