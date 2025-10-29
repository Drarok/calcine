<?php declare(strict_types=1);

namespace Calcine\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Calcine\FrontMatter\FrontMatterParser;

final class FrontMatterParserTest extends TestCase
{
    #[DataProvider('invalidFilesProvider')]
    public function testThat_GivenInvalidFile_WhenParsed_ThenErrorsAreCorrect(
        string $name,
        string $expectedMessage
    ): void
    {
        $this->expectException(\Calcine\FrontMatter\FrontMatterParserException::class);
        $this->expectExceptionMessage($expectedMessage);
        $path = $this->makePath($name);
        $d = FrontMatterParser::parsePost($path);
        var_dump($d);
    }


    public static function invalidFilesProvider(): array
    {
        return [
            ['invalid/invalid-header.markdown', "Unknown header in invalid-header.markdown: 'invalidheader'."],
            ['invalid/invalid-line.markdown', "Invalid header line in invalid-line.markdown: 'Totally invalid header line.'."],
        ];
    }

    private function makePath(string $name): string
    {
        return realpath(__DIR__ . '/../posts') . '/' . $name;
    }
}
