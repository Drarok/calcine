<?php

namespace Calcine\Tests;

use Calcine\User;

class UserTest extends \PHPUnit_Framework_TestCase
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
