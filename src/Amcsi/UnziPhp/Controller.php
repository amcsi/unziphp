<?php
namespace Amcsi\UnziPhp;

class Controller
{
    public function dispatchCli()
    {
        $cliOptions = getopt('', array('target:'));
        var_dump($cliOptions);
        $options = $cliOptions;

        $input = file("php://stdin");
        $model = $this->getModel();

        /*
        if (!isset($options['target-dir']) && empty($options['upload'])) {
            echo "A target directory or an upload URL must be given.\n";
            return 1;
        }
         */

        if (!$input) {
            echo "No files to upload.";
            return 1;
        }
        $filename = $model->tgzFiles($input, @$options['target']);
        var_dump($filename, filesize($filename));
    }

    public function getModel()
    {
        static $model;
        if (!$model) {
            $model = new Model;
        }
        return $model;
    }
}
