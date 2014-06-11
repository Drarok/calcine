<?php

namespace Calcine;

class User
{
    /**
     * User name.
     *
     * @var string
     */
    protected $name;

    /**
     * User email address.
     *
     * @var string
     */
    protected $email;

    /**
     * Constructor.
     *
     * @param string $name  User name.
     * @param string $email User email address.
     */
    public function __construct($name, $email)
    {
        $this->name = $name;
        $this->email = $email;
    }

    /**
     * Get user name.
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get user email address.
     *
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }
}
