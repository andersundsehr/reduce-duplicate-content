<?php

/** @var string $_EXTKEY */
$EM_CONF[$_EXTKEY] = [
    'title' => 'reduce_duplicate_content',
    'description' => 'redirect if page has a / at the end (or not). (reduce Duplicate Content)',
    'constraints' => [
        'depends' => [
            'typo3' => '11.0.0-12.4.99',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'AUS\\ReduceDuplicateContent\\' => 'Classes/',
        ],
    ],
];
