<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Toothpaste\Sugar;

class MaintenanceOnCommand extends Command
{
    protected static $defaultName = 'local:maintenance:on';

    protected function configure()
    {
        $this
            ->setDescription('Set Maintenance mode on')
            ->setHelp('This command helps you set maintenance mode Sugar instance')
            ->addOption('instance', null, InputOption::VALUE_REQUIRED, 'Instance relative or absolute path')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \Toothpaste\Toothpaste::resetStartTime();

        $output->writeln('Setting maintenance mode on...');

        $instance = $input->getOption('instance');
        if (empty($instance)) {
            $output->writeln('Please provide the instance path. Check with --help for the correct syntax');
        } else {
            $path = Sugar\Instance::validate($instance, $output);

            if (!empty($path)) {
                $output->writeln('Entering ' . $path . '...');
                $output->writeln('Setting up instance...');
                Sugar\Instance::setup();
                $logic = new Sugar\Logic\MaintenanceMode();
                $logic->setLogger($output);
                $logic->changeMaintenanceSetting(true);
            } else {
                $output->writeln($instance . ' does not contain a valid Sugar installation. Aborting...');
            }
        }
    }
}
