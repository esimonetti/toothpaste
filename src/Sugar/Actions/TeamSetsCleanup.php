<?php

// Enrico Simonetti
// enricosimonetti.com
//
// 2018-05-01 on 8.0.0
//
// soft delete unused team sets

namespace Toothpaste\Sugar\Actions;
use Toothpaste\Sugar;

class TeamSetsCleanup extends Sugar\BaseAction
{
    protected $db;
    protected $tables = [];
    protected $deleted_teamsets = [];
    protected $undelete_queries = [];
    protected $date_modified;

    protected $valid_fields = [
        'team_set_id',
        'acl_team_set_id',
    ];

    protected $tables_to_ignore = [
        'team_sets_modules',
        'team_sets_teams',
        'team_sets_users_1',
        'team_sets_users_2',
    ];

    public $max_sleep_time = 20;

    public function __construct()
    {
        $this->db = \DBManagerFactory::getInstance();
        $this->tables = $this->getTablesWithTeams();
        $this->date_modified = gmdate('Y-m-d H:i:s');
    }

    public function microSleep()
    {
        // sleep a little, to reduce db load
        if ($this->max_sleep_time > 0) {
            $time = rand(0, $this->max_sleep_time);
            usleep($time);
        }
    }

    public function verifyTeamSetExistancePerFieldOnTable($table, $team_set_id, $field)
    {
        if (in_array($field, $this->valid_fields) && in_array($table, $this->tables)) {

            $this->microSleep();

            $builder = $this->db->getConnection()->createQueryBuilder();

            $builder->select('id')
                ->from($table)
                ->where('deleted = ' . $builder->createPositionalParameter(0))
                ->andWhere($field . ' = ' . $builder->createPositionalParameter($team_set_id))
                ->setMaxResults(1);

            $res = $builder->execute();

            $output = $this->convertSingleResultSet($res->fetchAll(), 'id');
            if (!empty($output['0'])) {
                return $output['0'];
            }
        }

        return 0;
    }

    public function verifyTeamSetExistanceOnTable($table, $team_set_id)
    {
        $team_set_id = $this->verifyTeamSetExistancePerFieldOnTable($table, $team_set_id, 'team_set_id');
        if ($team_set_id) {
            // we have the team set
            return true;
        } else {
            // we should first check if TBP is enabled to optimise performance
            $acl_team_set_id = $this->verifyTeamSetExistancePerFieldOnTable($table, $team_set_id, 'acl_team_set_id');
            if ($acl_team_set_id) {
                // we have an acl team set
                return true;
            }
        }

        return 0;
    }

    public function getAllTeamSets()
    {
        // get all team sets from team_sets_teams
        $builder1 = $this->db->getConnection()->createQueryBuilder();
        $builder1->select('team_set_id')
            ->from('team_sets_teams')
            ->where('deleted = ' . $builder1->createPositionalParameter(0))
            ->groupBy('team_set_id');
          
        $res = $builder1->execute();
        $output1 = $this->convertSingleResultSet($res->fetchAll(), 'team_set_id');

        // get all team sets that are in team_sets but not in team_sets_teams
        $builder2 = $this->db->getConnection()->createQueryBuilder();
        $builder2->select('id')
            ->from('team_sets')
            ->where('deleted = ' .  $builder2->createPositionalParameter(0))
            ->andWhere(
                $builder2->expr()->notIn('id', $builder1->getSQL())
            );

        $res = $builder2->execute();
        $output2 = $this->convertSingleResultSet($res->fetchAll(), 'id');

        // merge the results
        $output = array_unique(array_merge($output1, $output2));

        return $output;
    }

    public function isTeamSetATeam($team_set_id)
    {
        if (!empty($team_set_id)) {
            $builder = $this->db->getConnection()->createQueryBuilder();
            $builder->select('id')
                ->from('teams')
                ->where('deleted = ' . $builder->createPositionalParameter(0))
                ->andWhere('id = ' . $builder->createPositionalParameter($team_set_id));
              
            $res = $builder->execute();
            $output = $this->convertSingleResultSet($res->fetchAll(), 'id');
            if (!empty($output)) {
                return true;
            }
        }
        return false;
    }

    public function getTablesWithTeams()
    {
        $db_tables = $this->db->getTablesArray();
        $tables_with_teams = [];

        foreach ($db_tables as $table) {
            if (!in_array($table, $this->tables_to_ignore)) {
                $columns = $this->db->get_columns($table);
                if (!empty($columns['team_set_id']) && !empty($columns['acl_team_set_id'])) {
                    $tables_with_teams[] = $table;
                }
            }
        }

        return $tables_with_teams;
    }

    public function findUnusedTeamSets()
    {
        $team_sets = $this->getAllTeamSets();
        $tables = $this->tables;

        $unused_teamsets = [];
        if (!empty($team_sets) && !empty($tables)) {
            foreach ($team_sets as $team_set_id) {
                $keep_teamset = false;
                // keep if it is equal to team id
                if ($this->isTeamSetATeam($team_set_id)) {
                    $keep_teamset = true;
                } else {
                    // look inside all tables randomised until we find it, and break as soon as possible
                    shuffle($tables); 

                    foreach ($tables as $table) {
                        $exists = $this->verifyTeamSetExistanceOnTable($table, $team_set_id);
                        if ($exists) {
                            // a record has it
                            $keep_teamset = true;
                            break;
                        }
                    }
                }

                if (!$keep_teamset) {
                    // the team set id wasn't found across all tables, mark as unused
                    $unused_teamsets[$team_set_id] = $team_set_id;
                }
            }
        }

        return $unused_teamsets;
    }

    public function getDeletedTeamSets()
    {
        return $this->deleted_teamsets;
    }

    protected function setDeletedTeamSets($teamsets)
    {
        if (!is_array($teamsets)) {
            $teamsets = [];
        }
        $this->deleted_teamsets = $teamsets;
    }

    public function softDeleteTeamSet($team_set_id)
    {
        if (!empty($team_set_id)) {

            $this->microSleep();

            $builder = $this->db->getConnection()->createQueryBuilder();
            $builder->update('team_sets')
            ->set('deleted', 1)
            ->where('deleted = ' . $builder->createPositionalParameter(0))
            ->andWhere('id = ' . $builder->createPositionalParameter($team_set_id));
            $res = $builder->execute();

            $builder = $this->db->getConnection()->createQueryBuilder();
            $builder->update('team_sets_teams')
            ->set('deleted', 1)
            ->where('deleted = ' . $builder->createPositionalParameter(0))
            ->andWhere('team_set_id = ' . $builder->createPositionalParameter($team_set_id));
            $res = $builder->execute();

            // produce revert query
            $this->addUndeleteQueryForTeamSet($team_set_id);
        }
    }

    public function addUndeleteQueryForTeamSet($team_set_id)
    {
        if (!empty($team_set_id)) {

            $builder = $this->db->getConnection()->createQueryBuilder();
            $builder->update('team_sets')
            ->set('deleted', 0)
            ->where('deleted = 1')
            ->andWhere('id = ' . $builder->createPositionalParameter($team_set_id));

            $this->undelete_queries[] = $this->composeSQLQueryFromPreparedComponents($builder->getSQL(), $builder->getParameters());

            $builder = $this->db->getConnection()->createQueryBuilder();
            $builder->update('team_sets_teams')
            ->set('deleted', 0)
            ->where('deleted = 1')
            ->andWhere('team_set_id = ' . $builder->createPositionalParameter($team_set_id));

            $this->undelete_queries[] = $this->composeSQLQueryFromPreparedComponents($builder->getSQL(), $builder->getParameters());
        }
    }

    protected function composeSQLQueryFromPreparedComponents($query, $params)
    {
        // only supports first param at this stage
        if (!empty($params) && count($params) == 1) {
            return str_replace('?', '\'' . array_pop($params) . '\'', $query) . ';';
        }

        return '';
    }

    public function getUndeleteTeamSetsQueries()
    {
        return $this->undelete_queries;
    }

    public function softDeleteUnusedTeamSets()
    {
        $teamsets = $this->findUnusedTeamSets();
        $this->setDeletedTeamSets($teamsets);

        if (!empty($teamsets)) {
            // soft delete
            foreach ($teamsets as $team_set_id) {
                $this->softDeleteTeamSet($team_set_id);
            }
        }

        return count($teamsets);
    }

    protected function convertSingleResultSet($results, $fieldname)
    {
        $output = [];
        if (!empty($results)) {
            foreach ($results as $result) {
                if (isset($result[$fieldname])) {
                    $output[] = $result[$fieldname];
                }          
            }
        }

        return $output;
    }
}
