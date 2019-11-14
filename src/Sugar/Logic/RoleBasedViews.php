<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Sugar\Logic;
use Toothpaste\Sugar\Instance;
use Toothpaste\Sugar;

class RoleBasedViews extends Sugar\BaseLogic
{
    protected $patternsToHave = ['/.php/', '/[a-f0-9\-]{36}/i'];
    protected $patternsToNotHave = ['/.ext.php/', '/history/', '/working/', '/_[0-9]{10}/'];
    protected $roleMapping = [];

    protected function seekRBVFiles() : array
    {
        return $this->findFiles('custom', $this->patternsToHave, $this->patternsToNotHave);
    }

    protected function lookupRoleName(String $filePath) : String
    {
        if (preg_match('/[a-f0-9\-]{36}/i', $filePath, $match)) {
            $id = $match[0];
            // return the pre-loaded name if already loaded
            if (!empty($this->roleMapping[$id])) {
                return $this->roleMapping[$id];
            }

            // lookup name
            $sq = new \SugarQuery();
            $sq->select('name');
            $sq->from(\BeanFactory::newBean('ACLRoles'));
            $sq->where()->equals('id', $id);
            $result = $sq->execute();

            $name = (!empty($result[0]['name']) ? $result[0]['name'] : 'ERROR - The Role with id ' . $id . ' no longer exists.');
            $this->roleMapping[$id] = $name;
            return $name;
        }

        return '';
    }

    public function usesRBV()
    {
        $rbv = $this->seekRBVFiles();
        if (!empty($rbv)) {
            $this->writeln('Role Based Views are in use. See file list below:');
            foreach ($rbv as $filePath) {
                $roleName = $this->lookupRoleName($filePath);
                $this->writeln($filePath . ' - Role Name: ' . $roleName);
            }
        } else {
            $this->writeln('Role Based Views is not in use for this system.');
        }
    }
}
