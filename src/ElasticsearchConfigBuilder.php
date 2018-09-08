<?php
namespace Triadev\EsConfigBuilder;

use Triadev\EsConfigBuilder\Contract\ElasticsearchConfigBuilderContract;
use Triadev\EsConfigBuilder\Exceptions\MappingFileNotFound;
use Triadev\EsConfigBuilder\Models\ElasticsearchConfig;
use Triadev\EsConfigBuilder\Models\Mappings;
use Triadev\EsConfigBuilder\Models\Settings;

class ElasticsearchConfigBuilder implements ElasticsearchConfigBuilderContract
{
    /** @var string */
    private $resourcesPath;
    
    /**
     * ElasticsearchConfigBuilder constructor.
     */
    public function __construct()
    {
        $this->resourcesPath = config('triadev-elasticsearch-config-builder.resourcesPath');
    }
    
    /**
     * @inheritdoc
     */
    public function getMappings(?string $index = null, ?string $version = null): array
    {
        $activeIndices = $this->getActiveIndices($index, $version);
        
        $mappings = [];
        
        array_walk($activeIndices, function ($version, $index) use (&$mappings) {
            $mappings[$index] = $this->buildElasticsearchConfig(
                $this->resourcesPath,
                $index,
                $version
            )->getElasticsearchConfig();
        });
        
        return $mappings;
    }
    
    private function getActiveIndices(?string $index = null, ?string $version = null) : array
    {
        $activeIndices = [];
        
        $indicesConfig = config('triadev-elasticsearch-config-builder.indices');
        
        foreach ($indicesConfig as $indexPath => $activeVersion) {
            if ($index && $indexPath != $index) {
                continue;
            }
            
            if ($version) {
                $activeVersion = $version;
            }
            
            if (is_dir($this->buildIndexResourcesPath(
                $this->resourcesPath,
                $indexPath,
                $activeVersion
            ))) {
                $activeIndices[$indexPath] = $activeVersion;
            }
        }
        
        return $activeIndices;
    }
    
    private function buildElasticsearchConfig(
        string $resourcesPath,
        string $index,
        string $version
    ) : ElasticsearchConfig {
        $mappings = $this->buildMappings($resourcesPath, $index, $version);
        $settings = $this->buildSettings($resourcesPath, $index, $version);
        
        $elasticsearchConfig = new ElasticsearchConfig(
            new Mappings(
                $this->translateMappings(
                    $resourcesPath,
                    $mappings,
                    $index,
                    $version
                )
            ),
            $settings ? new Settings($settings) : null
        );
        
        return $elasticsearchConfig;
    }
    
    /**
     * @param string $resourcesPath
     * @param string $index
     * @param string $version
     * @return array
     * @throws MappingFileNotFound
     */
    private function buildMappings(string $resourcesPath, string $index, string $version) : array
    {
        $mappingsPath = $this->buildIndexResourcesPath($resourcesPath, $index, $version, 'mappings');
        if (file_exists($mappingsPath)) {
            return require $mappingsPath;
        }
    
        throw new MappingFileNotFound();
    }
    
    /**
     * @param string $resourcesPath
     * @param string $index
     * @param string $version
     * @return array|null
     */
    private function buildSettings(string $resourcesPath, string $index, string $version) : ?array
    {
        $settingsPath = $this->buildIndexResourcesPath($resourcesPath, $index, $version, 'settings');
        if (file_exists($settingsPath)) {
            return require $settingsPath;
        }
        
        return null;
    }
    
    /**
     * @param string $resourcesPath
     * @param array $mappings
     * @param string $index
     * @param string $version
     * @return array
     */
    private function translateMappings(string $resourcesPath, array $mappings, string $index, string $version) : array
    {
        $translationsPath = $this->buildIndexResourcesPath($resourcesPath, $index, $version, 'translations');
        if (file_exists($translationsPath)) {
            $translationConfig = require $translationsPath;
            
            switch ($translationConfig['type']) {
                case 'field':
                    $mappings = $this->translateByField(
                        $mappings,
                        array_get($translationConfig, 'fields'),
                        array_get($translationConfig, 'locales'),
                        array_get($translationConfig, 'configPerLocale')
                    );
                    break;
                default:
                    break;
            }
        }
        
        return $mappings;
    }
    
    private function translateByField(array $mappings, array $fields, array $locales, array $configPerLocale) : array
    {
        foreach ($fields as $field) {
            $fieldValue = array_get($mappings, $field);
        
            foreach ($locales as $locale) {
                if (preg_match('/^[a-z]{2}[A-Z]{2}$/', $locale)) {
                    array_set($mappings, sprintf("%s_%s", $field, $locale), $fieldValue);
                }
            }
        
            if (is_array($configPerLocale) && array_key_exists($field, $configPerLocale)) {
                foreach ($configPerLocale[$field] as $locale => $configs) {
                    foreach (array_dot($configs) as $configKey => $configValue) {
                        array_set(
                            $mappings,
                            sprintf("%s_%s.%s", $field, $locale, $configKey),
                            $configValue
                        );
                    }
                }
            }
        }
        
        return $mappings;
    }
    
    private function buildIndexResourcesPath(
        string $resourcesPath,
        string $index,
        string $version,
        ?string $configFile = null
    ) : string {
        $path = sprintf("%s/%s/%s", $resourcesPath, $index, $version);
        
        if ($configFile) {
            $path .= sprintf("/%s.php", $configFile);
        }
        
        return $path;
    }
}
