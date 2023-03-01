<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Toothpaste\Sugar;

class TeamSetCleanupCommand extends Command
{
    protected static $defaultName = 'local:data:clean:teams';

    protected function configure()
    {
        $this
            ->setDescription('Clean up TeamSets')
            ->setHelp('Clean up TeamSets')
            ->addOption('instance', null, InputOption::VALUE_REQUIRED, 'Instance relative or absolute path')
            ->addOption('yes-hard-delete-live-data', null, InputOption::VALUE_NONE, 'Flag to consent to hard delete live system data without a backup')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \Toothpaste\Toothpaste::resetStartTime();

        $output->writeln('Executing clean up of TeamSets...');

        $instance = $input->getOption('instance');
        $consentToDelete = $input->getOption('yes-hard-delete-live-data');
        if (empty($consentToDelete)) {
            $output->writeln('Please consent to hard delete live data without a backup from your system with the script option "--yes-hard-delete-live-data". Check with --help for the correct syntax');
        } else {
            if (empty($instance)) {
                $output->writeln('Please provide the instance path. Check with --help for the correct syntax');
            } else {
                $path = Sugar\Instance::validate($instance, $output);

                if (!empty($path)) {
                    $output->writeln('Entering ' . $path . '...');
                    $output->writeln('Setting up instance...');
                    Sugar\Instance::setup();
                    $logic = new Sugar\Logic\TeamSetsCleanup();
                    $logic->setLogger($output);
                    $logic->performFullCleanup();
                } else {
                    $output->writeln($instance . ' does not contain a valid Sugar installation. Aborting...');
                }
            }
        }
        return Command::SUCCESS;
    }
}
