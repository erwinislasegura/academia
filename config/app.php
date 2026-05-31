<?php

return [
    'name' => 'Academia Iquique',
    'url' => getenv('APP_URL') ?: 'http://localhost:8000',
    'timezone' => 'America/Santiago',
    'debug' => filter_var(getenv('APP_DEBUG') ?: true, FILTER_VALIDATE_BOOLEAN),
];
