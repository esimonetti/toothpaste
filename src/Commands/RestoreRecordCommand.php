<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Toothpaste\Sugar;

class RestoreRecordCommand extends Command
{
    protected static $defaultName = 'local:data:restore-record';

    protected function configure()
    {
        $this
            ->setDescription('Restore a soft-deleted record (if present) and most of its relationships')
            ->setHelp('Command to restore a soft-deleted record (if present) and most of its relationships. Some one-to-many relationships cannot be restored without an actual backup')
            ->addOption('instance', null, InputOption::VALUE_REQUIRED, 'Instance relative or absolute path')
            ->addOption('module', null, InputOption::VALUE_REQUIRED, 'Module to restore')
            ->addOption('record', null, InputOption::VALUE_REQUIRED, 'Record id to restore')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \Toothpaste\Toothpaste::resetStartTime();

        $output->writeln('Executing restore of a soft-deleted record...');

        $instance = $input->getOption('instance');
        $module = $input->getOption('module');
        $record = $input->getOption('record');
        if (empty($instance)) {
            $output->writeln('Please provide the instance path. Check with --help for the correct syntax');
        } else {
            $path = Sugar\Instance::validate($instance, $output);

            if (!empty($path)) {
                if (!empty($module) && !empty($record)) {
                    $output->writeln('Entering ' . $path . '...');
                    $output->writeln('Setting up instance...');
                    Sugar\Instance::setup();
                    $logic = new Sugar\Logic\RestoreRecord();
                    $logic->setLogger($output);
                    $logic->restore($module, $record);
                } else {
                    $output->writeln('Please provide the module name and the single record id to restore. Check with --help for the correct syntax');
                }
            } else {
                $output->writeln($instance . ' does not contain a valid Sugar installation. Aborting...');
            }
        }
    }
}
