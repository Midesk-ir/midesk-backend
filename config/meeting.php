<?php

return [
    'provider' => env('MEETING_PROVIDER', 'jitsi'),
    'jitsi_domain' => env('JITSI_DOMAIN', 'meet.jit.si'),
    'jitsi_app_id' => env('JITSI_APP_ID'),
    'jitsi_secret' => env('JITSI_APP_SECRET'),
    'bbb_url' => env('BBB_SERVER_URL'),
    'bbb_secret' => env('BBB_SECRET'),
];
