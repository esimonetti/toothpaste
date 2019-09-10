<?php

namespace Toothpaste\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Toothpaste\Sugar;

class RepairCommand extends Command
{
    protected static $defaultName = 'sugar:repair';

    protected function configure()
    {
        $this
            ->setDescription('Repair a Sugar instance')
            ->setHelp('This command helps you repair a Sugar instance')
            ->addOption('instance', null, InputOption::VALUE_REQUIRED, 'Instance relative or absolute path')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Executing Repair command...');

        $instance = $input->getOption('instance');
        $path = Sugar\Instance::validate($instance);

        \Toothpaste\Toothpaste::resetStartTime();

        if (!empty($path)) {
            $output->writeln('Entering ' . $path . '...');
            Sugar\Instance::setup();
            Sugar\Repair::executeSimpleRepair();
        } else {
            $output->writeln($instance . ' does not contain a valid Sugar installation. Aborting...');
        }
    }
}
