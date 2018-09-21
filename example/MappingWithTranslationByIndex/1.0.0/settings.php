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
            'exampleAnalyzer' => [
                'type' => 'custom',
                'tokenizer' => 'standard',
                'filter' => [
                    'germanStop'
                ]
            ],
            'exampleAnalyzerEn' => [
                'type' => 'custom',
                'tokenizer' => 'standard',
                'filter' => [
                    'englishStop'
                ]
            ]
        ]
    ]
];
