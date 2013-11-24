<?php

namespace monolyth\core;
use monolyth\core\Project;
use monolyth\Language_Model;

interface Router
{
    public function __construct(Project $project, Language_Model $language);
    public function setDefaultDomain($domain);
    public function connect($url, $controller, $domain = null,
        array $required = []);
    public function match($url);
    public function generate($idstr, $args, $context = false);
}

