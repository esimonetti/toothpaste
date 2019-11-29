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
Phar::interceptFileFuncs();
//$phar->buildFromDirectory($tmpDir . '/vendor', '/\.php$/');
$phar->buildFromDirectory($tmpDir . '/vendor');
$phar->setStub($phar->createDefaultStub('esimonetti/toothpaste/bin/toothpaste.php'));

exec('cd .. && rm -rf ' . $tmpDir);
