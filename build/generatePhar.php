<?php

// Enrico Simonetti
// enricosimonetti.com

$tmpDir = dirname(__FILE__) . '/tmp';
if (!is_dir($tmpDir)) {
    mkdir($tmpDir);
}

chdir($tmpDir);
exec('composer -n require esimonetti/toothpaste *');

$buildRoot = dirname(__FILE__) . '/../phar/';
$fileName = 'toothpaste.phar';
$phar = new Phar($buildRoot . '/' . $fileName, 0, $fileName);
$phar->buildFromDirectory($tmpDir . '/vendor');
$phar->setStub("<?php
Phar::mapPhar();
set_include_path(get_include_path() . PATH_SEPARATOR . 'phar://' . __FILE__);
require_once 'esimonetti/toothpaste/bin/toothpaste.php'; __HALT_COMPILER();
");

exec('cd .. && rm -rf ' . $tmpDir);
