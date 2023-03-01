<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Toothpaste\Sugar;

class FileSystemBenchmarkCommand extends Command
{
    protected static $defaultName = 'local:analysis:fsbenchmark';

    protected function configure()
    {
        $this
            ->setDescription('Perform a benchmark on the file system')
            ->setHelp('Command to perform a file system benchmark via PHP, to assess the range of performance of PHP on a file system')
            ->addOption('instance', null, InputOption::VALUE_REQUIRED, 'Instance relative or absolute path')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \Toothpaste\Toothpaste::resetStartTime();

        $output->writeln('Executing benchmark on the file system...');

        $instance = $input->getOption('instance');
        if (empty($instance)) {
            $output->writeln('Please provide the instance path. Check with --help for the correct syntax');
        } else {
            $path = Sugar\Instance::validate($instance, $output);

            if (!empty($path)) {
                $output->writeln('Entering ' . $path . '...');
                $logic = new Sugar\Logic\FileSystemBenchmark();
                $logic->setLogger($output);
                $logic->performFileSystemBenchmark();
            } else {
                $output->writeln($instance . ' does not contain a valid Sugar installation. Aborting...');
            }
        }
        return Command::SUCCESS;
    }
}
