<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Enum\Role;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ApiRoutesTest extends WebTestCase
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

    public function testLoginSuccess(): void
    {
        // Make a login request with valid credentials
        $this->client->jsonRequest('POST', '/api/login', [
            'name' => 'testuser',
            'password' => 'testpassword',
        ]);

        // Assert that the response status code is 200 (OK)
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        // Assert that the response contains a JWT token
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $responseContent);
    }

    public function testGetUsers(): void
    {
        // Authenticate first
        $token = $this->loginAndGetToken();

        // Send GET request to fetch users
        $this->client->request('GET', '/api/users', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        // Assert that the response status code is 200 (OK)
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        // Assert that the response contains the user list
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseContent);
    }

    public function testGetUser(): void
    {
        $token = $this->loginAndGetToken();

        // Get a specific user by id (assuming 1 is the id of a test user)
        $this->client->request('GET', '/api/users/1', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        // Assert that the response status code is 200 (OK)
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        // Assert that the response contains the user data
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('name', $responseContent);
    }

    public function testUpdateUser(): void
    {
        $token = $this->loginAndGetToken();

        // Update user data (assuming 1 is the id of a test user)
        $this->client->jsonRequest('PUT', '/api/users/1', [
            'name' => 'updateduser',
        ], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        // Assert that the response status code is 200 (OK)
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        // Assert that the response contains the updated user data
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('updateduser', $responseContent['name']);
    }

    public function testDeleteUser(): void
    {
        $token = $this->loginAndGetToken();

        // Delete user by id (assuming 1 is the id of a test user)
        $this->client->request('DELETE', '/api/users/1', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        // Assert that the response status code is 204 (No Content)
        $this->assertSame(204, $this->client->getResponse()->getStatusCode());
    }

    private function loginAndGetToken(): string
    {
        // Login and get the JWT token
        $this->client->jsonRequest('POST', '/api/login', [
            'name' => 'testuser',
            'password' => 'testpassword',
        ]);

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        return $responseContent['token'];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if ($this->entityManager) {
            $this->entityManager->close();
            $this->entityManager = null; // Avoid memory leaks
        }
    }
}
