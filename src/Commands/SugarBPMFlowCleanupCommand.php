<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Toothpaste\Sugar;

class SugarBPMFlowCleanupCommand extends Command
{
    protected static $defaultName = 'local:data:clean:sugarbpm';

    protected function configure()
    {
        $this
            ->setDescription('Delete from the database old completed and terminated SugarBPM flow records')
            ->setHelp('Command to delete from the database old completed and terminated SugarBPM flow records')
            ->addOption('instance', null, InputOption::VALUE_REQUIRED, 'Instance relative or absolute path')
            ->addOption('months', null, InputOption::VALUE_OPTIONAL, 'Number of months to keep completed and terminated records for. Older records will be deleted from the system', 3)
            ->addOption('yes-hard-delete-live-data', null, InputOption::VALUE_NONE, 'Flag to consent to hard delete live system data without a backup')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \Toothpaste\Toothpaste::resetStartTime();

        $output->writeln('Executing database delete of old SugarBPM flow records...');

        $months = $input->getOption('months');
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
                    $logic = new Sugar\Logic\SugarBPMCleanup();
                    $logic->setLogger($output);
                    $logic->delete($months);
                } else {
                    $output->writeln($instance . ' does not contain a valid Sugar installation. Aborting...');
                }
            }
        }
        return Command::SUCCESS;
    }
}
