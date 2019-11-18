<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Sugar;

class BaseLogic
{
    protected $logger;

    public function setLogger($out = null)
    {
        if ($out) {
            $this->logger = $out; 
        }
    }

    public function writeln($message)
    {
        if ($this->logger) {
            $this->logger->writeln($message);
        }
    }

    public function write($message)
    {
        if ($this->logger) {
            $this->logger->write($message);
        }
    }

    public function addTrailingSlash($string) : String
    {
        return rtrim($string, '/') . '/';
    }

    protected function createDir($dir)
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    protected function formatNumber(float $number, ?int $decimals = 2) : string
    {
        return number_format($number, $decimals, '.', ',');
    }

    protected function matchesAllPatterns(String $string, array $patternsToMatch = [], array $patternsToIgnore = []) : bool
    {
        // if it does not match any of the required patterns, return false
        if (!empty($patternsToMatch)) {
            foreach ($patternsToMatch as $have) {
                if (!preg_match($have, $string)) {
                    return false;
                }
            }
        }

        // if it matches any of the patterns it should not match, return false
        if (!empty($patternsToIgnore)) {
            foreach ($patternsToIgnore as $notHave) {
                if (preg_match($notHave, $string)) {
                    return false;
                }
            }
        }

        // if it has not yet returned false, return true
        return true;
    }

    protected function findFiles(String $dir, array $patternsToMatch = [], array $patternsToIgnore = []) : array
    {
        $files = [];
        if (is_dir($dir)) {
            $rdi = new \RecursiveDirectoryIterator($dir);
            foreach (new \RecursiveIteratorIterator($rdi) as $f) {
                $filePath = $f->getPathName();
                if ($this->matchesAllPatterns($filePath, $patternsToMatch, $patternsToIgnore)) {
                    $files[] = $filePath;
                }
            }
        }
        return $files;
    }
}
