<?php declare(strict_types=1);

namespace Calcine\Tests\PagesProvider;

use PHPUnit\Framework\TestCase;
use Calcine\Model\Page;
use Calcine\PagesProvider\FilePagesProvider;

class FilePagesProviderTest extends TestCase
{
    private ?FilePagesProvider $sut;

    public function setUp(): void
    {
        $this->markTestSkipped('Refactoring');
        parent::setUp();
        $this->sut = new FilePagesProvider(__DIR__ . '/../pages');
    }

    public function tearDown(): void
    {
        $this->sut = null;
        parent::tearDown();
    }

    public function testThat_GivenValidPages_WhenGetPagesCalled_ThenPagesAreReturned(): void
    {
        $pages = $this->sut->getPages();
        $this->assertEquals(1, count($pages));

        foreach ($pages as $page) {
            $expectedSlug = $this->makeSlug($page);
            $this->assertEquals($expectedSlug, $page->slug);
        }
    }

    private function makeSlug(Page $page): string
    {
        return strtolower(str_replace(' ', '-', $page->title));
    }
}
