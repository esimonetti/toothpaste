<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Toothpaste\Sugar;

class SugarBPMAnalysisCommand extends Command
{
    protected static $defaultName = 'local:analysis:sugarbpm';

    protected function configure()
    {
        $this
            ->setDescription('Perform an analysis of SugarBPM records')
            ->setHelp('Command to perform an analysis of SugarBPM records to understand distribution, timing, usage etc.')
            ->addOption('instance', null, InputOption::VALUE_REQUIRED, 'Instance relative or absolute path')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Output on screen all data available')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \Toothpaste\Toothpaste::resetStartTime();

        $output->writeln('Executing analysis of SugarBPM records...');

        $outputAll = $input->getOption('all');
        $instance = $input->getOption('instance');
        if (empty($instance)) {
            $output->writeln('Please provide the instance path. Check with --help for the correct syntax');
        } else {
            $path = Sugar\Instance::validate($instance, $output);

            if (!empty($path)) {
                $output->writeln('Entering ' . $path . '...');
                $output->writeln('Setting up instance...');
                Sugar\Instance::setup();
                $logic = new Sugar\Logic\SugarBPMAnalysis();
                $logic->setLogger($output);
                $logic->performAnalysis($outputAll);
            } else {
                $output->writeln($instance . ' does not contain a valid Sugar installation. Aborting...');
            }
        }
    }
}
