<?php

namespace Calcine\Config;

class Parser
{
    protected $data;

    /**
     * Constructor.
     *
     * @param string $pathname Full path to a JSON file.
     */
    public function __construct($pathname)
    {
        if (! file_exists($pathname) || ! is_readable($pathname)) {
            throw new \Exception('Cannot read file \'' . $pathname . '\'');
        }

        $this->data = json_decode(file_get_contents($pathname), true);
    }

    /**
     * Get a config section or value using a "path" string.
     *
     * @param string $path    Path string e.g. "section1.section2.value".
     * @param mixed  $default Value to return if the path is invalid.
     *
     * @return mixed
     */
    public function get($path, $default = null)
    {
        $keys = explode('.', $path);
        $val = $this->data;

        while ($keys && is_array($val)) {
            $key = array_shift($keys);
            if (array_key_exists($key, $val)) {
                $val = $val[$key];
            } else {
                return $default;
            }
        }

        return $val;
    }
}
