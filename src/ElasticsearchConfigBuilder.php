<?php
namespace Triadev\EsConfigBuilder;

use Triadev\EsConfigBuilder\Business\Validator\AnalyzerOfMappings;
use Triadev\EsConfigBuilder\Business\Validator\FilterOfAnalyzer;
use Triadev\EsConfigBuilder\Contract\ElasticsearchConfigBuilderContract;
use Triadev\EsConfigBuilder\Exceptions\MappingFileNotFound;
use Triadev\EsConfigBuilder\Models\ElasticsearchConfig;

class ElasticsearchConfigBuilder implements ElasticsearchConfigBuilderContract
{
    const TRANSLATION_TYPE_FIELD = 'field';
    const TRANSLATION_TYPE_INDEX = 'index';
    
    /** @var string */
    private $filePath;
    
    /**
     * ElasticsearchConfigBuilder constructor.
     */
    public function __construct()
    {
        $this->filePath = config('triadev-elasticsearch-config-builder.filePath');
    }
    
    /**
     * @inheritdoc
     */
    public function getMappings(?string $index = null, ?string $version = null): array
    {
        $activeIndices = $this->getActiveIndices($index, $version);
        
        $mappings = [];
        
        array_walk($activeIndices, function ($version, $index) use (&$mappings) {
            $translationsConfig = $this->buildTranslations($this->filePath, $index, $version);
            
            $elasticsearchConfig = $this->buildElasticsearchConfig(
                $this->filePath,
                $index,
                $version
            );
            
            if (array_get($translationsConfig, 'type') == self::TRANSLATION_TYPE_FIELD) {
                $elasticsearchConfig = $this->translateMappingsByField($elasticsearchConfig, $translationsConfig);
                $elasticsearchConfig = $this->updateMappingsByLocale($elasticsearchConfig, $translationsConfig);
            } elseif (array_get($translationsConfig, 'type') == self::TRANSLATION_TYPE_INDEX) {
                foreach (array_get($translationsConfig, 'locales') as $locale) {
                    if (preg_match('/^[a-z]{2}[A-Z]{2}$/', $locale)) {
                        $tmpElasticsearchConfig = clone $elasticsearchConfig;
    
                        $tmpElasticsearchConfig = $this->updateMappingsByLocale(
                            $tmpElasticsearchConfig,
                            $translationsConfig,
                            $locale
                        );
                        
                        $mappings[sprintf("%s_%s", $index, $locale)] = $tmpElasticsearchConfig->getElasticsearchConfig();
                    }
                }
            }
    
            $mappings[$index] = $elasticsearchConfig->getElasticsearchConfig();
        });
        
        app()->make(FilterOfAnalyzer::class)->validate($mappings);
        app()->make(AnalyzerOfMappings::class)->validate($mappings);
        
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
                $this->filePath,
                $indexPath,
                $activeVersion
            ))) {
                $activeIndices[$indexPath] = $activeVersion;
            }
        }
        
        return $activeIndices;
    }
    
    /**
     * @param string $filePath
     * @param string $index
     * @param string $version
     * @return ElasticsearchConfig
     *
     * @throws MappingFileNotFound
     */
    private function buildElasticsearchConfig(
        string $filePath,
        string $index,
        string $version
    ) : ElasticsearchConfig {
        $mappings = $this->buildMappings($filePath, $index, $version);
        $settings = $this->buildSettings($filePath, $index, $version);
        
        return new ElasticsearchConfig(
            $mappings,
            $settings
        );
    }
    
    /**
     * @param string $filePath
     * @param string $index
     * @param string $version
     * @return array
     * @throws MappingFileNotFound
     */
    private function buildMappings(string $filePath, string $index, string $version) : array
    {
        $mappingsPath = $this->buildIndexResourcesPath($filePath, $index, $version, 'mappings');
        if (file_exists($mappingsPath)) {
            return require $mappingsPath;
        }
    
        throw new MappingFileNotFound();
    }
    
    /**
     * @param string $filePath
     * @param string $index
     * @param string $version
     * @return array|null
     */
    private function buildSettings(string $filePath, string $index, string $version) : ?array
    {
        $settingsPath = $this->buildIndexResourcesPath($filePath, $index, $version, 'settings');
        if (file_exists($settingsPath)) {
            return require $settingsPath;
        }
        
        return null;
    }
    
    /**
     * @param string $filePath
     * @param string $index
     * @param string $version
     * @return array|null
     */
    private function buildTranslations(string $filePath, string $index, string $version) : ?array
    {
        $translationsPath = $this->buildIndexResourcesPath($filePath, $index, $version, 'translations');
        if (file_exists($translationsPath)) {
            return require $translationsPath;
        }
        
        return null;
    }
    
    /**
     * @param ElasticsearchConfig $elasticsearchConfig
     * @param array $translationsConfig
     * @return ElasticsearchConfig
     */
    private function translateMappingsByField(
        ElasticsearchConfig $elasticsearchConfig,
        array $translationsConfig
    ) : ElasticsearchConfig  {
        $mappings = $elasticsearchConfig->getMappings();
    
        foreach (array_get($translationsConfig, 'fields') as $field) {
            $fieldValue = array_get($mappings, $field);
    
            foreach (array_get($translationsConfig, 'locales') as $locale) {
                if (preg_match('/^[a-z]{2}[A-Z]{2}$/', $locale)) {
                    array_set($mappings, sprintf("%s_%s", $field, $locale), $fieldValue);
                }
            }
        }
        
        $elasticsearchConfig->setMappings($mappings);
        
        return $elasticsearchConfig;
    }
    
    /**
     * @param ElasticsearchConfig $elasticsearchConfig
     * @param array $translationsConfig
     * @param string|null $locale
     * @return ElasticsearchConfig
     */
    private function updateMappingsByLocale(
        ElasticsearchConfig $elasticsearchConfig,
        array $translationsConfig,
        ?string $locale = null
    ) : ElasticsearchConfig {
        $mappings = $elasticsearchConfig->getMappings();
        
        $translationType = array_get($translationsConfig, 'type');
    
        foreach (array_get($translationsConfig, 'fields') as $field) {
            $configPerLocale = array_get($translationsConfig, 'configPerLocale');
            
            if (is_array($configPerLocale) && array_key_exists($field, $configPerLocale)) {
                if ($translationType == self::TRANSLATION_TYPE_FIELD) {
                    foreach ($configPerLocale[$field] as $locale => $configs) {
                        if (in_array($locale, array_get($translationsConfig, 'locales'))) {
                            foreach (array_dot($configs) as $configKey => $configValue) {
                                array_set(
                                    $mappings,
                                    sprintf("%s_%s.%s", $field, $locale, $configKey),
                                    $configValue
                                );
                            }
                        }
                    }
                } elseif ($translationType == self::TRANSLATION_TYPE_INDEX &&
                    in_array($locale, array_get($translationsConfig, 'locales'))) {
                    $configByField = array_get($configPerLocale, $field);
    
                    if ($config = array_get($configByField, $locale)) {
                        foreach (array_dot($config) as $configKey => $configValue) {
                            array_set(
                                $mappings,
                                sprintf("%s.%s", $field, $configKey),
                                $configValue
                            );
                        }
                    }
                }
            }
        }
        
        $elasticsearchConfig->setMappings($mappings);
        
        return $elasticsearchConfig;
    }
    
    /**
     * @param string $filePath
     * @param string $index
     * @param string $version
     * @param null|string $configFile
     * @return string
     */
    private function buildIndexResourcesPath(
        string $filePath,
        string $index,
        string $version,
        ?string $configFile = null
    ) : string {
        $path = sprintf("%s/%s/%s", $filePath, $index, $version);
        
        if ($configFile) {
            $path .= sprintf("/%s.php", $configFile);
        }
        
        return $path;
    }
}
