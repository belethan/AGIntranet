<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Tests\Factory\UserFactory;
use App\Repository\UserRepository;

class UserRepositoryTest extends RepositoryTestCase
{
    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->em->getRepository(User::class);
    }

    public function testRepositoryIsInstantiable(): void
    {
        $this->assertInstanceOf(UserRepository::class, $this->repository);
    }

    public function testPersistAndFindUser(): void
    {
        $user = UserFactory::createValid();

        $this->em->persist($user);
        $this->em->flush();
        $this->em->clear();

        $found = $this->repository->findOneBy(['username' => 'test.user']);

        $this->assertNotNull($found);
        $this->assertSame('test.user', $found->getUsername());
        $this->assertSame('TU', $found->getInitiales());
    }

    public function testFindAllReturnsArray(): void
    {
        $users = $this->repository->findAll();

        $this->assertIsArray($users);
    }
}
