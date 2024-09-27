<?php


namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security; // Use the correct Security class
use App\Entity\User;
use ApiPlatform\Metadata\Operation;

class UserCompanyExtension implements QueryCollectionExtensionInterface
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if ($resourceClass !== User::class) {
            return;
        }

        $user = $this->security->getUser();
        if ($user && (in_array('ROLE_COMPANY_ADMIN', $user->getRoles()) || in_array('ROLE_USER', $user->getRoles()))) {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $queryBuilder->andWhere(sprintf('%s.company = :company', $rootAlias))
                ->setParameter('company', $user->getCompany());
        }
    }
}
