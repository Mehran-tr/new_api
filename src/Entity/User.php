<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Repository\UserRepository;
use App\Enum\Role;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[UniqueEntity('name')]
#[ApiResource(

    operations: [
        new GetCollection(
            security: "is_granted('ROLE_SUPER_ADMIN') or is_granted('ROLE_COMPANY_ADMIN') or is_granted('ROLE_USER')"
        ),
        new Get(
            security: "is_granted('ROLE_SUPER_ADMIN') or (is_granted('ROLE_COMPANY_ADMIN') or is_granted('ROLE_USER') and object.company == user.company)"
        ),
        new Post(
            security: "is_granted('ROLE_SUPER_ADMIN') or is_granted('ROLE_COMPANY_ADMIN')"
        ),
        new Delete(
            security: "is_granted('ROLE_SUPER_ADMIN')"
        ),
    ]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 100)]
    #[Assert\Regex(
        pattern: '/^(?=.*[A-Z])[A-Za-z ]+$/',
        message: 'Name must contain at least one uppercase letter and only letters and spaces'
    )]
    private string $name;

    #[ORM\Column(type: 'string', length: 255)]
    private string $password;

    #[ORM\Column(type: 'string', enumType: Role::class, length: 50)]
    #[Assert\NotBlank]
    private Role $role;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Company $company = null;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function setRole(Role $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getRoles(): array
    {
        return [$this->role->value];
    }

    public function getUserIdentifier(): string
    {
        return $this->name; // Use name as the user identifier
    }

    public function eraseCredentials(): void
    {
    }

    public function getSalt(): ?string
    {
        return null;
    }
}
