<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Sugar;

class MySQLBaseAction extends BaseAction
{
    protected function isMySQL()
    {
        $db = \DBManagerFactory::getInstance();
        return ($db->dbType === 'mysql') ? true : false;
    }
}
