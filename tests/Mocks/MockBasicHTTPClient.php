<?php declare(strict_types=1);

namespace Calcine\Tests\Mocks;

use Calcine\Strapi\BasicHTTPClientInterface;

class MockBasicHTTPClient implements BasicHTTPClientInterface
{
    public $onGet = null;

    public function get(string $url): string
    {
        if (!($onGet = $this->onGet)) {
            throw new \Exception('onGet is not defined in MockBasicHTTPClient');
        }

        return call_user_func($onGet, $url);
    }
}
