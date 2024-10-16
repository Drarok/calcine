<?php declare(strict_types=1);

namespace Calcine\Tests;

use PHPUnit\Framework\TestCase;
use Calcine\User;

class UserTest extends TestCase
{
    public function testGetters()
    {
        $name = 'Alice Foobar';
        $email = 'alice.foorbar@example.org';

        $user = new User($name, $email);
        $this->assertEquals($name, $user->getName());
        $this->assertEquals($email, $user->getEmail());
    }
}
