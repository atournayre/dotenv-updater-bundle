<?php

namespace Atournayre\DotEnvUpdaterBundle\Exception;

class DotEnvEditionNotAllowedException extends \Exception implements ExceptionInterface
{
    protected $message = '.env edition is not allowed!';
}