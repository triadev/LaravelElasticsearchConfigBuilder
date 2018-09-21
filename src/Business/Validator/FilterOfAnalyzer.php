<?php
namespace Triadev\EsConfigBuilder\Business\Validator;

use Triadev\EsConfigBuilder\Exceptions\FilterNotFound;

class FilterOfAnalyzer
{
    /**
     * Validate
     *
     * @param array $mappings
     *
     * @throws FilterNotFound
     */
    public function validate(array $mappings)
    {
        foreach ($mappings as $type => $configPerType) {
            $validFilter = $this->getValidFilter($configPerType);
    
            if ($configAnalyzer = array_get($configPerType, 'settings.analysis.analyzer')) {
                foreach ($configAnalyzer as $ca) {
                    if ($filter = array_get($ca, 'filter')) {
                        foreach ($filter as $f) {
                            if (!in_array($f, $validFilter)) {
                                throw new FilterNotFound(sprintf(
                                    "The filter could not be found: %s",
                                    $f
                                ));
                            }
                        }
                    }
                }
            }
        }
    }
    
    private function getValidFilter(array $config) : array
    {
        $filterSettings = array_get($config, 'settings.analysis.filter');
        if (!is_array($filterSettings)) {
            $filterSettings = [];
        }
        
        return array_merge(
            config('triadev-elasticsearch-config-builder.validation.whitelistFilter'),
            array_keys($filterSettings)
        );
    }
}
