<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Sugar\Logic;
use Toothpaste\Sugar;

class TeamSetsCleanup extends Sugar\BaseLogic
{
    public function performFullCleanup()
    {
        $this->performTeamSetManagerCleanup();
        $this->performDeleteOfNullTeamSetModules();
    }

    public function performTeamSetManagerCleanup()
    {
        $this->write('Executing system\'s TeamSetManager cleanup... ');
        \TeamSetManager::cleanUp(); 
        $this->writeln('done.');
    }

    public function performDeleteOfNullTeamSetModules()
    {
        $this->write('Deleting all Team Sets with null team_set_id from team_sets_modules... ');
        $builder = \DBManagerFactory::getInstance()->getConnection()->createQueryBuilder();
        $builder->delete('team_sets_modules')
        ->where($builder->expr()->eq('deleted', $builder->createPositionalParameter(0)))
        ->andWhere($builder->expr()->isNull('team_set_id'));
        $res = $builder->execute();
        $this->writeln('done.');
    }
}
