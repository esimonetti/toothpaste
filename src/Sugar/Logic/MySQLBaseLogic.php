<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Sugar\Logic;
use Toothpaste\Sugar;

class MySQLBaseLogic extends Sugar\BaseLogic
{
    protected function isMySQL()
    {
        $db = \DBManagerFactory::getInstance();
        return ($db->dbType === 'mysql') ? true : false;
    }
}
