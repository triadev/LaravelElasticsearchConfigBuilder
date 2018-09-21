<?php
namespace Triadev\EsConfigBuilder\Business\Validator;

use Triadev\EsConfigBuilder\Exceptions\AnalyzerNotFound;

class AnalyzerOfMappings
{
    /**
     * Validate
     *
     * @param array $mappings
     *
     * @throws AnalyzerNotFound
     */
    public function validate(array $mappings)
    {
        foreach ($mappings as $type => $configPerType) {
            $validAnalyzer = $this->getValidAnalyzer($configPerType);
            
            foreach (array_dot(array_get($configPerType, 'mappings')) as $key => $value) {
                if (preg_match('/^.*\.analyzer$/', $key) && !in_array($value, $validAnalyzer)) {
                    throw new AnalyzerNotFound(sprintf(
                        "The analyzer could not be found: %s",
                        $value
                    ));
                }
            }
        }
    }
    
    private function getValidAnalyzer(array $config) : array
    {
        $analyzer = array_get($config, 'settings.analysis.analyzer');
        if (!is_array($analyzer)) {
            $analyzer = [];
        }
        
        return array_keys($analyzer);
    }
}
