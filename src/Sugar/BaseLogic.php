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

    public function addTrailingSlash($string)
    {
        return rtrim($string, '/') . '/';
    }

    protected function createDir($dir)
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }
}
