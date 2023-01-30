<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Toothpaste\Sugar;

class RepairMissingTablesCommand extends Command
{
    protected static $defaultName = 'local:system:repair-missing-tables';

    protected function configure()
    {
        $this
            ->setDescription('Repair missing SQL tables and align the database schema to Sugar\'s definitions')
            ->setHelp('This command helps align the database schema of an incomplete system provided as a backup to Sugar\'s file system definitions')
            ->addOption('instance', null, InputOption::VALUE_REQUIRED, 'Instance relative or absolute path')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \Toothpaste\Toothpaste::resetStartTime();

        $output->writeln('Executing repair of missing SQL tables...');

        $instance = $input->getOption('instance');
        if (empty($instance)) {
            $output->writeln('Please provide the instance path. Check with --help for the correct syntax');
        } else {
            $path = Sugar\Instance::validate($instance, $output);

            if (!empty($path)) {
                $output->writeln('Entering ' . $path . '...');
                $output->writeln('Setting up instance without user first...');
                // do not setup the current user yet, as the system might contain corrupted tables
                Sugar\Instance::setup(false);
                $logic = new Sugar\Logic\RepairMissingTables();
                $logic->setLogger($output);
                $logic->performInitialRepair();
                // now we can setup the current user to complete the repair
                $output->writeln('Setting up system user...');
                Sugar\Instance::loadSystemUser();
                $logic->performFinalRepair();
            } else {
                $output->writeln($instance . ' does not contain a valid Sugar installation. Aborting...');
            }
        }
        return 1;
    }
}
