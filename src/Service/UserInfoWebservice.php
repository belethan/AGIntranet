<?php

namespace App\Service;

use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class UserInfoWebservice
{
    public function __construct(
        private HttpClientInterface $webClient,
        private string $wsHost,
        private string $wsBasePath,
        private string $wsBasic,   // "login:password"
        private bool $ignoreSsl
    ) {}

    /**
     * @throws TransportExceptionInterface
     * @throws DecodingExceptionInterface
     */
    public function fetchUserData(string $username): array
    {
        $url = rtrim($this->wsHost, '/') . '/' . trim($this->wsBasePath, '/') . '/LOGIN/' . rawurlencode($username);

        $options = [
            'headers' => ['Accept' => 'application/json'],
        ];

        if ($this->wsBasic !== '') {
            [$login, $password] = array_pad(explode(':', $this->wsBasic, 2), 2, '');
            $options['auth_basic'] = [$login, $password];
        }

        if ($this->ignoreSsl) {
            $options['verify_peer'] = false;
            $options['verify_host'] = false;
        }

        $response = $this->webClient->request('GET', $url, $options);

        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException('WebService AGDUC erreur HTTP ' . $response->getStatusCode());
        }

        return $response->toArray(false);
    }
}
