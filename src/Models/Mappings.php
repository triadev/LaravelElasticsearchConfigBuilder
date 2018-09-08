<?php
namespace Triadev\EsConfigBuilder\Models;

class Mappings
{
    /** @var array */
    private $mappings;
    
    /**
     * Mappings constructor.
     * @param array $mappings
     */
    public function __construct(array $mappings)
    {
        $this->mappings = $mappings;
    }
    
    /**
     * @return array
     */
    public function getMappings() : array
    {
        return $this->mappings;
    }
}
