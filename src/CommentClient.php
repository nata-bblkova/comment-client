<?php

namespace App;

use JsonException;
use RuntimeException;

class CommentClient
{
    private string $baseUrl;

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @throws JsonException
     */
    private function sendRequest(string $endpoint, string $method, array $params = []): array
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "$this->baseUrl/$endpoint",
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
        ]);

        if (!empty($params)) {
            if ($method === 'POST') {
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
            }

            if ($method === 'PUT') {
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
            }
        }

        $response = curl_exec($curl);
        if ($response === false) {
            throw new RuntimeException("Something went wrong while connecting to $this->baseUrl.");
        }

        $data = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            throw new RuntimeException('Response is not an array.');
        }

        return $data;
    }

    /**
     * @throws JsonException
     */
    public function getComments(): array
    {
        return $this->sendRequest('comments', 'GET');
    }

    /**
     * @throws JsonException
     */
    public function postComment(string $name, string $text): array
    {
        return $this->sendRequest('comment', 'POST', ['name' => $name, 'text' => $text]);
    }

    /**
     * @throws JsonException
     */
    public function putComment(int $id, string $name, string $text): array
    {
        return $this->sendRequest("comment/$id", 'PUT', ['name' => $name, 'text' => $text]);
    }
}