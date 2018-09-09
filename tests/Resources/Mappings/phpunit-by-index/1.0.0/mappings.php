<?php

return [
    'phpunit' => [
        'dynamic' => 'strict',
        'properties' => [
            'title' => [
                'type' => 'text',
                'analyzer' => 'phpunitAnalyzer'
            ],
            'link' => [
                'type' => 'keyword',
                'doc_values' => false
            ],
            'images' => [
                'type' => 'nested',
                'properties' => [
                    'title' => [
                        'type' => 'text',
                        'analyzer' => 'phpunitAnalyzer',
                        'search_analyzer' => 'phpunitAnalyzer'
                    ]
                ]
            ]
        ]
    ]
];
