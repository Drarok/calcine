<?php declare(strict_types=1);

namespace Calcine\FrontMatter;

final class FrontMatterParser
{
    public static function parsePage(string $path): array
    {
        return static::parse($path, [
            FrontMatterField::Title,
            FrontMatterField::Slug,
            FrontMatterField::Body,
        ]);
    }

    public static function parsePost(string $path): array
    {
        return static::parse($path, [
            FrontMatterField::Title,
            FrontMatterField::Tags,
            FrontMatterField::Slug,
            FrontMatterField::Date,
            FrontMatterField::Body,
        ]);
    }

    private static function parse(string $path, array $fields): array
    {
        $data = [];
        foreach ($fields as $field) {
            $data[$field->value] = false;
        }

        if (! ($file = fopen($path, 'r'))) {
            throw new FrontMatterParserException("Cannot open $path");
        }

        $basepath = basename($path);
        $bodyField = FrontMatterField::Body->value;

        while (! feof($file)) {
            $line = fgets($file);

            if ($line === false) {
                continue;
            }

            $line = trim($line);

            // Skip blank or comment lines.
            if (! $line || $line[0] == ';') {
                continue;
            }

            // Parse/validate this header line.
            if (! preg_match('/^([a-zA-Z]+): *(.*)$/', $line, $matches)) {
                throw new FrontMatterParserException("Invalid header line in $basepath: '$line'.");
            }

            $name = strtolower($matches[1]);
            $value = trim($matches[2]);

            if ($name === $bodyField) {
                break;
            }

            if (! array_key_exists($name, $data)) {
                throw new FrontMatterParserException("Unknown header in $basepath: '$name'.");
            }

            $data[$name] = $value;
        }

        $body = '';
        while (! feof($file)) {
            $body .= fread($file, 1024);
        }
        $data[$bodyField] = trim($body);

        return $data;
    }
}
