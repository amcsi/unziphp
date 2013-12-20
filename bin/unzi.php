<?php
use Amcsi\UnziPhp\Controller;

chdir(__DIR__ . '/..');

require_once __DIR__ . '/../vendor/autoload.php';

$cont = new Controller;
$ret = $cont->dispatchCli();
return $ret;
