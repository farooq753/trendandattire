<?php

namespace App\Exceptions;

use RuntimeException;

class CheckoutException extends RuntimeException
{
    /**
     * @param  array<string, string>  $errors
     */
    public function __construct(public array $errors)
    {
        parent::__construct(implode(' ', $this->errors));
    }
}
