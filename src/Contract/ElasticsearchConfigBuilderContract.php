<?php
namespace Triadev\EsConfigBuilder\Contract;

use Triadev\EsConfigBuilder\Exceptions\MappingFileNotFound;

interface ElasticsearchConfigBuilderContract
{
    /**
     * Get mapping
     *
     * @param null|string $index
     * @param null|string $version
     * @return array array [
     *      INDEX-KEY => [
     *          "mappings" => [...],
     *          "settings" => [...]
     *      ],
     *      ...
     * ]
     *
     * @throws MappingFileNotFound
     */
    public function getMappings(?string $index = null, ?string $version = null) : array;
}
