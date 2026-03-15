<?php

return [
    'smtp_health' => [
        'cooldown_rules' => [
            [
                'failures' => 3,
                'minutes' => 10,
            ],
            [
                'failures' => 5,
                'minutes' => 30,
            ],
            [
                'failures' => 7,
                'minutes' => 60,
            ],
        ],

        'auto_disable_after_consecutive_failures' => 10,
    ],
];