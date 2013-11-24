<?php

namespace monolyth;
use ErrorException;

class Redirect
{
    private $url;

    public function __construct($url)
    {
        try {
            $this->url = sprintf($url, $_SERVER['REQUEST_URI']);
        } catch (ErrorException $e) {
            $this->url = $url;
        }
    }

    public function __toString()
    {
        return $this->url;
    }

    public function inject(array $matches)
    {
        $replace = [];
        foreach ($matches as $match => $value) {
            $replace[":$match"] = $value;
        }
        $this->url = str_replace(
            array_keys($replace),
            $replace,
            $this->url
        );
    }
}

