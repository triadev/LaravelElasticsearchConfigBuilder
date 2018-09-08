<?php

return [
    'refresh_interval' => "30s",
    'analysis' => [
        'filter' => [
            'germanStop' => [
                'type' => 'stop',
                'stopwords' => '_german_'
            ],
            'englishStop' => [
                'type' => 'stop',
                'stopwords' => '_english_'
            ]
        ],
        'analyzer' => [
            'phpunitAnalyzer' => [
                'type' => 'custom',
                'tokenizer' => 'standard',
                'filter' => [
                    'germanStop'
                ]
            ],
            'phpunitAnalyzerEn' => [
                'type' => 'custom',
                'tokenizer' => 'standard',
                'filter' => [
                    'englishStop'
                ]
            ]
        ]
    ]
];
