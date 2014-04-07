<?php
namespace Amcsi\UnziPhp\Utils;

class Temp
{
    public function createTempfile()
    {
        return tempnam();
    }

    public function createTempdir()
    {

    }
}
