<?php
namespace Amcsi\UnziPhp;

class Tempdir implements DirectoryInterface
{
    protected $path;

    public function __construct()
    {
        $tempdir = $this->createTempdir();
        $this->path = $tempdir;
    }

    protected function createTempdir()
    {
        $mode = 0777;
        do {
            $path = sprintf(
                '%s/%s',
                sys_get_temp_dir(),
                'unzi-' . Utils::getRandomAlphanumString(20)
            );
        } while (!mkdir($path, $mode));

        return $path;
    }

    /**
     * Returns the name of the path
     * 
     * @access public
     * @return void
     */
    public function getPath()
    {
        
    }

    public function delete($recursive = false)
    {
        if ($recursive) {

        }
    }

    /**
     * Returns the name of the path
     * 
     * @access public
     * @return void
     */
    public function __toString()
    {
        return (string) $this->getPath();
    }

    public function __destruct()
    {

    }
}
