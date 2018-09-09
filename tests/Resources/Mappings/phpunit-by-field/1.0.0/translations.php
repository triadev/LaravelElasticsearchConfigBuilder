<?php

return [
    'type' => 'field',
    'locales' => [
        'enUS'
    ],
    'fields' => [
        'phpunit.properties.title',
        'phpunit.properties.link',
        'phpunit.properties.images'
    ],
    'configPerLocale' => [
        'phpunit.properties.title' => [
            'enUS' => [
                'analyzer' => 'phpunitAnalyzerEn'
            ],
            'enGB' => [
                'analyzer' => 'phpunitAnalyzerEn'
            ]
        ],
        'phpunit.properties.images' => [
            'enUS' => [
                'properties' => [
                    'title' => [
                        'analyzer' => 'phpunitAnalyzerEn',
                        'search_analyzer' => 'phpunitAnalyzerEn'
                    ]
                ]
            ]
        ]
    ]
];
