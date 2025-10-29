<?php declare(strict_types=1);

namespace Calcine\Post;

use Calcine\Post;
use Calcine\FrontMatter\FrontMatterField;
use Calcine\FrontMatter\FrontMatterParser;
use Calcine\FrontMatter\FrontMatterParserException;

final class FilePostParser extends AbstractPostParser implements PostParserInterface
{
    function parse(string $path): Post
    {
        $data = FrontMatterParser::parsePost($path);

        $errors = [];

        try {
            foreach ($data as $name => $value) {
                $data[$name] = $this->processHeader($name, $value);
            }
        } catch (PostParserException $e) {
            $errors[] = $e->getMessage();
        }

        if ($errors) {
            $errors = implode(', ', $errors);
            throw new PostParserException("Failed to parse $path: $errors");
        }

        return new Post(...$data);
    }
}
