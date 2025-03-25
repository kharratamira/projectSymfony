<?php

namespace App\Command;

use App\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:add-roles', // Make sure this is correct
    description: 'Adds initial roles to the database.'
)]
class AddRolesCommand extends Command
{ 
    

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Adds initial roles to the database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $roles = ['ROLE_ADMIN', 'ROLE_COMMERCIAL', 'ROLE_TECHNICIEN', 'ROLE_CLIENT'];

        foreach ($roles as $roleName) {
            $role = new Role();
            $role->setNomRole($roleName);
            $this->entityManager->persist($role);
        }

        $this->entityManager->flush();

        $output->writeln('Roles added successfully.');
        return Command::SUCCESS;
    }

    }

