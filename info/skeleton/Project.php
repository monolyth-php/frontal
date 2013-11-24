<?php

class Project extends monolyth\core\Project
{
    public function __construct()
    {
        parent::__construct();
        $this['private'] = realpath(__DIR__);
        $this['site'] = 'examplecom';
        $this['name'] = 'Example.com';
        $this['down'] = false;
        $this['cli'] = php_sapi_name() == 'cli';
        $this['test'] = call_user_func(function($project) {
            // Return true if the project is running in 'test'
            // (i.e., developement or staging, but not production).
            return false;
        }, $this);
        $this['http'] = 'http://www.example.com';
        $this['https'] = 'https://secure.example.com';
        if ($this['test']) {
            if ($this['cli']) {
                // Override http(s) for testing.
            } else {
                // Override http(s) for testing.
            }
        }
        // Set to true if on the secure domain; logic depends on your
        // server-setup. Note that 'secure' doesn't require https; it
        // can be a http-subdomain e.g. during testing.
        $this['secure'] = false;
    }
}

