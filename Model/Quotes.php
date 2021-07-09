<?php

namespace Droppa\DroppaShipping\Model;

class Quotes
{
    public $pickUpPCode;
    public $dropOffPCode;
    public $weight;

    public function __construct($pickUpPCode, $dropOffPCode, $weight)
    {
        $this->pickUpPCode      = $pickUpPCode;
        $this->dropOffPCode     = $dropOffPCode;
        $this->weight           = $weight;
    }

    public function getPickUpCode()
    {
        return $this->pickUpPCode;
    }

    public function getDropOffCode()
    {
        return $this->dropOffPCode;
    }

    public function getWeight()
    {
        return $this->weight;
    }
}
