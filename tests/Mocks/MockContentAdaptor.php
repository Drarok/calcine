<?php declare(strict_types=1);

namespace Calcine\Tests\Mocks;

use Calcine\Services\ContentAdaptors\ContentAdaptorInterface;

final class MockContentAdaptor implements ContentAdaptorInterface
{
    public $onLoadContent = null;

    public function loadContent(): array
    {
        if (!($onLoadContent = $this->onLoadContent)) {
            throw new \Exception('onLoadContent is missing or empty');
        }

        return call_user_func($onLoadContent);
    }
}
