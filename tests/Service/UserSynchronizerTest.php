<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\UserSynchronizer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class UserSynchronizerTest extends TestCase
{
    private EntityManagerInterface $em;
    private EntityRepository $repository;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(EntityRepository::class);

        $this->em->method('getRepository')
            ->with(User::class)
            ->willReturn($this->repository);
    }

    public function testSyncCreatesNewUser(): void
    {
        $username = 'jdupont';

        $wsData = [
            'nomusu' => 'DUPONT',
            'prenom' => 'Jean',
            'mail' => 'jdupont@test.fr',
            'codagt' => 'AG123',
            'compte_info' => $username,
        ];

        $this->repository->method('findOneBy')->willReturn(null);

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        // ✅ VRAI serializer (simple, fiable)
        $serializer = new Serializer([new ObjectNormalizer()]);

        $synchronizer = new UserSynchronizer($this->em, $serializer);

        $user = $synchronizer->sync($username, $wsData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame($username, $user->getUsername());
        $this->assertNotNull($user->getExternalHash());
    }

    public function testSyncDoesNothingIfHashIsIdentical(): void
    {
        $username = 'jdupont';

        $wsData = [
            'nomusu' => 'DUPONT',
            'prenom' => 'Jean',
        ];

        $existingUser = new User($username);
        $existingUser->setExternalHash(
            hash('sha256', json_encode($wsData, JSON_THROW_ON_ERROR))
        );

        $this->repository->method('findOneBy')->willReturn($existingUser);

        $this->em->expects($this->never())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $serializer = new Serializer([new ObjectNormalizer()]);
        $synchronizer = new UserSynchronizer($this->em, $serializer);

        $user = $synchronizer->sync($username, $wsData);

        $this->assertSame($existingUser, $user);
    }

    public function testSyncThrowsExceptionIfNomusuIsMissing(): void
    {
        $this->repository->method('findOneBy')->willReturn(null);

        $wsData = [
            'prenom' => 'Jean',
        ];

        $serializer = new Serializer([new ObjectNormalizer()]);
        $synchronizer = new UserSynchronizer($this->em, $serializer);

        $this->expectException(\RuntimeException::class);

        $synchronizer->sync('jdupont', $wsData);
    }
}
