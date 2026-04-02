<?php
namespace App\Repository;

use App\Entity\User;
use App\Entity\WebauthnCredential;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class WebauthnCredentialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WebauthnCredential::class);
    }

    public function saveCredentialData(User $user, array $credentialData): void
    {
        $credential = new WebauthnCredential();
        $credential->setUser($user);
        $credential->setCredentialRawData(json_encode($credentialData));
        $credential->setName('Passkey ' . date('d/m/Y H:i'));
        $this->getEntityManager()->persist($credential);
        $this->getEntityManager()->flush();
    }

    public function findByCredentialId(string $credentialId): ?WebauthnCredential
    {
        return $this->createQueryBuilder('c')
            ->where('c.credentialData LIKE :id')
            ->setParameter('id', '%' . $credentialId . '%')
            ->getQuery()
            ->getOneOrNullResult();
    }
}