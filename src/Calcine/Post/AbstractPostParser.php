<?php declare(strict_types=1);

namespace Calcine\Post;

use Calcine\Post;

abstract class AbstractPostParser
{
    private static $dateFormats = [
        'Y-m-d H:i:s',
        'Y-m-d\\TH:i:s',
        \DateTimeInterface::ATOM, // "Y-m-d\\TH:i:sP"
        \DateTimeInterface::RFC3339, // "Y-m-d\\TH:i:sP
        \DateTimeInterface::RFC3339_EXTENDED, // "Y-m-d\\TH:i:s.vP"
    ];

    /**
     * Validate a header, returning its native type.
     *
     * @throws PostParserException
     */
    protected function processHeader(string $name, string|false $value): mixed
    {
        $name = strtolower($name);

        if (! $value) {
            if ($name === 'body') {
                throw new PostParserException("Body is missing or empty");
            } else {
                throw new PostParserException("Header '$name' is missing or empty");
            }
        }

        switch ($name) {
            case 'title':
            case 'body':
                return $value;

            case 'tags':
                $tags = array_map('trim', explode(',', $value));
                natsort($tags);
                return array_map(
                    fn ($name) => new Tag($name),
                    $tags
                );

            case 'slug':
                if (! preg_match('/^[a-z0-9-]+$/', $value)) {
                    throw new PostParserException("Header 'slug' is invalid: '$value'");
                }
                return $value;

            case 'date':
                $date = $this->createDate($value);
                if ($date === false) {
                    throw new PostParserException("Header 'date' is invalid: '$value'");
                }
                return $date;

            default:
                throw new PostParserException("Header '$name' is unknown");
        }
    }

    private function createDate(string $value): \DateTimeInterface|false
    {
        foreach (static::$dateFormats as $format) {
            $date = \DateTimeImmutable::createFromFormat('!' . $format, $value);
            if ($date) {
                return $date;
            }
        }

        return false;
    }
}
