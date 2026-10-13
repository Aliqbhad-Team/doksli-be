<?php

return [
    'secret' => env('JWT_SECRET', env('APP_KEY')),
    'ttl' => (int) env('JWT_TTL', 60), // minutes
    'refresh_ttl' => (int) env('JWT_REFRESH_TTL', 20160), // 14 days
    'algo' => 'HS256',
    'leeway' => 0,
];
