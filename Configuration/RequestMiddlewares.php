<?php

use AUS\ReduceDuplicateContent\Middleware\ReduceDuplicateContentMiddleware;

return [
    'frontend' => [
        'a-u-s/reduce-duplicate-content/reduce-duplicate-content-middleware' => [
            'target' => ReduceDuplicateContentMiddleware::class,
            'after' => [
                'typo3/cms-frontend/page-resolver',
            ],
        ],
    ],
];
