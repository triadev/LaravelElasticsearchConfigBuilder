<?php
namespace Tests;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Triadev\EsConfigBuilder\Contract\ElasticsearchConfigBuilderContract;

class ElasticsearchConfigTest extends TestCase
{
    /** @var ElasticsearchConfigBuilderContract */
    private $service;
    
    /** @var Client */
    private $esClient;
    
    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->service = app(ElasticsearchConfigBuilderContract::class);
    
        $clientBuilder = ClientBuilder::create();
        $clientBuilder->setHosts([
            [
                'host' => env('ELASTICSEARCH_HOST', 'localhost'),
                'port' => env('ELASTICSEARCH_PORT', 9222),
                'scheme' => 'http'
            ]
        ]);
    
        $this->esClient = $clientBuilder->build();
    
        $this->esClient->indices()->delete([
            'index' => '_all'
        ]);
    }
    
    /**
     * @test
     */
    public function it_returns_a_elasticsearch_mapping_translated_by_field()
    {
        $mappings = $this->service->getMappings('phpunit-by-field', '1.0.0');
        
        $this->assertEquals([
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
        ], $mappings['phpunit-by-field']['settings']);
    
        $this->assertEquals([
            'phpunit' => [
                'dynamic' => 'strict',
                'properties' => [
                    'title' => [
                        'type' => 'text',
                        'analyzer' => 'phpunitAnalyzer'
                    ],
                    'title_enUS' => [
                        'type' => 'text',
                        'analyzer' => 'phpunitAnalyzerEn'
                    ],
                    'title_enGB' => [
                        'type' => 'text',
                        'analyzer' => 'phpunitAnalyzerEn'
                    ],
                    'title_deAT' => [
                        'type' => 'text',
                        'analyzer' => 'phpunitAnalyzer'
                    ],
                    'title_deCH' => [
                        'type' => 'text',
                        'analyzer' => 'phpunitAnalyzer'
                    ],
                    'link' => [
                        'type' => 'keyword',
                        'doc_values' => false
                    ],
                    'link_enUS' => [
                        'type' => 'keyword',
                        'doc_values' => false
                    ],
                    'link_enGB' => [
                        'type' => 'keyword',
                        'doc_values' => false
                    ],
                    'link_deAT' => [
                        'type' => 'keyword',
                        'doc_values' => false
                    ],
                    'link_deCH' => [
                        'type' => 'keyword',
                        'doc_values' => false
                    ],
                    'images' => [
                        'type' => 'nested',
                        'properties' => [
                            'title' => [
                                'analyzer' => 'phpunitAnalyzer',
                                'search_analyzer' => 'phpunitAnalyzer'
                            ]
                        ]
                    ],
                    'images_deAT' => [
                        'type' => 'nested',
                        'properties' => [
                            'title' => [
                                'analyzer' => 'phpunitAnalyzer',
                                'search_analyzer' => 'phpunitAnalyzer'
                            ]
                        ]
                    ],
                    'images_deCH' => [
                        'type' => 'nested',
                        'properties' => [
                            'title' => [
                                'analyzer' => 'phpunitAnalyzer',
                                'search_analyzer' => 'phpunitAnalyzer'
                            ]
                        ]
                    ],
                    'images_enUS' => [
                        'type' => 'nested',
                        'properties' => [
                            'title' => [
                                'analyzer' => 'phpunitAnalyzerEn',
                                'search_analyzer' => 'phpunitAnalyzerEn'
                            ]
                        ]
                    ],
                    'images_enGB' => [
                        'type' => 'nested',
                        'properties' => [
                            'title' => [
                                'analyzer' => 'phpunitAnalyzerEn',
                                'search_analyzer' => 'phpunitAnalyzerEn'
                            ]
                        ]
                    ]
                ]
            ]
        ], $mappings['phpunit-by-field']['mappings']);
    }
}
