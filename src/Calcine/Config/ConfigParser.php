<?php declare(strict_types=1);

namespace Calcine\Config;

use Calcine\Services\ContentProvider;
use Calcine\Services\ContentProviderInterface;
use Calcine\Services\ContentAdaptors\ContentAdaptorInterface;
use Calcine\Services\ContentAdaptors\FileContentAdaptor;
use Calcine\Services\ContentAdaptors\StrapiContentAdaptor;
use Calcine\Strapi\BasicHTTPClient;
use Calcine\Strapi\BasicHTTPClientInterface;
use Calcine\Strapi\StrapiClient;
use Calcine\Strapi\StrapiContentType;

class ConfigParser
{
    private ?array $data;

    private ?BasicHTTPClientInterface $http = null;
    private ?StrapiClient $strapi = null;

    public function __construct(string $pathname, ?BasicHTTPClientInterface $http = null)
    {
        if (! file_exists($pathname) || ! is_readable($pathname)) {
            throw new \Exception('Cannot read file \'' . $pathname . '\'');
        }

        $this->http = $http;

        $this->data = json_decode(file_get_contents($pathname), true);
        if ($this->data === null && $error = json_last_error_msg()) {
            throw new \Exception($error);
        }
    }

    public function get(string $path, mixed $default = null): mixed
    {
        $keys = explode('.', $path);
        $val = $this->data;

        while ($keys && is_array($val)) {
            $key = array_shift($keys);
            if (array_key_exists($key, $val)) {
                $val = $val[$key];
            } else {
                return $default;
            }
        }

        return $val;
    }

    public function makeContentProvider(): ContentProviderInterface
    {
        return new ContentProvider(
            $this->get('site.title', ''),
            $this->get('site.description', ''),
            $this->makePagesAdaptor(),
            $this->makePostsAdaptor(),
        );
    }

    private function getHTTPClient(): BasicHTTPClientInterface
    {
        if (($http = $this->http)) {
            return $http;
        }

        return $this->http = new BasicHTTPClient($this->get('content.headers'));
    }

    private function getStrapiClient(): StrapiClient
    {
        if (($strapi = $this->strapi)) {
            return $strapi;
        }

        $http = $this->getHTTPClient();
        return $this->strapi = new StrapiClient($http, $this->get('content.root_url'));
    }

    private function makePagesAdaptor(): ContentAdaptorInterface
    {
        $adaptor = $this->get('content.adaptor');

        switch ($adaptor) {
            case 'file':
                return $this->makeFileContentAdaptor(StrapiContentType::Pages);
            case 'strapi':
                $strapi = $this->getStrapiClient();
                return new StrapiContentAdaptor($strapi, StrapiContentType::Pages);
            default:
                // TODO: Custom providers
                throw new \Exception("Invalid pages adaptor: '$adaptor'");
        }
    }

    private function makePostsAdaptor(): ContentAdaptorInterface
    {
        $adaptor = $this->get('content.adaptor');

        switch ($adaptor) {
            case 'file':
                return $this->makeFileContentAdaptor(StrapiContentType::Posts);
            case 'strapi':
                $strapi = $this->getStrapiClient();
                return new StrapiContentAdaptor($strapi, StrapiContentType::Posts);
            default:
                // TODO: Custom providers
                throw new \Exception("Invalid posts adaptor: '$adaptor'");
        }
    }

    private function makeFileContentAdaptor(StrapiContentType $type): FileContentAdaptor
    {
        $path = sprintf('%s/%s', $this->get('content.path'), $type->value);
        return new FileContentAdaptor($path);
    }
}
