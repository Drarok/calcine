<?php declare(strict_types=1);

namespace Calcine\Tests\Strapi;

use PHPUnit\Framework\TestCase;

use Calcine\Strapi\BasicHTTPClientInterface;
use Calcine\Strapi\StrapiClient;
use Calcine\Tests\Mocks\MockBasicHTTPClient;

class StrapiClientTest extends TestCase
{
    private ?BasicHTTPClientInterface $http;
    private ?StrapiClient $sut;

    public function setUp(): void
    {
        parent::setUp();

        $http = $this->http = new MockBasicHTTPClient();
        $rootURL = 'http://example.org';

        $this->sut = new StrapiClient(
            $http,
            $rootURL
        );
    }

    public function testThatGivenValidDataWhenFetchPagesCalledThenResultsAreValid()
    {
        $this->http->onGet = function () {
            $page = [
                'title' => 'Example Page',
                'slug' => 'example-page',
                'body' => '## Example Page',
            ];

            return json_encode([
                'meta' => [
                    'pagination' => ['total' => 1],
                ],
                'data' => [$page],
            ]);
        };

        $pages = $this->sut->fetchPages();
        $pages = iterator_to_array($pages);
        $this->assertEquals(1, count($pages));

        $page = $pages[0];
        $this->assertEquals('Example Page', $page['title']);
        $this->assertEquals('example-page', $page['slug']);
        $this->assertEquals('## Example Page', $page['body']);
    }

    public function testThatGivenValidDataWhenFetchPostsCalledThenResultsAreValid()
    {
        $this->http->onGet = function () {
            $post = [
                'title' => 'Example Post',
                'tags' => ['tag1', 'tag2'],
                'slug' => 'example-post',
                'date' => '2025-10-28 15:47:00',
                'body' => '## Example Post',

            ];

            return json_encode([
                'meta' => [
                    'pagination' => ['total' => 1],
                ],
                'data' => [$post],
            ]);
        };

        $posts = $this->sut->fetchPosts();
        $posts = iterator_to_array($posts);
        $this->assertEquals(1, count($posts));

        $post = $posts[0];
        $this->assertEquals('Example Post', $post['title']);
        $this->assertEquals(['tag1', 'tag2'], $post['tags']);
        $this->assertEquals('example-post', $post['slug']);
        $this->assertEquals('2025-10-28 15:47:00', $post['date']);
        $this->assertEquals('## Example Post', $post['body']);
    }
}
