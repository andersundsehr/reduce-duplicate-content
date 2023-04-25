<?php

use AUS\ReduceDuplicateContent\Middleware\ReduceDuplicateContentMiddleware;

return [
    'frontend' => [
        'a-u-s/reduce-duplicate-content/reduce-duplicate-content-middleware' => [
            'target' => ReduceDuplicateContentMiddleware::class,
            'after' => [
                'typo3/cms-frontend/base-redirect-resolver',
                'typo3/cms-frontend/static-route-resolver',
                'typo3/cms-frontend/page-resolver',
            ],
            'before' => [
                'typo3/cms-frontend/preview-simulator',
                'typo3/cms-frontend/page-argument-validator',
                'typo3/cms-frontend/tsfe',
            ]
        ],
    ],
];
