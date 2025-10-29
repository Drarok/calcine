<?php declare(strict_types=1);

namespace Calcine\FrontMatter;

enum FrontMatterField: string
{
    case Title = 'title';
    case Tags = 'tags';
    case Slug = 'slug';
    case Date = 'date';
    case Body = 'body';

    public static function fromValue(string $value): ?FrontMatterField
    {
        foreach (static::cases() as $field) {
            if ($field->value === $value) {
                return $field;
            }
        }

        return null;
    }
}
