<?php

namespace Calcine\Template;

use Calcine\User;

class TemplateRenderer
{
    /**
     * User object.
     *
     * @var User
     */
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
