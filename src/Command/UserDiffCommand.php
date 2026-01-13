<?php

namespace App\Command;

use App\Entity\User;
use App\Service\UserInfoWebservice;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:user:diff',
    description: 'Compare les données WS avec la base locale pour un utilisateur'
)]
final class UserDiffCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserInfoWebservice $userWs
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'username',
            InputArgument::REQUIRED,
            'Username SSO (ex: lfournier)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getArgument('username');

        $wsData = $this->userWs->fetchUserData($username);

        $codagt = (string) ($wsData['codagt'] ?? '');
        $user = $this->em->getRepository(User::class)->findOneBy(['codagt' => $codagt]);

        if (!$user) {
            $output->writeln("<error>Utilisateur non trouvé en base (codagt={$codagt})</error>");
            return Command::FAILURE;
        }

        $output->writeln("<info>Diff WS vs DB pour {$username}</info>");

        foreach ($wsData as $wsKey => $wsValue) {
            $getter = 'get' . ucfirst($this->camelize($wsKey));

            if (!method_exists($user, $getter)) {
                $output->writeln("  [IGNORED] {$wsKey} (pas de getter)");
                continue;
            }

            $dbValue = $user->$getter();

            $normalizedDb = $dbValue instanceof \DateTimeInterface
                ? $dbValue->format('Y-m-d H:i:s')
                : (string) $dbValue;

            $normalizedWs = is_scalar($wsValue) ? (string) $wsValue : json_encode($wsValue);

            if ($normalizedDb !== $normalizedWs) {
                $output->writeln(sprintf(
                    "  <comment>DIFF</comment> %-20s WS='%s' | DB='%s'",
                    $wsKey,
                    $normalizedWs,
                    $normalizedDb
                ));
            }
        }

        return Command::SUCCESS;
    }

    private function camelize(string $value): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $value)));
    }
}
