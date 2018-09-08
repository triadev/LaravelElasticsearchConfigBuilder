<?php

return [
    'refresh_interval' => "30s",
    'analysis' => [
        'filter' => [
            'germanStop' => [
                'type' => 'stop',
                'stopwords' => '_german_'
            ]
        ],
        'analyzer' => [
            'phpunitAnalyzer' => [
                'type' => 'custom',
                'tokenizer' => 'standard',
                'filter' => [
                    'FILTER-NOT-FOUND'
                ]
            ]
        ]
    ]
];
