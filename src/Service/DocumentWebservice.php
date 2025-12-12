<?php

namespace App\Service;

use Exception;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class DocumentWebservice
{
    public function __construct(
        private HttpClientInterface $webClient,
        private string              $wsBasePath
    ) {}

    /**
     * Retourne tous les documents d'un agent.
     *
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function fetchDocuments(string $codagt): array
    {
        $url = sprintf("%s/DOC/%s", $this->wsBasePath, $codagt);

        $response = $this->webClient->request('GET', $url, [
            'verify_peer' => false,
            'verify_host' => false,
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new Exception("Erreur WebService DOC: HTTP " . $response->getStatusCode());
        }

        return $response->toArray();
    }

    /**
     * Retourne un nouvel ID Oracle pour un document.
     *
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function fetchNewDocId(): int
    {
        $url = sprintf("%s/DOCKEY", $this->wsBasePath);

        $response = $this->webClient->request('GET', $url, [
            'verify_peer' => false,
            'verify_host' => false,
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new Exception("Erreur WebService DOCKEY: HTTP " . $response->getStatusCode());
        }

        $data = $response->toArray();

        return (int) $data['id'];
    }
}
