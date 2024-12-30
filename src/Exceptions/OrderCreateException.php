<?php

namespace Wsmallnews\Order\Exceptions;

use Wsmallnews\Order\OrderRocket;
use Wsmallnews\Support\Exceptions\SupportException;

class OrderCreateException extends SupportException 
{

    protected ?OrderRocket $rocket = null;

    public function setRocket($rocket): self
    {
        $this->rocket = $rocket;
        return $this;
    }


    public function getRocket(): OrderRocket
    {
        return $this->rocket;
    }

}
