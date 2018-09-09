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
                'port' => env('ELASTICSEARCH_PORT', 9200),
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
    public function it_creates_the_elasticsearch_mappings()
    {
        $mappingsToCreate = ['phpunit-by-field', 'phpunit-by-index'];
        
        foreach ($mappingsToCreate as $mappingToCreate) {
            $mappings = $this->service->getMappings($mappingToCreate);
            
            try {
                $this->esClient->indices()->create([
                    'index' => $mappingToCreate,
                    'body' => $mappings[$mappingToCreate]
                ]);
                
                $result = true;
            } catch (\Exception $e) {
                $result = false;
            }
            
            $this->assertTrue($result);
        }
        
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
                    'link' => [
                        'type' => 'keyword',
                        'doc_values' => false
                    ],
                    'link_enUS' => [
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
                    ],
                    'images_enUS' => [
                        'type' => 'nested',
                        'properties' => [
                            'title' => [
                                'type' => 'text',
                                'analyzer' => 'phpunitAnalyzerEn',
                                'search_analyzer' => 'phpunitAnalyzerEn'
                            ]
                        ]
                    ]
                ]
            ]
        ], $mappings['phpunit-by-field']['mappings']);
    }
    
    /**
     * @test
     */
    public function it_returns_a_elasticsearch_mapping_translated_by_index()
    {
        $result = $this->service->getMappings('phpunit-by-index', '1.0.0');
        
        $settings = [
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
        
        $mappings = [
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
        
        $this->assertEquals($settings, $result['phpunit-by-index']['settings']);
        $this->assertEquals($mappings, $result['phpunit-by-index']['mappings']);
        
        array_set($mappings, 'phpunit.properties.title.analyzer', 'phpunitAnalyzerEn');
        array_set($mappings, 'phpunit.properties.images.properties.title.analyzer', 'phpunitAnalyzerEn');
        array_set($mappings, 'phpunit.properties.images.properties.title.search_analyzer', 'phpunitAnalyzerEn');
    
        $this->assertEquals($settings, $result['phpunit-by-index_enUS']['settings']);
        $this->assertEquals($mappings, $result['phpunit-by-index_enUS']['mappings']);
    }
    
    /**
     * @test
     * @expectedException \Triadev\EsConfigBuilder\Exceptions\AnalyzerNotFound
     */
    public function it_throws_an_exception_that_analyzer_not_found()
    {
        $this->service->getMappings('phpunit-analyzer-not-found');
    }
    
    /**
     * @test
     * @expectedException \Triadev\EsConfigBuilder\Exceptions\FilterNotFound
     */
    public function it_throws_an_exception_that_filter_not_found()
    {
        $this->service->getMappings('phpunit-filter-not-found');
    }
}
