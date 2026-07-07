<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class LexwareClient
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey  = config('services.lexware.api_key', '');
        $this->baseUrl = rtrim(config('services.lexware.base_url', 'https://api.lexoffice.io/v1'), '/');
    }

    private function http()
    {
        return Http::withToken($this->apiKey)
            ->baseUrl($this->baseUrl)
            ->acceptJson();
    }

    private function send(string $method, string $url, array $data = []): array
    {
        $response = $this->http()->{$method}($url, $data ?: null);

        if ($response->failed()) {
            $body = $response->json();
            $detail = $body['message'] ?? $body['error'] ?? json_encode($body);
            throw new \RuntimeException(
                "Lexware {$response->status()} on {$url}: {$detail}",
                $response->status()
            );
        }

        return $response->json();
    }

    public function createContact(array $data): array
    {
        return $this->send('post', '/contacts', $data);
    }

    public function getContact(string $id): array
    {
        return $this->send('get', "/contacts/{$id}");
    }

    public function createQuotation(array $data): array
    {
        return $this->send('post', '/quotations', $data);
    }

    public function createInvoice(array $data, bool $finalize = true): array
    {
        $url = $finalize ? '/invoices?finalize=true' : '/invoices';

        return $this->send('post', $url, $data);
    }
}
