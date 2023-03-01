<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Toothpaste\Sugar;

class SugarFormulasUsageCommand extends Command
{
    protected static $defaultName = 'local:analysis:formulas';

    protected function configure()
    {
        $this
            ->setDescription('List all Sugar Logic formulas and single out the related ones')
            ->setHelp('Command to check all Sugar logic formulas')
            ->addOption('instance', null, InputOption::VALUE_REQUIRED, 'Instance relative or absolute path')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \Toothpaste\Toothpaste::resetStartTime();

        $output->writeln('Executing check all Sugar Logic formulas in use...');

        $instance = $input->getOption('instance');
        if (empty($instance)) {
            $output->writeln('Please provide the instance path. Check with --help for the correct syntax');
        } else {
            $path = Sugar\Instance::validate($instance, $output);

            if (!empty($path)) {
                $output->writeln('Entering ' . $path . '...');
                $output->writeln('Setting up instance...');
                Sugar\Instance::setup();
                $logic = new Sugar\Logic\SugarFormulas();
                $logic->setLogger($output);
                $logic->findFormulas();
                $logic->findRelatedFormulas();
                $logic->findCustomFilesWithFormulas();
            } else {
                $output->writeln($instance . ' does not contain a valid Sugar installation. Aborting...');
            }
        }
        return Command::SUCCESS;
    }
}
