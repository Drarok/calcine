<?php declare(strict_types=1);

namespace Calcine\PagesProvider;

use Calcine\FrontMatter\FrontMatterField;
use Calcine\FrontMatter\FrontMatterParser;
use Calcine\FrontMatter\FrontMatterParserException;
use Calcine\Model\Page;

final class FilePageParser extends AbstractPageParser
{
    function parse(string $path): Page
    {
        $data = FrontMatterParser::parsePage($path);

        $errors = [];

        try {
            foreach ($data as $name => $value) {
                $data[$name] = $this->processHeader($name, $value);
            }
        } catch (ParseException $e) {
            $errors[] = $e->getMessage();
        }

        if ($errors) {
            $errors = implode(', ', $errors);
            throw new ParseException("Failed to parse $path: $errors");
        }

        return new Page(...$data);

    }
}
