<?php

namespace App\Command;

use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCompanyCommand extends Command
{
    protected static $defaultName = 'app:create-company';  // Define the command name

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setName('app:create-company')  // Explicitly set the command name
            ->setDescription('Create a new company')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the company');
    }
    /*
     * sample php bin/console app:create-company "Example Company"
     * */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $name = $input->getArgument('name');

        // Check if the company name already exists
        $existingCompany = $this->entityManager->getRepository(Company::class)->findOneBy(['name' => $name]);
        if ($existingCompany) {
            $output->writeln('<error>Company with this name already exists.</error>');
            return Command::FAILURE;
        }

        // Create new company
        $company = new Company();
        $company->setName($name);

        // Persist the company to the database
        $this->entityManager->persist($company);
        $this->entityManager->flush();

        $output->writeln('<info>Company created successfully!</info>');

        return Command::SUCCESS;
    }
}
