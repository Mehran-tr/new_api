<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Enum\Role;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ApiLoginTest extends WebTestCase
{
    private ?EntityManagerInterface $entityManager = null;
    private ?UserPasswordHasherInterface $passwordHasher = null;
    private $client;

    protected function setUp(): void
    {
        // Boot the test client
        $this->client = static::createClient();

        // Ensure container services are available
        $container = static::getContainer();

        // Get services from the container
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);

        // Check if the test user exists and create it if not
        $this->checkOrCreateTestUser();
    }

    private function checkOrCreateTestUser(): void
    {
        if (!$this->entityManager) {
            throw new \Exception("EntityManager is not initialized");
        }

        $userRepository = $this->entityManager->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['name' => 'testuser']);

        if (!$testUser) {
            // Create new test user if it doesn't exist
            $user = new User();
            $user->setName('testuser');
            $user->setRole(Role::ROLE_USER);
            $hashedPassword = $this->passwordHasher->hashPassword($user, 'testpassword');
            $user->setPassword($hashedPassword);
            $user->setCompany(null); // Assuming company is nullable

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }

    public function test(): void
    {
        $this->assertTrue(true);
    }

    // Uncomment and implement the following tests when you want to test login functionality

    // public function testLoginSuccess(): void
    // {
    //     // Make a login request with valid credentials
    //     $this->client->jsonRequest('POST', '/api/login', [
    //         'name' => 'testuser',
    //         'password' => 'testpassword',
    //     ]);

    //     // Assert that the response status code is 200 (OK)
    //     $this->assertSame(200, $this->client->getResponse()->getStatusCode());

    //     // Assert that the response contains a JWT token
    //     $responseContent = json_decode($this->client->getResponse()->getContent(), true);
    //     $this->assertArrayHasKey('token', $responseContent);
    // }

    // public function testLoginFailure(): void
    // {
    //     // Make a login request with invalid credentials
    //     $this->client->jsonRequest('POST', '/api/login', [
    //         'name' => 'wronguser',
    //         'password' => 'wrongpassword',
    //     ]);

    //     // Assert that the response status code is 401 (Unauthorized)
    //     $this->assertSame(401, $this->client->getResponse()->getStatusCode());
    // }

    protected function tearDown(): void
    {
        parent::tearDown();
        if ($this->entityManager) {
            $this->entityManager->close();
            $this->entityManager = null; // Avoid memory leaks
        }
    }
}
