<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Toothpaste\Sugar;

class LogicHooksUsageCommand extends Command
{
    protected static $defaultName = 'local:analysis:logichooks';

    protected function configure()
    {
        $this
            ->setDescription('Check if custom Logic Hooks are in use')
            ->setHelp('Command to check if custom Logic Hooks are in use anywhere in the system, and provide a breakdown of the usage')
            ->addOption('instance', null, InputOption::VALUE_REQUIRED, 'Instance relative or absolute path')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \Toothpaste\Toothpaste::resetStartTime();

        $output->writeln('Executing check if custom Logic Hooks are in use...');

        $instance = $input->getOption('instance');
        if (empty($instance)) {
            $output->writeln('Please provide the instance path. Check with --help for the correct syntax');
        } else {
            $path = Sugar\Instance::validate($instance);

            if (!empty($path)) {
                $output->writeln('Entering ' . $path . '...');
                $output->writeln('Setting up instance...');
                Sugar\Instance::setup();
                $logic = new Sugar\Logic\LogicHooks();
                $logic->setLogger($output);
                $logic->findHooks();
            } else {
                $output->writeln($instance . ' does not contain a valid Sugar installation. Aborting...');
            }
        }
    }
}
