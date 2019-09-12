<?php

namespace Toothpaste\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Toothpaste\Sugar;

class MySQLOptimizeWithIndexManipulationCommand extends Command
{
    protected static $defaultName = 'sugar:mysql:optimizewithindex';

    protected function configure()
    {
        $this
            ->setDescription('Optimize all MySQL tables by dropping and re-creating all indexes and also by running MySQL OPTIMIZE')
            ->setHelp('Run MySQL OPTIMIZE on all MySQL tables of this instance, with drop and re-creation of indexes. This command might disrupt your indexes if not created through the extension framework. It will temporarily lock the table for write purposes.')
            ->addOption('instance', null, InputOption::VALUE_REQUIRED, 'Instance relative or absolute path')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Executing MySQL Optimize command across all tables...');

        $instance = $input->getOption('instance');
        $path = Sugar\Instance::validate($instance);

        \Toothpaste\Toothpaste::resetStartTime();

        if (!empty($path)) {
            $output->writeln('Entering ' . $path . '...');
            Sugar\Instance::setup();
            Sugar\Actions\MySQLOptimizeIndex::executeTablesOptimize();
        } else {
            $output->writeln($instance . ' does not contain a valid Sugar installation. Aborting...');
        }
    }
}
