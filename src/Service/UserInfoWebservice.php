<?php

namespace App\Service;

use Exception;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class UserInfoWebservice
{
    public function __construct(
        private HttpClientInterface $webClient,
        private string              $wsHost,
        private string              $wsBasePath,
        private string              $wsBasic,
        private bool                $ignoreSsl
    ) {}

    /**
     * Appel du Webservice AGDUC pour récupérer les infos utilisateur
     * @throws Exception
     * @throws TransportExceptionInterface
     * @throws DecodingExceptionInterface
     */
    public function fetchUserData(string $username): array
    {
        $url = rtrim($this->wsHost, '/') . '/' . trim($this->wsBasePath, '/') . "/LOGIN/" . $username;

        // Si ton env contient "login:password"
        $auth = base64_encode($this->wsBasic);

        $options = [
            'headers' => [
                'Authorization' => 'Basic ' . $auth,
                'Accept'        => 'application/json'
            ]
        ];

        if ($this->ignoreSsl) {
            $options['verify_peer'] = false;
            $options['verify_host'] = false;
        }

        $response = $this->webClient->request('GET', $url, $options);

        $status = $response->getStatusCode();

        if ($status !== 200) {
            throw new RuntimeException("WebService AGDUC erreur HTTP " . $status);
        }

        return $response->toArray(false);
    }
}
