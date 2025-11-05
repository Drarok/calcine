<?php declare(strict_types=1);

namespace Calcine\Model;

readonly class User
{
    public function __construct(public string $name, public string $email)
    {
    }
}
