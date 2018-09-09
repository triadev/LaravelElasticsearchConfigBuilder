<?php
namespace Triadev\EsConfigBuilder\Exceptions;

use Throwable;

class FilterNotFound extends \Exception
{
    /**
     * FilterNotFound constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
