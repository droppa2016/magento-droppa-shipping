<?php

namespace Droppa\DroppaShipping\Model;

class Quotes
{

    public $parcel_length;
    public $parcel_breadth;
    public $parcel_height;

    public function __construct($parcel_length, $parcel_breadth, $parcel_height, $parcel_mass)
    {
        $this->parcel_length      = $parcel_length;
        $this->parcel_breadth     = $parcel_breadth;
        $this->parcel_height      = $parcel_height;
        $this->parcel_mass        = $parcel_mass;
    }

    function get_parcel_length()
    {
        return $this->parcel_length;
    }

    function get_parcel_breadth()
    {
        return $this->parcel_breadth;
    }

    function get_parcel_height()
    {
        return $this->parcel_height;
    }

    function get_parcel_mass()
    {
        return $this->parcel_mass;
    }

    // public $pickUpPCode;
    // public $dropOffPCode;
    // public $weight;

    // public function __construct($pickUpPCode, $dropOffPCode, $weight)
    // {
    //     $this->pickUpPCode      = $pickUpPCode;
    //     $this->dropOffPCode     = $dropOffPCode;
    //     $this->weight           = $weight;
    // }

    // public function getPickUpCode()
    // {
    //     return $this->pickUpPCode;
    // }

    // public function getDropOffCode()
    // {
    //     return $this->dropOffPCode;
    // }

    // public function getWeight()
    // {
    //     return $this->weight;
    // }
}