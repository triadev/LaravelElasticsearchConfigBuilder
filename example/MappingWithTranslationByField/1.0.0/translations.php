<?php

return [
    'type' => 'field',
    'locales' => [
        'enUS'
    ],
    'fields' => [
        'example.properties.title'
    ],
    'configPerLocale' => [
        'example.properties.title' => [
            'enUS' => [
                'analyzer' => 'exampleAnalyzerEn'
            ],
            'enGB' => [
                'analyzer' => 'exampleAnalyzerEn'
            ]
        ]
    ]
];
