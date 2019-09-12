<?php

namespace Toothpaste\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Toothpaste\Sugar;

class RecordCountCommand extends Command
{
    protected static $defaultName = 'sugar:count';

    protected function configure()
    {
        $this
            ->setDescription('Count records per database table')
            ->setHelp('Count the amount of records per database table')
            ->addOption('instance', null, InputOption::VALUE_REQUIRED, 'Instance relative or absolute path')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Executing count of database records across all tables...');

        $instance = $input->getOption('instance');
        $path = Sugar\Instance::validate($instance);

        \Toothpaste\Toothpaste::resetStartTime();

        if (!empty($path)) {
            $output->writeln('Entering ' . $path . '...');
            Sugar\Instance::setup();
            Sugar\Actions\RecordCount::count();
        } else {
            $output->writeln($instance . ' does not contain a valid Sugar installation. Aborting...');
        }
    }
}
