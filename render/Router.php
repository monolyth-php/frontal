<?php

namespace monolyth\render;
use ErrorException;
use monolyth\Project_Access;
use monolyth\Language_Access;
use monolyth\Country_Access;

class Router
{
    use Project_Access;
    use Language_Access;
    use Country_Access;

    protected $domain;
    protected $routes = [];
    protected $controllers = [];
    protected $project, $country;
    protected $requiredArguments = [];
    protected $translations = [];
    public $language;

    public function __construct()
    {
        $url = sprintf(
            '%s://%s',
            self::project()['secure'] ?
                self::project()['https'] :
                self::project()['http'],
            isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/'
        );
        $this->setDefaultDomain(self::project()['http']);
        self::project()->setLanguage($url, self::language());
        if ($country = self::country()) {
            self::project()->setCountry($url, $country);
        }
    }

    public function setDefaultDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * Add a route to the pool.
     *
     * Placeholders in routes are mapped to named regular expressions, and are
     * based on PHP's sprintf-format. They take the form of %TYPE:NAME. If so
     * required, you can use the %0.2f syntax to specify a floating point number
     * with two decimals, though it's rarely needed in URLs.
     *
     * Monolyth's router support %s for a word (string), %d for a decimal and
     * $f for a float. Additionally, it support %a for "All", which effectively
     * maps to (.*?) and matches anything and everything - so the receiving
     * controller should take care to validate any input!
     *
     * @param string|array $url The URL to match, with optional placeholders.
     * @param string|array $controller The controller(s) to associate with this
     *                                 URL. The suffix [_]Controller can be
     *                                 omitted.
     * @param string $domain Optional domain to force. This will automatically
     *                       get prepended when generating the route from a
     *                       different domain, or when $context is true.
     * @param array $req Optional array of required key/value pairs. This
     *                   allows us to force a certain route to a specific
     *                   URL. E.g., for blog posts you might want to
     *                   differentiate between /(%s:catslug)/(%s:slug)/ and
     *                   /blog/(%s:catslug)/(%s:slug)/, with the former
     *                   matching only when catslug equals "news".
     * @return void
     * @see monolyth\Advanced_Router::generate
     */
    public function connect($url, $controller, $domain = null, array $req = [])
    {
        if (is_array($url)) {
            foreach ($url as $one) {
                $this->connect($one, $controller, $domain, $req);
            }
            return;
        }
        if (is_array($controller)) {
            foreach ($controller as $one) {
                $this->connect($url, $one, $domain, $req);
            }
            return;
        }
        if (!isset($domain)) {
            $domain = $this->domain;
        }
        $matchdomain = str_replace('.', '\.', $domain);
        $args = [];
        $printf = [];
        $printfurl = trim($url);
        if (!isset($req['language']) && $this->translations) {
            if ($result = call_user_func(function() use(
                $url,
                $controller,
                $domain,
                $req
            ) {
                $found = $once = false;
                foreach (self::language()->available as $lang) {
                    if ($lang->code == $this->translations['from']) {
                        continue;
                    }
                    $newurl = $url;
                    foreach ($this->translations['slugs'] as $match => $value) {
                        if (preg_match("@/$match(/|^)@", $newurl, $matches)) {
                            $newurl = preg_replace(
                                "@/$match(/|^)@",
                                "/{$value[$lang->code]}\\1",
                                $newurl
                            );
                            $found = true;
                        }
                    }
                    if ($found && !$once) {
                        $once = true;
                        $this->connect(
                            $url,
                            $controller,
                            $domain,
                            $req + ['language' => $this->translations['from']]
                        );
                    }
                    if ($found) {
                        $this->connect(
                            $newurl,
                            $controller,
                            $domain,
                            $req + ['language' => $lang->code]
                        );
                    }
                }
                return $found ? $this : null;
            })) {
                return $result;
            }
        }
        $newurl = preg_replace_callback(
            '@\((%[0-9\.]{0,}[asdf]):(\w+)\)@',
            function($matches) use(&$args, &$printf, &$printfurl, $req) {
                $printfurl = str_replace(
                    $matches[0],
                    "%{$matches[2]}",
                    $printfurl
                );
                $printf[$matches[2]] = $matches[1] == '%a' ? '%s' : $matches[1];
                $args[] = $matches[2];
                if (isset($req[$matches[2]])) {
                    return "(?'{$matches[2]}'{$req[$matches[2]]}?)";
                }
                switch (substr($matches[1], -1)) {
                    case 's': return "(?'{$matches[2]}'[^/]+?)";
                    case 'd': return "(?'{$matches[2]}'\d+?)";
                    case 'f': return "(?'{$matches[2]}'\d+\.\d+?)";
                    case 'a': return "(?'{$matches[2]}'.*?)";
                }
            },
            str_replace('.', '\.', trim($url))
        );
        $this->routes[$matchdomain.$newurl] = $controller;
        asort($args);
        $idstr = str_replace(
            '\\',
            DIRECTORY_SEPARATOR,
            strtolower($controller)
        );
        $mod = ':'.implode(',', $args);
        if ($req) {
            $args = array_unique(array_merge($args, array_keys($req)));
            asort($args);
            $mod = ':'.implode(',', $args);
            $this->flagRequiredArguments($idstr.$mod, $req);
            ksort($req);
            $mod .= ':'.json_encode($req);
        }
        if (is_null($controller)) {
            return;
        } elseif (!strlen($controller)) {
            $fullname = 'Controller';
        } elseif ($controller == strtolower($controller)) {
            $fullname = "{$controller}\\Controller";
        } else {
            $fullname = "{$controller}_Controller";
        }
        $this->controllers["$fullname$mod"] =
        $this->controllers["$controller$mod"] =
        $this->controllers["$idstr$mod"] = [$domain.$printfurl, $printf, $req];
    }

    /**
     * Try to map the specified URL to a controller.
     */
    public function match($url)
    {
        if (self::project()['cli']) {
            if (self::project()['secure']) {
                $url = self::project()['https'].$url;
            } else {
                $url = self::project()['http'].$url;
            }
        } else {
            $url = sprintf(
                '%s://%s%s',
                self::project()[self::project()['secure'] ?
                    'protocols' :
                    'protocol'],
                $_SERVER['SERVER_NAME'],
                $url
            );
        }
        foreach ($this->routes as $match => $controller) {
            if (preg_match("@^$match$@", $url, $matches)) {
                if ($controller instanceof Redirect) {
                    $controller->inject($matches);
                }
                foreach ($matches as $key => $value) {
                    if (is_numeric($key)) {
                        unset($matches[$key]);
                    }
                }
                if (!isset($matches['controller'])) {
                    $matches['controller'] = $controller;
                }
                if (!is_null($matches['controller'])
                    && !is_object($matches['controller'])
                    && strpos($matches['controller'], 'Controller') === false
                ) {
                    if (preg_match('@\\\\[A-Z]@', $matches['controller'])
                        or ($matches['controller'] !=
                            strtolower($matches['controller'])
                            && strlen($matches['controller'])
                        )
                    ) {
                        $matches['controller'] .= '_Controller';
                    } else {
                        $matches['controller'] .= '\Controller';
                    }
                }
                return $matches;
            }
        }
        return null;
    }

    /**
     * Generate a URL.
     */
    public function generate($idstr, $args, $context = false)
    {
        if (is_object($idstr)) {
            $idstr = get_class($idstr);
        }
        $idstr = strtolower($idstr);
        $idstr = str_replace('\\', '/', $idstr);
        $idstr = preg_replace("@_?controller$@", '', $idstr);
        $keys = array_unique(array_keys($args));
        asort($keys);
        $mod = ':'.implode(',', $keys);
        $required = [];
        if (isset($this->requiredArguments[$idstr.$mod])) {
            $required = $this->requiredArguments[$idstr.$mod];
            $reqargs = [];
            foreach ($required as $key) {
                try {
                    $reqargs[$key] = $args[$key];
                } catch (ErrorException $e) {
                    // If a required argument isn't set, we can't have a match.
                    return null;
                }
            }
            ksort($reqargs);
            $mod .= ':'.json_encode($reqargs);
        }
        try {
            $match = $this->controllers["$idstr$mod"];
        } catch (ErrorException $e) {
            return null;
        }
        $newargs = [];
        foreach ($args as $key => $value) {
            try {
                $newargs["%$key"] = sprintf($match[1][$key], $value);
            } catch (ErrorException $e) {
            }
        }
        $url = str_replace(
            array_keys($newargs),
            array_values($newargs),
            $match[0]
        );
        $url = preg_replace('@(?<!:)/{2,}@', '/', $url);
        $test = self::project()[self::project()['secure'] ? 'https' : 'http'];
        if (!$context && strpos($url, $test) !== false) {
            $url = preg_replace(
                "@^https?://{$_SERVER['SERVER_NAME']}@",
                '',
                $url
            );
        }
        return $url;
    }

    protected function flagRequiredArguments($idstr, array $required)
    {
        if (!isset($this->requiredArguments[$idstr])) {
            $this->requiredArguments[$idstr] = [];
        }
        $this->requiredArguments[$idstr] = array_unique(array_merge(
            $this->requiredArguments[$idstr],
            array_keys($required)
        ));
    }

    public function setTranslations($from, array $slugs)
    {
        $this->translations = compact('from', 'slugs');
    }
}

