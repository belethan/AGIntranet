<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UserInfoWebservice
{
    public function __construct(
        private readonly HttpClientInterface $webClient,
        private readonly string              $wsBasePath
    ) {}

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function fetchUserData(string $username): array
    {
        // Construction dynamique de l’URL à partir du basePath
        $url = sprintf("%s/LOGIN/%s", $this->wsBasePath, $username);

        // Appel du WebService avec options SSL si nécessaire
        $response = $this->webClient->request('GET', $url, [
            'verify_peer' => false,   // tu peux mettre true en production
            'verify_host' => false,
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception(
                "WebService erreur HTTP : " . $response->getStatusCode()
            );
        }

        return $response->toArray();
    }

}
