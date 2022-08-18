<?php

use Moonpie\Tp6Plugin\LoginGuard\Contracts\LimitConfigInterface;

return [
    LimitConfigInterface::KEY_ACCOUNT_TEMP_LOCK => [
        'minutes'      => 2,
        'times'        => 5,
        'lock_minutes' => 60,
    ],
    LimitConfigInterface::KEY_ACCOUNT_FOREVER_LOCK=> [
        'times' => 3,
    ],
    LimitConfigInterface::KEY_IP_TEMP_LOCK=> [
        'minutes'      => 2,
        'times'        => 5,
        'lock_minutes' => 60,
    ],
];
