<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Toothpaste\Sugar;

class ExtractMetadataCommand extends Command
{
    protected static $defaultName = 'local:system:metadata:extract';

    protected function configure()
    {
        $this
            ->setDescription('Save the current metadata cache on disk in a plaintext array')
            ->setHelp('This command helps extract the current metadata cache on disk in a plaintext array to allow comparison between two metadata contents')
            ->addOption('instance', null, InputOption::VALUE_REQUIRED, 'Instance relative or absolute path')
            ->addOption('dir', null, InputOption::VALUE_REQUIRED, 'Output directory')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \Toothpaste\Toothpaste::resetStartTime();

        $output->writeln('Executing generate metadata command...');

        $instance = $input->getOption('instance');
        if (empty($instance)) {
            $output->writeln('Please provide the instance path. Check with --help for the correct syntax');
        } else {
            $dir = $input->getOption('dir');
            if (empty($dir)) {
                $output->writeln('Please make sure all required parameters are passed correctly to the command. Check with --help for the correct syntax');
            } else {
                $path = Sugar\Instance::validate($instance);

                if (!empty($path)) {
                    $output->writeln('Entering ' . $path . '...');
                    $output->writeln('Setting up instance...');
                    Sugar\Instance::setup();
                    $logic = new Sugar\Logic\Metadata();
                    $logic->setLogger($output);
                    $logic->extractContent($dir);
                } else {
                    $output->writeln($instance . ' does not contain a valid Sugar installation. Aborting...');
                }
            }
        }
    }
}
