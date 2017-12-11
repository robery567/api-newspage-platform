<?php
namespace Newspage\Api\Builder;

use Symfony\Component\HttpFoundation\JsonResponse;

class JsonBuilder
{
    private $json = [];

    public function __construct($version)
    {
        $this->json = [
            'api-version' => $version,
        ];
    }

    public function getContent($key = null)
    {
        if (is_null($key) || empty($key)) {
            return $this->json['api-content'];
        }

        return $this->json['api-content'][$key];
    }

    public function setContents(array $array = []): self
    {
        if (!is_array($array)) {
            throw new \InvalidArgumentException("You must provide an array, '" . gettype($array) . "' given.");
        }

        foreach ($array as $key => $value) {
            $this->setContent($key, $value);
        }

        return $this;
    }

    public function setContent($key, $value): self
    {
        $this->json['api-content'][$key] = $value;

        return $this;
    }

    public function setError(string $message, int $response = 500): self
    {
        $this->setResponse($response);
        $this->setContent('error-code', $response);
        $this->setContent('error-message', $message);

        return $this;
    }

    public function setResponse(int $response = 200): self
    {
        $this->json['api-response'] = $response;

        return $this;
    }

    public function respond(): JsonResponse
    {
        return new JsonResponse($this->getJson(), $this->getResponse());
    }

    public function getJson(): array
    {
        return $this->json;
    }

    public function getResponse(): int
    {
        return $this->json['api-response'];
    }
}