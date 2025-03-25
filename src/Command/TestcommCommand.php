<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'testcomm',
    description: 'Add a short description for your command',
)]
class TestcommCommand extends Command
{
    protected static $defaultName = 'app:test';

    protected function configure()
    {
        $this->setDescription('A simple test command.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Test command executed successfully.');
        return Command::SUCCESS;
    }}
