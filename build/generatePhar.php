<?php

// Enrico Simonetti
// enricosimonetti.com

$pharIni = ini_get('phar.readonly');
if ($pharIni) {
    echo PHP_EOL . 'Please make sure the setting phar.readonly is set to false' . PHP_EOL;
    exit(1);
}

$tmpDir = dirname(__FILE__) . '/tmp';
if (is_dir($tmpDir)) {
    exec('rm -rf ' . $tmpDir);
}

mkdir($tmpDir);
chdir($tmpDir);
exec('composer -n require esimonetti/toothpaste *');

$buildRoot = dirname(__FILE__) . '/../phar';
if (!is_dir($buildRoot)) {
    mkdir($buildRoot);
}

$fileName = 'toothpaste.phar';
$phar = new Phar($buildRoot . '/' . $fileName, 0, $fileName);
$phar->buildFromDirectory($tmpDir . '/vendor');
$phar->setStub("<?php
Phar::mapPhar();
set_include_path(get_include_path() . PATH_SEPARATOR . 'phar://' . __FILE__);
require_once 'esimonetti/toothpaste/bin/toothpaste.php'; __HALT_COMPILER();
");

echo PHP_EOL . 'Phar generated: ' . $buildRoot . '/' . $fileName . PHP_EOL . PHP_EOL;
