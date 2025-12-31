<?php

namespace App\Service;

use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;
use Symfony\Component\Serializer\SerializerInterface;

class UserSynchronizer
{
    private SerializerInterface $serializer;

    public function __construct(
        private readonly EntityManagerInterface $em,
        SerializerInterface             $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * @throws Exception
     */
    public function sync(string $username, array $wsData): User
    {
        $repo = $this->em->getRepository(User::class);
        $user = $repo->findOneBy(['compteinfo' => $username]) ?? new User($username);

        // Hash
        $externalHash = hash('sha256', data: json_encode($wsData, JSON_THROW_ON_ERROR));


        if ($user->getExternalHash() !== $externalHash) {
            // Mapping WS => Entity
            // Mapping exact AGDUC -> Entity
            $mapping = [
                'nomusu'              => 'nomusu',
                'prenom'              => 'prenom',
                'nompat'              => 'nompat',
                'teleph'              => 'teleph',
                'sexe'                => 'sexe',
                'dtenai'              => 'dtenai',
                'comnai'              => 'comnai',
                'mail'                => 'mail',
                'mailpro'             => 'mailpro',
                'telportpro'          => 'telportpro',
                'telport'             => 'telport',
                'telpro'              => 'telpro',
                'notel'               => 'notel',
                'compte_info'         => 'compteInfo',
                'site'                => 'site',
                'service'             => 'service',
                'nomcj'               => 'nomcj',
                'prenomcj'            => 'prenomcj',
                'codnat'              => 'codnat',
                'contacc'             => 'contacc',
                'telacc'              => 'telacc',
                'telportacc'          => 'telportacc',
                'libcom'              => 'libcom',
                'codpos'              => 'codpos',
                'codpay'              => 'codpay',
                'nomrue'              => 'nomrue',
                'numrue'              => 'numrue',
                'codagt'              => 'codagt',
                'compte_actif'        => 'compte_actif',
                'codagtResponsable'   => 'codagt_responsable',
                'nomResponsable'      => 'nom_responsable',
                'prenomResponsable'   => 'prenom_responsable',
                'mailResponsable'     => 'mail_responsable',
                'num_rpps'            => 'num_rpps'
            ];


            $cleanData = [];

            foreach ($mapping as $wsKey => $entityField) {

                if (!array_key_exists($wsKey, $wsData)) {
                    continue;
                }

                $value = $wsData[$wsKey];

                // Normalisation pour les numéros (beaucoup de WS Oracle renvoient des INT)
                $phoneFields = ['teleph', 'telport', 'telportpro', 'telpro', 'notel', 'telacc', 'telportacc'];

                if (in_array($wsKey, $phoneFields, true)) {
                    $cleanData[$entityField] = $value !== null ? (string)$value : null;
                    continue;
                }

                // sexe -> int
                if ($wsKey === 'sexe') {
                    $cleanData[$entityField] = (int)($value ?? 0);
                    continue;
                }

                // codagt -> string
                if ($wsKey === 'codagt') {
                    $cleanData[$entityField] = (string)$value;
                    continue;
                }

                // dates
                if ($wsKey === 'dtenai' && !empty($value)) {
                    try {
                        $cleanData[$entityField] = (new DateTime($value))->format('Y-m-d');
                    } catch (Exception) {
                        $cleanData[$entityField] = null;
                    }
                    continue;
                }

                // Valeur normale
                $cleanData[$entityField] = $value;
            }

            // Sécuriser le remplissage de champ critique (exemple nomusu)
            if (empty($cleanData['nomusu'])) {
                throw new RuntimeException("ERREUR : 'nomusu' est vide après mapping AGDUC");
            }

            // Username obligatoire pour Symfony + DB
            if (isset($cleanData['compteInfo'])) {
                $user->setUsername($cleanData['compteInfo']);
            } elseif ($user->getUsername() === null) {
                // Cas initial : nouvel utilisateur
                $user->setUsername($username);
            }

            // HYDRATATION PRO
            $this->serializer->denormalize(
                $cleanData,
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


