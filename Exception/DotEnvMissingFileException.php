<?php

namespace Atournayre\DotEnvUpdaterBundle\Exception;

use Throwable;

class DotEnvMissingFileException extends \Exception implements ExceptionInterface
{
    public function __construct(string $file, string $message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->message = sprintf('Impossible to update %s cause file is missing.', $file);
    }
}