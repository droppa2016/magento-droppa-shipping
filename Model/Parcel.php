<?php

namespace Droppa\DroppaShipping\Model;

class Parcel
{
    public $itemMass;
    public $width;
    public $height;
    public $length;

    public function __construct($itemMass, $width, $height, $length)
    {
        $this->itemMass = $itemMass;
        $this->width = $width;
        $this->height = $height;
        $this->length = $length;
    }

    public function getItemMass()
    {
        return $this->itemMass;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getLength()
    {
        return $this->length;
    }
}
