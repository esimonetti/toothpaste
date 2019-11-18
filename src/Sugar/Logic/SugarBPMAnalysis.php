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

    public function performAnalysis()
    {
        $this->writeln('Performing analysis of SugarBPM usage');

        $this->analysisRecordGrowth();
        $this->analysisRecordGrowthPerProcess();
        $this->analysisRecordsPerModulesRecord();
        $this->analysisRecordGrowthPerProcessPerMonth();

        $this->writeln('Analysis of SugarBPM usage completed');
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
                if ($highestValues == $lowestValues) {
                    // TODO
                    // only one sample? bad data?
                } else {
                    // tell me something smart
                    $this->writeln('Average entries: ' . $averageValue . ' across ' . count($allValues) . ' months');
                    $this->writeln('Min: ' . $lowestValues['entries'] . ' entries for the month of: ' . $lowestValues['month'] . ' with a change from the average of ' . 
                        $this->formatNumber((abs($lowestValues['entries'] - $averageValue)) / ($averageValue / 100)) . '%');
                    $this->writeln('Max: ' . $highestValues['entries'] . ' entries for the month of: ' . $highestValues['month'] . ' with a change from the average of ' .
                        $this->formatNumber((abs($highestValues['entries'] - $averageValue)) / ($averageValue / 100)) . '%');
                }

                $this->writeln('');
                $this->writeln('The list of all the entries by month can be found below:');
                foreach ($allValues as $value) {
                    $this->writeln('Month: ' . $value['month'] . ' Entries: ' . $value['entries']);
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
                $this->processes[$id] = $row['name'] . ' - ' . $row['status'] . ' Module: ' . $row['module'] . ' (Process id: ' . $row['id'] . ')';
            }

            return $this->processes[$id];
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
            $this->write('Retrieving information about record growth per process over time, please wait... ');
            $query = "select count(f.id) as entries, f.pro_id as process, f.cas_flow_status as status from pmse_bpm_flow f group by f.pro_id, f.cas_flow_status";
            $stmt = $this->conn->executeQuery($query, []);
            $this->writeln('done.');
            $this->writeln('');

            while ($row = $stmt->fetch()) {
                $allValues[] = $row;
            }

            if (!empty($allValues)) {
                $this->writeln('');
                $this->writeln('The list of all the entries can be found below:');

                foreach ($allValues as $value) {
                    // initialise for totals
                    if (empty($totalProcesses[$value['process']])) {
                        // how can i output the previous value?
                        $totalProcesses[$value['process']] = $value['entries'];
                    } else {
                        $totalProcesses[$value['process']] += $value['entries'];
                    }
                }

                foreach ($allValues as $value) {
                    $this->writeln('Process: ' . $this->getBPMProcessFromFlowProId($value['process']) . ' Status: ' . $value['status']  . ' Entries: ' . $value['entries'] . ' / total ' . $totalProcesses[$value['process']]);
                }

                $this->writeln('');
                $this->writeln('');
            }
        } else {
            // TODO
        }
    }

    protected function analysisRecordsPerModulesRecord()
    {
        if ($this->isMySQL()) {
            $this->getConn();
            $allValues = [];
            $totalProcesses = [];

            $this->writeln('');
            $this->write('Retrieving information about SugarBPM records per Sugar record entry, please wait... ');
            $query = "select count(f.id) as entries, f.cas_sugar_module as module, f.cas_sugar_object_id as record_id from pmse_bpm_flow f group by f.cas_sugar_object_id order by count(f.id) asc";
            $stmt = $this->conn->executeQuery($query, []);
            $this->writeln('done.');
            $this->writeln('');

            while ($row = $stmt->fetch()) {
                $allValues[] = $row;
            }

            if (!empty($allValues)) {
                $this->writeln('');
                $this->writeln('The list of all the entries can be found below:');

                foreach ($allValues as $value) {
                    $this->writeln('Entries: ' . $value['entries'] . ' Module: ' . $value['module'] . ' Record id: ' . $value['record_id']);
                }

                // TODO we should calculate average per module and outliers per module if any

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

            $this->writeln('');
            $this->writeln('The list of all the entries by month can be found below:');

            while ($row = $stmt->fetch()) {
                $this->writeln('Process: ' . $this->getBPMProcessFromFlowProId($row['process']) . ' Month: ' . $row['month'] . ' Entries: ' . $row['entries']);
            }

            $this->writeln('');
            $this->writeln('');
        } else {
            // TODO
            // find equivalent queries and/or trasform what is possible into proper doctrine (it might be hard for queries containing DATE_FORMAT)
        }
    }
}
