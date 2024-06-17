<?php

return [
    'include' => [
        // __DIR__ . '/../app/core/helpers', 
        __DIR__ . '/../app/helpers',
        // __DIR__ . '/../boot'

        __DIR__ . '/../app/core/helpers/system.php',
        __DIR__ . '/../app/core/helpers/credits.php',
        __DIR__ . '/../app/core/helpers/http.php',
        __DIR__ . '/../app/core/helpers/url.php',        
        __DIR__ . '/../app/core/helpers/wp_urls.php',
        __DIR__ . '/../app/core/helpers/wp_db.php',
        __DIR__ . '/../app/core/helpers/factories.php',
        __DIR__ . '/../app/core/helpers/view.php',
        __DIR__ . '/../app/core/helpers/db.php',
    ],

    'exclude' => [
        __DIR__ . '/../app/core/helpers/cli.php',
    ]
];