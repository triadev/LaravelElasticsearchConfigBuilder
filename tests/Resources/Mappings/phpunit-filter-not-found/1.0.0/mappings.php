<?php

return [
    'phpunit' => [
        'dynamic' => 'strict',
        'properties' => [
            'title' => [
                'type' => 'text',
                'analyzer' => 'phpunitAnalyzer'
            ]
        ]
    ]
];
