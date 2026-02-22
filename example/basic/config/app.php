<?php

return [
    'name' => 'AurexEngine App',
    'env' => 'local',
    'debug' => filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOL),
];