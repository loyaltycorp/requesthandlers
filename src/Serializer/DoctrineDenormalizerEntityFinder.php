<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Serializer;

use Doctrine\Common\Persistence\ManagerRegistry;
use LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerEntityFinderClassException;
use LoyaltyCorp\RequestHandlers\Serializer\Interfaces\DoctrineDenormalizerEntityFinderInterface;

class DoctrineDenormalizerEntityFinder implements DoctrineDenormalizerEntityFinderInterface
{
    /**
     * @var \Doctrine\Common\Persistence\ManagerRegistry
     */
    private $entityManager;

    /**
     * Create entity finder.
     *
     * @param \Doctrine\Common\Persistence\ManagerRegistry $entityManager
     */
    public function __construct(ManagerRegistry $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Find an entity by given criteria.
     *
     * @param string $class
     * @param mixed[] $criteria
     * @param mixed[]|null $context
     *
     * @return object|null
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerEntityFinderClassException
     *
     * @phpstan-param class-string $class
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) Unused context
     */
    public function findOneBy(string $class, array $criteria, ?array $context = null): ?object
    {
        if (\class_exists($class) === false) {
            throw new DoctrineDenormalizerEntityFinderClassException(
                \sprintf('The class "%s" could not be found.', $class)
            );
        }

        $repository = $this->entityManager->getRepository($class);

        return $repository->findOneBy($criteria);
    }
}
