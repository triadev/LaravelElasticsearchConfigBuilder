<?php
namespace Triadev\EsConfigBuilder\Models;

class ElasticsearchConfig
{
    /** @var Mappings */
    private $mappings;
    
    /** @var Settings|null  */
    private $settings;
    
    /**
     * ElasticsearchConfig constructor.
     * @param Mappings $mappings
     * @param Settings|null $settings
     */
    public function __construct(Mappings $mappings, ?Settings $settings = null)
    {
        $this->mappings = $mappings;
        $this->settings = $settings;
    }
    
    /**
     * Get elasticsearch config
     *
     * @return array
     */
    public function getElasticsearchConfig() : array
    {
        $config = [
            'mappings' => $this->mappings->getMappings()
        ];
        
        if ($this->settings) {
            $config['settings'] = $this->settings->getSettings();
        }
        
        return $config;
    }
}
