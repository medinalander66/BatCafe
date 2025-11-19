<?php
// config.php
declare(strict_types=1);

return (object)[
    // Database
    'db' => (object)[
        'host' => 'localhost',
        'dbname' => 'batcavecafez',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    // Business rates and constants (centralized)
    'rates' => (object)[
        // equipment codes => per-hour fee
        'equipment' => [
            'PROJECTOR'   => 150.00,
            'SPEAKER_MIKE'=> 150.00,
        ],
    ],
    // Misc config
    'site' => (object)[
        'timezone' => 'Asia/Manila',
    ],
];
