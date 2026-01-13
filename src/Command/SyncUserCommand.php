<?php

namespace App\Command;

use App\Service\UserInfoWebservice;
use App\Service\UserSynchronizer;
use App\Service\DocumentSynchronizer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(
    name: 'app:sync:user',
    description: 'Synchronisation complète AGDUC d’un utilisateur (hors SSO)'
)]
final class SyncUserCommand extends Command
{
    public function __construct(
        private readonly UserInfoWebservice $userInfoWebservice,
        private readonly UserSynchronizer $userSynchronizer,
        private readonly DocumentSynchronizer $documentSynchronizer
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
        $username = strtolower((string) $input->getArgument('username'));

        $output->writeln('');
        $output->writeln('<info>🔄 Synchronisation AGDUC (hors SSO)</info>');
        $output->writeln("Utilisateur : <comment>{$username}</comment>");
        $output->writeln('');

        try {
            // ============================
            // 1) Appel WS USER
            // ============================
            $wsData = $this->userInfoWebservice->fetchUserData($username);

            if (empty($wsData)) {
                throw new \RuntimeException('Données WS vides');
            }

            // ============================
            // 2) Synchronisation USER
            // ============================
            $user = $this->userSynchronizer->sync($username, $wsData);

            $codagt = $user->getCodagt();

            if (!$codagt) {
                throw new \RuntimeException('codagt manquant après synchronisation USER');
            }

            $output->writeln('✔ Utilisateur synchronisé');
            $output->writeln("  id     : {$user->getId()}");
            $output->writeln("  codagt : <comment>{$codagt}</comment>");
            $output->writeln("  nom    : {$user->getNomusu()} {$user->getPrenom()}");
            $output->writeln('');

            // ============================
            // 3) Synchronisation DOCUMENTS
            // ============================
            $result = $this->documentSynchronizer->syncForUser(
                $codagt,
                false // ⚠️ non dry-run
            );

            $output->writeln('📄 Documents synchronisés');
            $output->writeln("  Total    : {$result->getTotal()}");
            $output->writeln("  Créés    : {$result->getCreated()}");
            $output->writeln("  Modifiés : {$result->getUpdated()}");
            $output->writeln("  Ignorés  : {$result->getIgnored()}");

            $output->writeln('');
            $output->writeln('<info>✅ Synchronisation terminée avec succès</info>');

            return Command::SUCCESS;

        } catch (Throwable $e) {
            $output->writeln('');
            $output->writeln('<error>❌ ERREUR DE SYNCHRONISATION</error>');
            $output->writeln("Message : {$e->getMessage()}");

            return Command::FAILURE;
        }
    }
}
