<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\UserSynchronizer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class UserSynchronizerTest extends TestCase
{
    private EntityManagerInterface $em;
    private EntityRepository $repository;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(EntityRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->em
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($this->repository);
    }

    public function testSyncCreatesNewUser(): void
    {
        $username = 'jdupont';

        $wsData = [
            'codagt' => 'AG123',
            'nomusu' => 'DUPONT',
            'prenom' => 'Jean',
        ];

        $this->repository
            ->method('findOneBy')
            ->with(['codagt' => 'AG123'])
            ->willReturn(null);

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        // ✅ Vrai serializer qui sait denormalize()
        $serializer = new Serializer([new ObjectNormalizer()]);

        $sync = new UserSynchronizer(
            $this->em,
            $serializer,
            $this->logger
        );

        $user = $sync->sync($username, $wsData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame($username, $user->getUsername());
        $this->assertSame('AG123', $user->getCodagt());
        $this->assertNotNull($user->getExternalHash());
    }

    public function testSyncDoesNothingIfHashIsIdentical(): void
    {
        $username = 'jdupont';

        $wsData = [
            'codagt' => 'AG123',
            'nomusu' => 'DUPONT',
        ];

        // ⚠️ Doit reproduire exactement la logique du service (ksort avant hash)
        $sorted = $wsData;
        ksort($sorted);
        $hash = hash('sha256', json_encode($sorted, JSON_THROW_ON_ERROR));

        $existingUser = new User($username);
        $existingUser->setCodagt('AG123');
        $existingUser->setExternalHash($hash);

        $this->repository
            ->method('findOneBy')
            ->with(['codagt' => 'AG123'])
            ->willReturn($existingUser);

        $this->em->expects($this->never())->method('persist');
        $this->em->expects($this->never())->method('flush');

        $serializer = new Serializer([new ObjectNormalizer()]);

        $sync = new UserSynchronizer(
            $this->em,
            $serializer,
            $this->logger
        );

        $user = $sync->sync($username, $wsData);

        $this->assertSame($existingUser, $user);
    }

    public function testSyncThrowsExceptionIfNomusuIsMissing(): void
    {
        $username = 'jdupont';

        $wsData = [
            'codagt' => 'AG123',
            'prenom' => 'Jean',
        ];

        $this->repository
            ->method('findOneBy')
            ->with(['codagt' => 'AG123'])
            ->willReturn(null);

        // On ne doit pas écrire en base
        $this->em->expects($this->never())->method('persist');
        $this->em->expects($this->never())->method('flush');

        $serializer = new Serializer([new ObjectNormalizer()]);

        $sync = new UserSynchronizer(
            $this->em,
            $serializer,
            $this->logger
        );

        $this->expectException(\RuntimeException::class);

        $sync->sync($username, $wsData);
    }
}
