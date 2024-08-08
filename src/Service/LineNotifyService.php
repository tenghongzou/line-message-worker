<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class LineNotifyService
{
    const LINE_NOTIFY_API_URL = 'https://notify-api.line.me/api/notify';
    private HttpClientInterface $client;
    private string              $token;

    public function __construct(
        HttpClientInterface $client,
        string              $token
    )
    {
        $this->client = $client;
        $this->token = $token;
    }

    public function sendMessage(string $message): void
    {
        $response = $this->client->request('POST', self::LINE_NOTIFY_API_URL, [
            'headers' => [
                'Content-Type: multipart/form-data',
                'Authorization' => 'Bearer ' . $this->token,
            ],
            'body' => [
                'message' => $message,
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Failed to send message: ' . $response->getContent(false));
        }
    }
}
