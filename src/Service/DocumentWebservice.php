<?php

namespace App\Service;

use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class DocumentWebservice implements DocumentWebserviceInterface
{
    public function __construct(
        private HttpClientInterface $webClient,
        private string $wsHost,
        private string $wsBasePath,
        private string $wsBasic,
        private bool $ignoreSsl
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchDocuments(string $codagt): array
    {
        $url = rtrim($this->wsHost, '/')
            . '/'
            . trim($this->wsBasePath, '/')
            . '/DOC/'
            . rawurlencode($codagt);

        $response = $this->webClient->request('GET', $url, $this->buildOptions());

        if ($response->getStatusCode() === 404) {
            return [];
        }

        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException(
                sprintf('Erreur WebService AGDUC DOC (%s)', $url)
            );
        }

        return $response->toArray(false);
    }

    /**
     * @return string Contenu binaire du fichier
     */
    public function fetchDocumentFile(int $iddoc): string
    {
        $url = rtrim($this->wsHost, '/')
            . '/'
            . trim($this->wsBasePath, '/')
            . '/DOC/FICHIER/'
            . $iddoc;

        try {
            $response = $this->webClient->request('GET', $url, $this->buildOptions());

            if ($response->getStatusCode() !== 200) {
                throw new RuntimeException(
                    sprintf('Impossible de récupérer le fichier du document %d.', $iddoc)
                );
            }

            return $response->getContent();

        } catch (ClientExceptionInterface $e) {
            throw new RuntimeException(
                sprintf('Accès refusé ou document inexistant (ID %d).', $iddoc),
                0,
                $e
            );
        } catch (RedirectionExceptionInterface $e) {
            throw new RuntimeException(
                sprintf('Redirection inattendue lors de l’accès au document %d.', $iddoc),
                0,
                $e
            );
        } catch (ServerExceptionInterface $e) {
            throw new RuntimeException(
                sprintf('Erreur interne AGDUC lors de la récupération du document %d.', $iddoc),
                0,
                $e
            );
        } catch (TransportExceptionInterface $e) {
            throw new RuntimeException(
                sprintf('Impossible de contacter le WebService AGDUC pour le document %d.', $iddoc),
                0,
                $e
            );
        }
    }

    public function fetchNewDocId(): int
    {
        $url = rtrim($this->wsHost, '/')
            . '/'
            . trim($this->wsBasePath, '/')
            . '/DOCKEY';

        $response = $this->webClient->request('GET', $url, $this->buildOptions());

        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException('Erreur WebService AGDUC DOCKEY');
        }

        $data = $response->toArray(false);

        if (!isset($data['id'])) {
            throw new RuntimeException('Réponse DOCKEY invalide');
        }

        return (int) $data['id'];
    }

    private function buildOptions(): array
    {
        $options = [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ];

        if ($this->wsBasic !== '') {
            [$login, $password] = array_pad(explode(':', $this->wsBasic, 2), 2, '');
            $options['auth_basic'] = [$login, $password];
        }

        if ($this->ignoreSsl) {
            $options['verify_peer'] = false;
            $options['verify_host'] = false;
        }

        return $options;
    }
}
