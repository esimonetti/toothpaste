<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Toothpaste\Sugar;

class CustomTableOrphansCleanupCommand extends Command
{
    protected static $defaultName = 'local:data:clean:custom-orphans';

    protected function configure()
    {
        $this
            ->setDescription('Delete from the database orphan records from all custom tables')
            ->setHelp('Command to delete from the database orphan records from all custom tables')
            ->addOption('instance', null, InputOption::VALUE_REQUIRED, 'Instance relative or absolute path')
            ->addOption('yes-hard-delete-live-data', null, InputOption::VALUE_NONE, 'Flag to consent to hard delete live system data without a backup')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \Toothpaste\Toothpaste::resetStartTime();

        $output->writeln('Executing database delete of orhpan records from all custom tables...');

        $instance = $input->getOption('instance');
        $consentToDelete = $input->getOption('yes-hard-delete-live-data');
        if (empty($consentToDelete)) {
            $output->writeln('Please consent to hard delete live data without a backup from your system with the script option "--yes-hard-delete-live-data". Check with --help for the correct syntax');
        } else {
            if (empty($instance)) {
                $output->writeln('Please provide the instance path. Check with --help for the correct syntax');
            } else {
                $path = Sugar\Instance::validate($instance);

                if (!empty($path)) {
                    $output->writeln('Entering ' . $path . '...');
                    $output->writeln('Setting up instance...');
                    Sugar\Instance::setup();
                    $logic = new Sugar\Logic\CustomTablesOrphansCleanup();
                    $logic->setLogger($output);
                    $logic->cleanup();
                } else {
                    $output->writeln($instance . ' does not contain a valid Sugar installation. Aborting...');
                }
            }
        }
    }
}
