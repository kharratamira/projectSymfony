<?php

namespace App\Command;

use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:test-email')]
class TestEmailCommand extends Command
{
    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = (new TemplatedEmail())
            ->from('amirakharrat541@gmail.com')
            ->to('amirakharrat@gmail.com')
            ->subject('Test Email')
            ->text('This is a test email from Symfony');

        try {
            $this->mailer->send($email);
            $output->writeln('Email sent successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->logger->error('Email test failed: '.$e->getMessage());
            $output->writeln('Error: '.$e->getMessage());
            return Command::FAILURE;
        }
    }
}
