<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Toothpaste\Sugar;

class GenerateMetadataCommand extends Command
{
    protected static $defaultName = 'local:system:metadata:generate';

    protected function configure()
    {
        $this
            ->setDescription('Remove and generate metadata cache for all users/roles combinations')
            ->setHelp('This command helps remove and generate metadata cache for all users/roles combinations')
            ->addOption('instance', null, InputOption::VALUE_REQUIRED, 'Instance relative or absolute path')
            ->addOption('mac', null, InputOption::VALUE_NONE, 'Flag to generate metadata on behalf of a browser on a Mac platform')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \Toothpaste\Toothpaste::resetStartTime();

        $output->writeln('Executing generate metadata command...');

        $instance = $input->getOption('instance');
        $isForMac = $input->getOption('mac');
        if (empty($instance)) {
            $output->writeln('Please provide the instance path. Check with --help for the correct syntax');
        } else {
            $path = Sugar\Instance::validate($instance, $output);

            if (!empty($path)) {
                $output->writeln('Entering ' . $path . '...');
                $output->writeln('Setting up instance...');
                Sugar\Instance::setup();
                $logic = new Sugar\Logic\Metadata();
                $logic->setLogger($output);
                $logic->generate($isForMac);
            } else {
                $output->writeln($instance . ' does not contain a valid Sugar installation. Aborting...');
            }
        }
        return 1;
    }
}
