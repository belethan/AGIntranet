<?php

namespace App\Service;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

final class UserSynchronizer
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $agducLogger
    ) {}

    public function sync(string $username, array $wsData): User
    {
        // =====================================================
        // 1) CODAGT OBLIGATOIRE
        // =====================================================
        $codagt = $wsData['codagt'] ?? null;

        if (!$codagt) {
            $this->agducLogger->error('AGDUC – codagt absent du WS USER', [
                'username' => $username,
                'ws_keys'  => array_keys($wsData),
            ]);
            throw new RuntimeException("ERREUR SSO : 'codagt' absent des données WS");
        }

        $codagt = (string) $codagt;

        // =====================================================
        // 2) RÉCUPÉRATION / CRÉATION USER
        // =====================================================
        $repo = $this->em->getRepository(User::class);
        $user = $repo->findOneBy(['codagt' => $codagt]) ?? new User($username);

        // =====================================================
        // 3) HASH EXTERNE WS (stabilité)
        // =====================================================
        ksort($wsData);
        $externalHash = hash('sha256', json_encode($wsData, JSON_THROW_ON_ERROR));

        if ($user->getExternalHash() === $externalHash) {
            $this->agducLogger->debug('AGDUC – utilisateur inchangé', [
                'username' => $username,
                'codagt'   => $codagt,
            ]);
            return $user;
        }

        // =====================================================
        // 4) MAPPING WS → ENTITY (SANS DATES)
        // =====================================================
        $mapping = [
            // identité
            'nomusu'   => 'nomusu',
            'prenom'   => 'prenom',
            'nompat'   => 'nompat',
            'sexe'     => 'sexe',
            'comnai'   => 'comnai',

            // contacts
            'mail'     => 'mail',
            'mailpro'  => 'mailpro',
            'teleph'   => 'teleph',
            'telpro'   => 'telpro',
            'telport'  => 'telport',
            'telportpro' => 'telportpro',
            'notel'    => 'notel',

            // pro
            'site'     => 'site',
            'service'  => 'service',
            'serviceresp' => 'serviceresp',
            'num_rpps' => 'numRpps',

            // conjoint / contact
            'nomcj'    => 'nomcj',
            'prenomcj'=> 'prenomcj',
            'codnat'  => 'codnat',
            'contacc' => 'contacc',
            'telacc'  => 'telacc',
            'telportacc' => 'telportacc',

            // adresse
            'libcom'  => 'libcom',
            'codpos'  => 'codpos',
            'codpay'  => 'codpay',
            'nomrue'  => 'nomrue',
            'numrue'  => 'numrue',

            // responsable
            'codagtResponsable'   => 'codagtResponsable',
            'nomResponsable'      => 'nomResponsable',
            'prenomResponsable'   => 'prenomResponsable',
            'mailResponsable'     => 'mailResponsable',
            'siteresp'            => 'siteresp',
        ];

        $cleanData = [];

        foreach ($mapping as $wsKey => $entityField) {
            if (!array_key_exists($wsKey, $wsData)) {
                continue;
            }

            $value = $wsData[$wsKey];

            // normalisation valeurs vides
            if ($value === '' || $value === '0') {
                $value = null;
            }

            $cleanData[$entityField] = $value;
        }

        if (empty($cleanData['nomusu'] ?? null)) {
            $this->agducLogger->error('AGDUC – nomusu vide après mapping', [
                'username' => $username,
                'codagt'   => $codagt,
            ]);
            throw new RuntimeException("ERREUR AGDUC : 'nomusu' vide après mapping");
        }

        // =====================================================
        // 5) IDENTITÉS TECHNIQUES
        // =====================================================
        $user
            ->setCodagt($codagt)
            ->setUsername($username)
            ->setCompteInfo($username)
            ->setCompteActif((int) ($wsData['compte_actif'] ?? 1));

        // =====================================================
        // 6) HYDRATATION SAFE (SANS DATES)
        // =====================================================
        $this->serializer->denormalize(
            $cleanData,
            User::class,
            'array',
            ['object_to_populate' => $user]
        );

        // =====================================================
        // 7) GESTION DES DATES (MANUELLE & SAFE)
        // =====================================================
        $this->hydrateDate($user, 'setDtenai', $wsData['dtenai'] ?? null);
        $this->hydrateDate($user, 'setDatenaicj', $wsData['datenaicj'] ?? null);

        // =====================================================
        // 8) FINALISATION
        // =====================================================
        $user->setExternalHash($externalHash);

        $this->em->persist($user);
        $this->em->flush();

        $this->agducLogger->info('AGDUC – utilisateur synchronisé', [
            'username' => $username,
            'codagt'   => $codagt,
            'user_id'  => $user->getId(),
        ]);

        return $user;
    }

    // =====================================================
    // OUTIL DATE SAFE
    // =====================================================
    private function hydrateDate(User $user, string $setter, mixed $value): void
    {
        if (!$value) {
            $user->$setter(null);
            return;
        }

        try {
            $user->$setter(new DateTimeImmutable((string) $value));
        } catch (Throwable) {
            $user->$setter(null);
        }
    }
}
