<?php
namespace Triadev\EsConfigBuilder\Models;

class ElasticsearchConfig
{
    /** @var array */
    private $mappings;
    
    /** @var array|null  */
    private $settings;
    
    /**
     * ElasticsearchConfig constructor.
     * @param array $mappings
     * @param array|null $settings
     */
    public function __construct(array $mappings, ?array $settings = null)
    {
        $this->mappings = $mappings;
        $this->settings = $settings;
    }
    
    /**
     * @return array
     */
    public function getMappings(): array
    {
        return $this->mappings;
    }
    
    /**
     * @param array $mappings
     */
    public function setMappings(array $mappings): void
    {
        $this->mappings = $mappings;
    }
    
    /**
     * @return array|null
     */
    public function getSettings(): ?array
    {
        return $this->settings;
    }
    
    /**
     * @param array|null $settings
     */
    public function setSettings(?array $settings): void
    {
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
            'mappings' => $this->mappings
        ];
        
        if ($this->settings) {
            $config['settings'] = $this->settings;
        }
        
        return $config;
    }
}
