<?php declare(strict_types=1);

namespace Calcine\Strapi;

class BasicHTTPClient implements BasicHTTPClientInterface
{
    public function __construct(private ?array $headers = null)
    {
    }

    public function get(string $url): string
    {
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
        ];
        if (($headers = $this->headers)) {
            $options[CURLOPT_HTTPHEADER] = $headers;
        }

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $data = curl_exec($ch);

        if (($errno = curl_errno($ch))) {
            $error = curl_error($ch);
            throw new \Exception("$errno: $error");
        }

        return $data;
    }
}
