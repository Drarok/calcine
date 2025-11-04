<?php declare(strict_types=1);

namespace Calcine\Tests\Config;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Calcine\Config\ConfigParser;
use Calcine\Services\ContentProvider;
use Calcine\Tests\Mocks\MockBasicHTTPClient;

class ConfigParserTest extends TestCase
{
    private MockBasicHTTPClient $http;
    private ConfigParser $object;

    public function setUp(): void
    {
        parent::setUp();
        $this->http = $http = new MockBasicHTTPClient();
        $this->object = new ConfigParser(__DIR__ . '/data/parser.json', $http);
    }

    public function testNoSuchFile()
    {
        $this->expectException(\Exception::class, 'Cannot read file \'/tmp/calcine-no-such-file\'');
        new ConfigParser('/tmp/calcine-no-such-file');
    }

    public function testInvalidFile()
    {
        $this->expectException(\Exception::class, 'Syntax error');
        new ConfigParser(__DIR__ . '/data/invalid.json');
    }

    #[DataProvider('getDataProvider')]
    public function testGet($expected, $path)
    {
        $this->assertEquals($expected, $this->object->get($path, 'default'));
    }

    public function testThatGivenStrapiContentProviderWhenGetPagesCalledThenResultIsCorrect(): void
    {
        $expectedBody = 'This is the page body.';

        $this->http->onGet = function ($url) use ($expectedBody) {
            $this->assertStringStartsWith('http://strapi/pages', $url);

            $page = [
                'title' => 'Title',
                'slug' => 'title',
                'body' => $expectedBody,
            ];
            return json_encode([
                "meta" => ["pagination" => ["total" => 1]],
                "data" => [$page],
            ]);
        };

        $sut = new ConfigParser(__DIR__ . '/data/strapi.json', $this->http);
        $provider = $sut->makeContentProvider();

        $pages = $provider->getPages();
        $this->assertEquals(1, count($pages));

        $page = $pages[0];
        $this->assertEquals($expectedBody, $page->body);
    }

    public function testThatGivenStrapiContentProviderWhenGetPostsCalledThenResultIsCorrect(): void
    {
        $expectedDate = '2025-10-29 02:08:00';
        $expectedBody = 'This is the post body.';

        $this->http->onGet = function ($url) use ($expectedDate, $expectedBody) {
            $this->assertStringStartsWith('http://strapi/posts', $url);

            $post = [
                'title' => 'Title',
                'tags' => ['tag1', 'tag2'],
                'slug' => 'title',
                'date' => $expectedDate,
                'body' => $expectedBody,
            ];
            return json_encode([
                "meta" => ["pagination" => ["total" => 1]],
                "data" => [$post],
            ]);
        };

        $sut = new ConfigParser(__DIR__ . '/data/strapi.json', $this->http);
        $provider = $sut->makeContentProvider();

        $posts = $provider->getPosts();
        $this->assertEquals(1, count($posts));

        $post = $posts[0];
        $this->assertEquals($expectedDate, $post->date->format('Y-m-d H:i:s'));
        $this->assertEquals($expectedBody, $post->body);
    }

    public function testThatGivenFileContentProviderWhenGetPagesCalledThenResultIsCorrect(): void
    {
        $expectedBody = '## Example Page';

        $sut = new ConfigParser(__DIR__ . '/data/file.json', $this->http);
        $provider = $sut->makeContentProvider();

        $pages = $provider->getPages();
        $this->assertEquals(1, count($pages));

        $page = $pages[0];

        $this->assertEquals($expectedBody, $page->body);
    }

    public function testThatGivenFileContentProviderWhenGetPostsCalledThenResultIsCorrect(): void
    {
        $expectedBody = '## Example Post';

        $sut = new ConfigParser(__DIR__ . '/data/file.json', $this->http);
        $provider = $sut->makeContentProvider();

        $posts = $provider->getPosts();
        $this->assertEquals(1, count($posts));

        $post = $posts[0];
        $this->assertEquals($expectedBody, $post->body);
    }

    public static function getDataProvider()
    {
        $testData = json_decode(file_get_contents(__DIR__ . '/data/parser.json'), true);

        return [
            [$testData['s01']['s01']['v01'], 's01.s01.v01'],
            [$testData['s01']['s01']['v02'], 's01.s01.v02'],
            [$testData['s01']['s01']['v03'], 's01.s01.v03'],

            [$testData['s01']['s02']['v01'], 's01.s02.v01'],
            [$testData['s01']['s02']['v02'], 's01.s02.v02'],
            [$testData['s01']['s02']['v03'], 's01.s02.v03'],

            [$testData['s02']['s01']['v01'], 's02.s01.v01'],
            [$testData['s02']['s01']['v02'], 's02.s01.v02'],
            [$testData['s02']['s01']['v03'], 's02.s01.v03'],

            [$testData['s02']['s02']['v01'], 's02.s02.v01'],
            [$testData['s02']['s02']['v02'], 's02.s02.v02'],
            [$testData['s02']['s02']['v03'], 's02.s02.v03'],

            [$testData['s01']['s01'], 's01.s01'],
            [$testData['s01']['s02'], 's01.s02'],

            [$testData['s02']['s01'], 's02.s01'],
            [$testData['s02']['s02'], 's02.s02'],

            [$testData['s01'], 's01'],
            [$testData['s02'], 's02'],

            ['default', 's01.s01.v04'],
            ['default', 's01.s03'],
            ['default', 's03'],
        ];
    }
}
