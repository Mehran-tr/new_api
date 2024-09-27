<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Company;
use App\Enum\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateUserCommand extends Command
{
    protected static $defaultName = 'app:create-user';  // Use this to define the command name

    private $entityManager;
    private $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function configure()
    {
        $this
            ->setName('app:create-user')  // Explicitly set the command name
            ->setDescription('Create a new user with a specific role')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the user')
            ->addArgument('role', InputArgument::REQUIRED, 'The role of the user (ROLE_USER, ROLE_COMPANY_ADMIN, ROLE_SUPER_ADMIN)')
            ->addArgument('password', InputArgument::REQUIRED, 'The password of the user')
            ->addArgument('companyId', InputArgument::OPTIONAL, 'The ID of the company (required for ROLE_USER and ROLE_COMPANY_ADMIN)');
    }

    /*
     * sample:
     * For ROLE_USER or ROLE_COMPANY_ADMIN (company required):
     *   php bin/console app:create-user "John Doe" ROLE_USER "secure_password" 1
     *  php bin/console app:create-user "Company James Doe" ROLE_COMPANY_ADMIN "secure_password" 1

     *  php bin/console app:create-user "Super Jane Doe" ROLE_SUPER_ADMIN "secure_password"
     * */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $role = $input->getArgument('role');
        $password = $input->getArgument('password');
        $companyId = $input->getArgument('companyId');

        $user = new User();
        $user->setName($name);

        // Hash the password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        // Set the role
        if (!in_array($role, ['ROLE_USER', 'ROLE_COMPANY_ADMIN', 'ROLE_SUPER_ADMIN'])) {
            $output->writeln('<error>Invalid role. Must be ROLE_USER, ROLE_COMPANY_ADMIN, or ROLE_SUPER_ADMIN</error>');
            return Command::FAILURE;
        }
        $user->setRole(Role::from($role));

        // Assign company if required
        if (in_array($role, ['ROLE_USER', 'ROLE_COMPANY_ADMIN'])) {
            if (!$companyId) {
                $output->writeln('<error>Company ID is required for ROLE_USER and ROLE_COMPANY_ADMIN.</error>');
                return Command::FAILURE;
            }

            $company = $this->entityManager->getRepository(Company::class)->find($companyId);
            if (!$company) {
                $output->writeln('<error>Company not found.</error>');
                return Command::FAILURE;
            }
            $user->setCompany($company);
        }

        // Persist the user to the database
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln('<info>User created successfully!</info>');

        return Command::SUCCESS;
    }
}
