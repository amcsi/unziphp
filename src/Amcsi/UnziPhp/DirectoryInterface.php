<?php
namespace Amcsi\UnziPhp;

interface DirectoryInterface
{
    /**
     * Returns the name of the path
     * 
     * @access public
     * @return void
     */
    public function getPath();

    /**
     * Returns the name of the path
     * 
     * @access public
     * @return void
     */
    public function __toString();
}
