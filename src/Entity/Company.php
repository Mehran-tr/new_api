<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\CompanyRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[ApiResource]
#[UniqueEntity(fields: ['name'], message: 'This company name is already used.')]
#[ApiResource(
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_USER') or is_granted('ROLE_COMPANY_ADMIN') or is_granted('ROLE_SUPER_ADMIN')"
        ),
        new Get(
            security: "is_granted('ROLE_USER') or is_granted('ROLE_COMPANY_ADMIN') or is_granted('ROLE_SUPER_ADMIN')"
        ),
        new Post(
            security: "is_granted('ROLE_SUPER_ADMIN')"
        )
    ]
)]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 5, max: 100)]
    private string $name;

    // Getters and Setters

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
}
