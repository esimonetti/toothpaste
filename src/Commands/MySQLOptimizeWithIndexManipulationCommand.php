<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Toothpaste\Sugar;

class MySQLOptimizeWithIndexManipulationCommand extends Command
{
    protected static $defaultName = 'local:mysql:optimize-with-indexes-manipulations';

    protected function configure()
    {
        $this
            ->setDescription('Optimize all MySQL tables by dropping and re-creating all indexes and also by running MySQL OPTIMIZE - EXPERIMENTAL, USE AT OWN RISK!')
            ->setHelp('Run MySQL OPTIMIZE on all MySQL tables of this instance, with drop and re-creation of indexes. This command might disrupt your indexes if not created through the extension framework. It will temporarily lock the table for write purposes.')
            ->addOption('instance', null, InputOption::VALUE_REQUIRED, 'Instance relative or absolute path')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \Toothpaste\Toothpaste::resetStartTime();

        $output->writeln('Executing MySQL Optimize command across all tables...');

        $instance = $input->getOption('instance');

        if (empty($instance)) {
            $output->writeln('Please provide the instance path. Check with --help for the correct syntax');
        } else {
            $path = Sugar\Instance::validate($instance);

            $logic = new Sugar\Actions\MySQLOptimizeIndex();
            $logic->setLogger($output);

            if (!empty($path)) {
                $output->writeln('Entering ' . $path . '...');
                $output->writeln('Setting up instance...');
                Sugar\Instance::setup();
                $logic->executeTablesOptimize();
            } else {
                $output->writeln($instance . ' does not contain a valid Sugar installation. Aborting...');
            }
        }
    }
}
