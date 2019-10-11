<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Sugar;

class BaseAction
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
        } else {
            //echo $message . PHP_EOL;
        }
    }

    public function write($message)
    {
        if ($this->logger) {
            $this->logger->write($message);
        } else {
            //echo $message;
        }
    }
}
