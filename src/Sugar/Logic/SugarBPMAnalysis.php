<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Sugar\Logic;
use Toothpaste\Sugar\Instance;
use Toothpaste\Sugar;

class SugarBPMAnalysis extends MySQLBaseLogic
{
    protected $conn = null;

    protected $processes = [];

    protected function getConn()
    {
        if (empty($this->conn)) {
            $this->conn = \DBManagerFactory::getInstance()->getConnection();
        }

        return $this->conn;
    }

    public function performAnalysis(Bool $outputAll = false)
    {
        $this->writeln('Performing analysis of SugarBPM usage');

        $this->analysisEnabledSugarBPMS();
        $this->analysisRecordGrowth();
        $this->analysisRecordGrowthPerProcess();
        $this->analysisRecordsPerModulesRecord($outputAll);
        $this->analysisRecordGrowthPerProcessPerMonth();

        $this->writeln('Analysis of SugarBPM usage completed');
    }

    protected function analysisEnabledSugarBPMS()
    {
        if ($this->isMySQL()) {
            $this->getConn();

            $this->writeln('');
            $this->write('Retrieving information currently active SugarBPMs, please wait... ');
            $query = "select count(id) as count, prj_module as module from pmse_project where prj_status = ? group by prj_module order by count(id) desc";
            $stmt = $this->conn->executeQuery($query, ['ACTIVE']);
            $this->writeln('done.');
            $this->writeln('');
            $this->writeln('The list of active SugarBPMs can be found below:');
            while ($row = $stmt->fetch()) {
                $this->writeln('Module: ' . $row['module']);
                $this->writeln('Number of Active SugarBPMs: ' . $this->formatNumber($row['count'], 0));
            }
            $this->writeln('');
            $this->writeln('');
        } else {
            // TODO
        }
    }

    protected function analysisRecordGrowth()
    {
        if ($this->isMySQL()) {
            $this->getConn();

            $allValues = [];
            $lowestValues = [];
            $highestValues = [];
            $totalValue = 0;
            $averageValue = 0;

            $this->writeln('');
            $this->write('Retrieving information about record growth over time, please wait... ');
            $query = "select count(f.id) as entries, DATE_FORMAT(date_entered, '%Y-%m') as month from pmse_bpm_flow f group by DATE_FORMAT(date_entered, '%Y-%m')";
            $stmt = $this->conn->executeQuery($query, []);
            $this->writeln('done.');
            $this->writeln('');

            while ($row = $stmt->fetch()) {
                $allValues[] = $row;
                $totalValue += $row['entries'];
                $averageValue = $totalValue / count($allValues);

                if (empty($highestValues) || $row['entries'] > $highestValues['entries']) {
                    // new max
                    $highestValues = $row;
                }

                if (empty($lowestValues) || $row['entries'] < $lowestValues['entries']) {
                    // new min
                    $lowestValues = $row;
                }
            }

            // calculate biggest swing

            if (!empty($allValues)) {
                if ($highestValues !== $lowestValues) {
                    // tell me something smart
                    $this->writeln('Average entries: ' . $this->formatNumber($averageValue, 0) . '; Time span: ' . count($allValues) . ' months');
                    $this->writeln('Min: ' . $this->formatNumber($lowestValues['entries'], 0) . ' entries for the month of: ' . $lowestValues['month'] . ' with a change from the average of ' . 
                        $this->formatNumber((abs($lowestValues['entries'] - $averageValue)) / ($averageValue / 100)) . '%');
                    $this->writeln('Max: ' . $this->formatNumber($highestValues['entries'], 0) . ' entries for the month of: ' . $highestValues['month'] . ' with a change from the average of ' .
                        $this->formatNumber((abs($highestValues['entries'] - $averageValue)) / ($averageValue / 100)) . '%');
                }

                $this->writeln('');
                $this->writeln('The list of all the entries by month can be found below:');
                foreach ($allValues as $value) {
                    $this->writeln('Month: ' . $value['month'] . '; Entries: ' . $this->formatNumber($value['entries'], 0));
                }
                $this->writeln('');
                $this->writeln('');
            }
        } else {
            // TODO
            // find equivalent queries and/or trasform what is possible into proper doctrine (it might be hard for queries containing DATE_FORMAT)
        }
    }

    protected function getBPMProcessFromFlowProId(String $id) : String
    {
        if ($this->isMySQL()) {
            $this->getConn();

            if (!empty($this->processes[$id])) {
                return $this->processes[$id];
            }

            $query = "select p.id as id, p.name as name, p.prj_module as module, p.prj_status as status from pmse_project p join pmse_bpm_process_definition pd on p.id = pd.prj_id join pmse_bpm_flow f on f.pro_id=pd.id where f.pro_id = ? limit 1";
            $stmt = $this->conn->executeQuery($query, [$id]);

            if ($row = $stmt->fetch()) {
                $this->processes[$id] = $row['name'] . '; Status: ' . $row['status'] . '; Module: ' . $row['module'] . '; Process id: ' . $row['id'] . '';
                return $this->processes[$id];
            }
            return '';

        } else {
            // TODO
            return '';
        }
    }

    protected function analysisRecordGrowthPerProcess()
    {
        if ($this->isMySQL()) {
            $this->getConn();
            $allValues = [];
            $totalProcesses = [];

            $this->writeln('');
            $this->write('Retrieving information about record growth per process, please wait... ');
            $query = "select count(f.id) as entries, f.pro_id as process, f.cas_flow_status as status from pmse_bpm_flow f group by f.pro_id, f.cas_flow_status";
            $stmt = $this->conn->executeQuery($query, []);
            $this->writeln('done.');
            $this->writeln('');

            while ($row = $stmt->fetch()) {
                $allValues[] = $row;
            }

            if (!empty($allValues)) {
                $this->writeln('The list of all the entries can be found below:');

                foreach ($allValues as $value) {
                    // initialise for totals
                    if (empty($totalProcesses[$value['process']])) {
                        $totalProcesses[$value['process']] = $value['entries'];
                    } else {
                        $totalProcesses[$value['process']] += $value['entries'];
                    }
                }

                foreach ($allValues as $value) {
                    if (!empty($value['process'])) {
                        $this->writeln('Process: ' . $this->getBPMProcessFromFlowProId($value['process']) . '; Status: ' . $value['status']  . '; Entries: ' . $this->formatNumber($value['entries'], 0) . ' / total ' . $this->formatNumber($totalProcesses[$value['process']], 0));
                    }
                }

                $this->writeln('');
                $this->writeln('');
            }
        } else {
            // TODO
        }
    }

    protected function analysisRecordsPerModulesRecord(Bool $outputAll = false)
    {
        if ($this->isMySQL()) {
            $this->getConn();
            $allValues = [];
            $statsData = [];

            $this->writeln('');
            $this->write('Retrieving information about SugarBPM records per Sugar record entry, please wait... ');
            $query = "select count(f.id) as entries, f.cas_sugar_module as module, f.cas_sugar_object_id as record_id from pmse_bpm_flow f group by f.cas_sugar_object_id order by count(f.id) asc";
            $stmt = $this->conn->executeQuery($query, []);
            $this->writeln('done.');
            $this->writeln('');

            while ($row = $stmt->fetch()) {
                $allValues[] = $row;

                // get some summary info per module
                if (empty($statsData[$row['module']])) {
                    $statsData[$row['module']] = [
                        'max' => 0,
                        'min' => 0,
                        'total' => 0,
                        'entries' => 0,
                        'avg' => 0,
                        'maxSampleEntry' => '',
                        'minSampleEntry' => '',
                    ];
                }

                $statsData[$row['module']]['entries']++;
                $statsData[$row['module']]['total'] += $row['entries'];
                $statsData[$row['module']]['avg'] = $statsData[$row['module']]['total'] / $statsData[$row['module']]['entries'];

                // new minimum
                if (empty($statsData[$row['module']]['min']) || $row['entries'] < $statsData[$row['module']]['min']) {
                    $statsData[$row['module']]['min'] = $row['entries'];
                    $statsData[$row['module']]['minSampleEntry'] = $row['record_id'];
                }

                // new maximum
                if ($row['entries'] > $statsData[$row['module']]['max']) {
                    $statsData[$row['module']]['max'] = $row['entries'];
                    $statsData[$row['module']]['maxSampleEntry'] = $row['record_id'];
                }
            }

            if (!empty($allValues)) {

                if ($outputAll) {
                    $this->writeln('The list of all the entries can be found below:');
                    foreach ($allValues as $value) {
                        $this->writeln('Entries: ' . $this->formatNumber($value['entries'], 0) . '; Module: ' . $value['module'] . '; Record id: ' . $value['record_id']);
                    }
                    $this->writeln('');
                }

                $this->writeln('The summary of entries on a per-module basis can be found below:');
                foreach ($statsData as $module => $content) {
                    $this->writeln('');
                    $this->writeln($module . ':');
                    $this->writeln('Min: ' . $this->formatNumber($content['min'], 0) . '; Max: ' . $this->formatNumber($content['max'], 0) . '; Average: ' . $this->formatNumber($content['avg'], 2) .
                        '; Total: ' . $this->formatNumber($content['total'], 0) . '; Entries: ' . $this->formatNumber($content['entries'], 0));
                    $this->writeln('Sample Min ' . $module  . ' record id: ' . $content['minSampleEntry']);
                    $this->writeln('Sample Max ' . $module  . ' record id: ' . $content['maxSampleEntry']);
                }

                $this->writeln('');
                $this->writeln('');
            }
        } else {
            // TODO
        }
    }

    protected function analysisRecordGrowthPerProcessPerMonth()
    {
        if ($this->isMySQL()) {
            $this->getConn();

            $allValues = [];

            $this->writeln('');
            $this->write('Retrieving information about record growth over time, please wait... ');
            $query = "select count(f.pro_id) as entries, f.pro_id as process, DATE_FORMAT(f.date_entered, '%Y-%m') as month from pmse_bpm_flow f group by f.pro_id, DATE_FORMAT(f.date_entered, '%Y-%m')";
            $stmt = $this->conn->executeQuery($query, []);
            $this->writeln('done.');
            $this->writeln('');

            $this->writeln('The list of all the entries by month can be found below:');

            while ($row = $stmt->fetch()) {
                if (!empty($row['process'])) {
                    $this->writeln('Process: ' . $this->getBPMProcessFromFlowProId($row['process']) . '; Month: ' . $row['month'] . '; Entries: ' . $this->formatNumber($row['entries'], 0));
                }
            }

            $this->writeln('');
            $this->writeln('');
        } else {
            // TODO
            // find equivalent queries and/or trasform what is possible into proper doctrine (it might be hard for queries containing DATE_FORMAT)
        }
    }
}
