<?php
namespace Amcsi\UnziPhp;

use Alchemy\Zippy\Zippy;

class Model
{
    public function tgzFiles(array $files, $target = null)
    {
        if (!$target || ($dirDoesntExist = !is_dir(dirname($target)))) {
            if (!empty($dirDoesntExist)) {
                trigger_error("Target directory doesn't exist. Using temporary file.");
            }
            $filename = tempnam($this->getTgzTmpDir(), 'unzi');
            var_dump('lol', $target);
        } else if (is_dir($target)) {
            $filename = tempnam($target, 'unzi');
        } else if (is_dir(dirname($target))) {
            $filename = $target;
        }

        if (file_exists($filename)) {
            unlink($filename);
        }
        if ('.tar.gz' == substr($filename, -7)) {
            $filename = substr($filename, 0, -3);
            if (file_exists($filename)) {
                unlink($filename);
            }
        }
        $pharData = new \PharData($filename);
        $files = $this->filterFilesForTar($files);
        foreach ($files as $file) {
            if (!is_dir($file)) {
                $pharData->addFile($file);
            }
        }
        $gzPhar = $pharData->compress(\Phar::GZ);
        $pharData = null;

        return $filename;
    }

    public function tgzFilesToTemp(array $files)
    {
        $files = $this->filterFileList($files);


        $filename = tempnam($this->getTgzTmpDir(), 'unzi');
    }

    public function filterFilesForTar(array $files)
    {
        $ret = array();
        foreach ($files as $key => $file) {
            if (0 === strpos($file, './')) {
                $file = substr($file, 2);
            }
            $ret[trim($file)] = trim($file);
        }
        return $ret;
    }

    public function filterFileList(array $files)
    {
        $ret = array();
        foreach ($files as $key => $file) {
            $newKey = $file;
            if (0 === strpos($newKey, './')) {
                $newKey = substr($newKey, 2);
            }
            $ret[trim($file)] = trim($file);
        }
        return $ret;
    }

    public function getTgzTmpDir()
    {
        $returnVal = shell_exec('which tar.exe');
        if (empty($returnVal)) {
            // ok
        } else {
            $dfs = explode(',', ini_get('disable_functions'));
            $dfs[] = 'exec';
            $dfs[] = 'shell_exec';
            ini_set('disable_functions', join(',', $dfs));
        }
        return sys_get_temp_dir();
    }
}
