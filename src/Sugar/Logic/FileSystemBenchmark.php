<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Sugar\Logic;
use Toothpaste\Sugar;

class FileSystemBenchmark extends Sugar\BaseLogic
{
    protected $readSpeeds = [
        'Excellent' => 'Above 100,000 KB/s',
        'Good' => 'Between 20,000 KB/s and 99,999 KB/s',
        'Minimum acceptable' => 'Between 5,000 and 19,999 KB/s',
        'Needs attention' => 'Less than 5,000 KB/s',
    ];
    
    protected $writeSpeeds = [
        'Excellent' => 'Above 60,000 KB/s',
        'Good' => 'Between 5,000 KB/s and 59,999 KB/s',
        'Minimum acceptable (Especially for NFS storage. If the infrastructure does not use NFS, it needs attention already)' => 'Between 1,000 and 4,999 KB/s',
        'Needs attention' => 'Less than 1,000 KB/s',
    ];

    protected $minimumNumberOfFiles = 10000;

    protected $numberOfActualFiles = 0;

    protected $defaultLoops = 2;

    public function performReadBenchmark() : array
    {
        $this->writeln('Performing file system reading benchmark through PHP');

        $files = [];
        for ($l = 1; $l <= $this->defaultLoops; $l++) {
            $directory = new \RecursiveDirectoryIterator('./', \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS);
            $iterator = new \RecursiveIteratorIterator($directory);
            foreach ($iterator as $info) {
                if ($info->isFile()) {
                    if ($info->getExtension() === 'php') {
                        $files[] = $info->getPathname();
                    }
                }
            }
        }

        $this->numberOfFilesProcessed = count($files);

        $res = [
            'files' => 0,
            'size' => 0,
            'time' => 0,
        ];

        if ($this->numberOfFilesProcessed < $this->minimumNumberOfFiles) {
            // something is wrong, might not be a Sugar system
            $this->writeln('Detected only ' . $this->formatNumber($this->numberOfFilesProcessed, 0) . ' PHP files, when the minimum expected is ' . $this->formatNumber($this->minimumNumberOfFiles, 0) . '. Stopping.');
        } else {
            $this->writeln('The Sugar system contains ' . $this->formatNumber($this->numberOfFilesProcessed / $this->defaultLoops, 0) .
                ' PHP files and the script will test ' . $this->formatNumber($this->numberOfFilesProcessed, 0)  . ' files');

            $startTime = microtime(true);
            if (!empty($files)) {
                foreach ($files as $file) {
                    if (is_file($file) && file_exists($file)) {
                        $content = file_get_contents($file);
                        $res['size'] += strlen($content);
                        $res['files']++;
                        if (($res['files'] % 1000) === 0) {
                            $this->write('.');
                        }
                    }
                }
                $this->writeln('');
            }

            $res['size'] = round($res['size'] / 1024, 2);
            $res['time'] = round(microtime(true) - $startTime, 2);
        }

        $this->writeln('File system reading benchmark through PHP completed');

        return $res;
    }

    protected function formatNumber(float $number, ?int $decimals = 2) : string
    {
        return number_format($number, $decimals, '.', ',');
    }

    protected function getStaticFileContent() : string
    {
        $fileContent =
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL .
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' . PHP_EOL ;
        return $fileContent;
    }

    public function performWriteBenchmark() : array
    {
        $this->writeln('Performing file system writing benchmark through PHP');

        // set the minimum amount to test for the write benchmark
        if ($this->numberOfFilesProcessed < $this->minimumNumberOfFiles) {
            $this->numberOfFilesProcessed = $this->minimumNumberOfFiles;
        }

        $res = [
            'files' => 0,
            'size' => 0,
            'time' => 0,
        ];

        $destDir = 'cache/perfTest';

        $this->writeln('Benchmarking file system write performance by writing and immediately deleting ' . $this->numberOfFilesProcessed . ' files.');
        $this->writeln('The benchmark process might take some time, please wait...');

        // create if does not exist
        if (!is_dir($destDir)) {
            mkdir($destDir, 0777, true);
        }

        $fileContent = $this->getStaticFileContent();
        $fileContentSize = strlen($fileContent);

        $startTime = microtime(true);
        for ($i = 1; $i <= $this->numberOfFilesProcessed; $i++) {
            $fullPath = $destDir . '/' . $i . '.txt';
            file_put_contents($fullPath, $fileContent);
            $res['files']++;
            $res['size'] += $fileContentSize;
            unlink($fullPath);
            if (($res['files'] % 1000) === 0) {
                $this->write('.');
            }
        }
        $this->writeln('');
        $res['size'] = round($res['size'] / 1024, 2);
        $res['time'] = round(microtime(true) - $startTime, 2);
        rmdir($destDir);

        $this->writeln('File system writing benchmark through PHP completed');

        return $res;
    }

    public function performFileSystemBenchmark()
    {
        $res = $this->performReadBenchmark();

        $throughputData = round($res['size'] / $res['time'], 2);

        $this->writeln('Processed ' . $this->formatNumber($res['files'], 0) . ' files. Loaded their content of ' . $this->formatNumber($res['size']) .
            ' KB. Read speed benchmark completed in ' . $this->formatNumber($res['time']) . ' seconds.');
        $this->writeln('');
        $this->writeln('Read speed: ' . $this->formatNumber($throughputData) . ' KB/s');

        $this->outputComparisonData($this->readSpeeds);     
  
        $res = $this->performWriteBenchmark();

        $throughputData = round($res['size'] / $res['time'], 2);

        $this->writeln('Processed ' . $this->formatNumber($res['files'], 0) . ' files. Loaded their content of ' . $this->formatNumber($res['size']) .
            ' KB. Write speed benchmark completed in ' . $this->formatNumber($res['time']) . ' seconds.');
        $this->writeln('');
        $this->writeln('Write speed: ' . $this->formatNumber($throughputData) . ' KB/s');

        $this->outputComparisonData($this->writeSpeeds);     
    }

    protected function outputComparisonData($speeds)
    {
        $this->writeln('');
        $this->writeln('Indicative comparison data:');
        foreach ($speeds as $level => $comment) {
            $this->writeln($level . ' - ' . $comment);
        }
        $this->writeln('');
    }
}
