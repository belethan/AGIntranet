<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class UserSynchronizer
{
    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer
    ) {}

    public function sync(string $username, array $wsData): User
    {
        $repo = $this->em->getRepository(User::class);
        $user = $repo->findOneBy(['compteinfo' => $username]) ?? new User($username);

        // Hash
        $externalHash = hash('sha256', json_encode($wsData));

        if ($user->getExternalHash() !== $externalHash) {

            // HYDRATATION PRO
            $this->serializer->denormalize(
                $wsData,
                User::class,
                'array',
                ['object_to_populate' => $user]
            );

            $user->setExternalHash($externalHash);

            $this->em->persist($user);
        }

        $this->em->flush();

        return $user;
    }
}


