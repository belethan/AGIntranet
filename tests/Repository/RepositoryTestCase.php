<?php

namespace App\Tests\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class RepositoryTestCase extends KernelTestCase
{
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->em = self::getContainer()
            ->get('doctrine')
            ->getManager();

        // Sécurité absolue
        $this->assertStringContainsString(
            '_test',
            $_ENV['DATABASE_URL'] ?? '',
            'Base NON test détectée'
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->em->close();
        unset($this->em);
    }
}
