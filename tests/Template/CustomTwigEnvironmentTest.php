<?php declare(strict_types=1);

namespace Calcine\Tests\Template;

use PHPUnit\Framework\TestCase;
use Twig\Extension\StringLoaderExtension;
use Twig\Loader\FilesystemLoader as TwigFileLoader;

use Calcine\Post;
use Calcine\Post\Tag;
use Calcine\Template\CustomTwigEnvironment;

class CustomTwigEnvironmentTest extends TestCase
{
    private const string POST_TYPE_PARAGRAPH = 'paragraph';
    private const string POST_TYPE_JUMP = 'jump';

    private CustomTwigEnvironment $object;

    public function setUp(): void
    {
        parent::setUp();

        $twigLoader = new TwigFileLoader();
        $this->object = new CustomTwigEnvironment($twigLoader);
    }

    public function testShortenPostBodyWithParagraph(): void
    {
        $body = $this->renderPostBody(self::POST_TYPE_PARAGRAPH);
        $this->assertEquals('<p>This is the first paragraph.</p>', $body);
    }

    public function testShortenPostBodyWithJump(): void
    {
        $body = $this->renderPostBody(self::POST_TYPE_JUMP);
        $expected = "<p>This is before the jump.</p>\n<p>So is this.</p>\n";
        $this->assertEquals($expected, $body);
    }

    private function renderPostBody(string $postType): string
    {
        $template = StringLoaderExtension::templateFromString(
            $this->object,
            '{{ post.body|markdown_to_html|shorten_post_body }}'
        );
        return $this->object->render($template, [
            'post' => $this->makeTestPost($postType),
        ]);
    }

    private function makeTestPost(string $postType): Post
    {
        $tags = [
            new Tag('Tag'),
            new Tag('Test'),
            new Tag('PHP'),
        ];
        return new Post(
            title: 'Test Blog Post',
            tags: $tags,
            slug: 'test-blog-post',
            date: \DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', '2000-01-01 00:00:00'),
            body: $this->getTestPostBody($postType),
        );
    }

    private function getTestPostBody(string $postType): string
    {
        switch ($postType) {
            case self::POST_TYPE_PARAGRAPH:
                return "This is the first paragraph.\n\nThis is the second paragraph.\n";
            case self::POST_TYPE_JUMP:
                return "This is before the jump.\n\nSo is this.\n\n<!-- jump -->\n\nThis is after the jump.\n";
            default:
                throw new \Exception("Invalid post type: $postType");
        }
    }
}
