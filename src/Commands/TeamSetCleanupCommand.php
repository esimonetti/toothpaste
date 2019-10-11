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
    protected static $defaultName = 'sugar:teamset:clean';

    protected function configure()
    {
        $this
            ->setDescription('Soft delete unused teamsets - EXPERIMENTAL, USE AT OWN RISK!')
            ->setHelp('Soft delete unused teamsets')
            ->addOption('instance', null, InputOption::VALUE_REQUIRED, 'Instance relative or absolute path')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \Toothpaste\Toothpaste::resetStartTime();

        $output->writeln('Executing soft-delete of team sets records not found across all tables...');

        $instance = $input->getOption('instance');
        if (empty($instance)) {
            $output->writeln('Please provide the instance path. Check with --help for the correct syntax');
        } else {
            $path = Sugar\Instance::validate($instance);

            if (!empty($path)) {
                $output->writeln('Entering ' . $path . '...');
                $output->writeln('Setting up instance...');
                Sugar\Instance::setup();

                $logic = new Sugar\Actions\TeamSetsCleanup();
                $logic->setLogger($output);

                $deleted = $logic->softDeleteUnusedTeamSets();
                $output->writeln('Soft deleted ' . $deleted . ' unused team sets.');
                if ($deleted > 0) {
                    $output->writeln('');
                    $output->writeln('Deleted the following team sets:');
                    $output->writeln($logic->getDeletedTeamSets());
                    $output->writeln('');
                    $output->writeln('To revert, execute the following SQL queries:');
                    $output->writeln($logic->getUndeleteTeamSetsQueries());
                    $output->writeln('');
                    $output->writeln('To proceed with the hard delete of the soft deleted records perform the following SQL queries');
                    $output->writeln('DELETE from where deleted=\'1\'');
                }
            } else {
                $output->writeln($instance . ' does not contain a valid Sugar installation. Aborting...');
            }
        }
    }
}
