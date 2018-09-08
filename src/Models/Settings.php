<?php
namespace Triadev\EsConfigBuilder\Models;

class Settings
{
    /** @var array */
    private $settings;
    
    /**
     * Settings constructor.
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }
    
    /**
     * @return array
     */
    public function getSettings() : array
    {
        return $this->settings;
    }
}
