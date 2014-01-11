<?php

/**
 * The Link class is used to intelligently query the routing table.
 *
 * The idea is that you want your links to be identified by the
 * controller and a method, *NOT* by a hard-coded URL.
 * E.g., to get a link to your home page you might call:
 * <a href="<?php echo Link::get('MyProject::overview') ?>">
 * Whether your homepage should match /, /home/, /en/index/ or something
 * completely different is then irrelevant.
 *
 * Link::get automatically tries to get a link for the current language.
 * It falls back to the default language unless false is the second argument.
 * Use monolyth::get('LanguageConfig')->current->id as the second argument
 * to force a particular language.
 *
 * Aditionally, to make life easier, your overridden Link class
 * can have an $alias array as a member.
 * In keeping with the above example, this might contain:
 * private $alias = [
 *     'home' => 'MyProject::overview',
 * ];
 * You could then simply use Link::get('home') as a shorthand.
 *
 * Routes that take extra parameters can be specified from arguments 3 and up.
 * Use null as second argument to have the language be ignored.
 *
 * IMPORTANT: it's up to you - the programmer - to ensure unique aliases!
 * It's also up to you to make sure the number of arguments fit the route.
 *
 * @package monolyth
 * @subpackage utils
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2008, 2009, 2010, 2011, 2012, 2014
 */

namespace monolyth\utils;
use monolyth;
use monolyth\Project_Access;
use ErrorException;

final class Link
{
    use Project_Access;

    private static $language = null;
    private static $instance = null;
    private static $parsed = [];
    private $current;

    protected $alias = [];
    
    /**
     * Get an instance of the Link object.
     *
     * @param string $uri The URI the link should point to.
     * @return Link A Link instance.
     */
    public function get($uri)
    {
        return new self($uri);
    }

    /**
     * This is identical to self::get, except that it ALWAYS returns an
     * absolute link (i.e., including scheme and domain).
     */
    public function getAbsolute($link)
    {
        $args = func_get_args();
        if (!isset(self::$instance)) {
            self::$instance = new Link();
        }
        array_unshift($args, array_shift($args), null);
        $link = call_user_func_array(
            [self::$instance, 'getByLanguage'],
            $args
        );
        if (!preg_match('@^https?://@', $link)) {
            // Append scheme/domain.
            if (Project::$secure) {
                $link = Project::$https.self::cleanPrefix($link);
            } else {
                $link = Project::$http.self::cleanPrefix($link);
            }
        }
        self::$instance->setCurrent($link);
        return self::$instance;
    }

    /**
     * Gets the URI associated with $link for a specific $language.
     *
     * @param string|array|monolyth\core\Controller $link The controller you
     *                                                    want the URI for.
     * @param int|null $language The language you want the URI for.
     * @param string $arg1,... Optional arguments to pass to the URI.
     * @return string The URI found, or '#' if undefined.
     * @see monolyth_Link::get
     */
    public function getByLanguage($link, $language = null)
    {
        $args = func_get_args();
        
        // Some last-minute setup if this is our first call...
        if (!isset(self::$instance)) {
            self::$instance = new Link();
        }
        if (!isset($this)) {
            return call_user_func_array(
                [self::$instance, 'getByLanguage'],
                $args
            );
        }
        if (!isset(self::$language)) {
            self::$language = Monolyth::get('language');
        }

        if ($link instanceof core\Controller) {
            $link = get_class($link);
        }
        if (isset($this->alias[$link])) {
            $link = $this->alias[$link];
        }
        $found = null;
        
        if (!$language) {
            try {
                $language = self::$language->current->code;
            } catch (ErrorException $e) {
                $language = self::$language->default->code;
            }
        } else {
            $language = $language;
        }
        
        // Check if something matches this controller::method.
        try {
            $route = core\Route::$inverse[$link];
        } catch (ErrorException $e) {
            throw new RouteNotFound_Exception($link);
        }
        try {
            $route = $route[$language];
        } catch (ErrorException $e) {
            try {
                $route = $route[null];
            } catch (ErrorException $e) {
                throw new RouteNotFound_Exception($link);
            }
        }
        $route = preg_replace('/\$[a-z]+/', $language, $route);
        
        // Remove obsolete args before continuing.
        array_shift($args);
        array_shift($args);
        while (substr_count($route, '%s') < count($args)) {
            $route .= "/%s";
        }
        while (!isset($url)) {
            try {
                $url = vsprintf($route, $args);
            } catch (ErrorException $e) {
                $args[] = null;
            }
        }
        while (strpos($url, '//') !== false) {
            $url = str_replace('//', '/', $url);
        }
        if (strpos(array_pop(explode('/', $url)), '.') === false
            && substr($url, -1) != '/'
        ) {
            $url .= '/';
        }
        self::$instance->setCurrent($url);
        return self::$instance;
    }

    /**
     * This is a slightly hacky way to remove the leading path from an
     * imported URI. There should be a better way...
     *
     * @param string $uri The URI to clean.
     * @return string The cleaned uri, e.g. /projectsecure/foo becomes /foo.
     */
    public static function cleanPrefix($uri)
    {
        foreach (['http', 'https'] as $part) {
            try {
                $parts = preg_split(
                    '@^(?=https?://.*?)/(.*?)@',
                    Project::$$part,
                    null,
                    PREG_SPLIT_DELIM_CAPTURE
                );
                $uri = str_replace("/{$parts[1]}", '', $uri);
            } catch (ErrorException $e) {
            }
        }
        return $uri;
    }

    /**
     * This is a slightly hacky way to add the leading path to an
     * imported URI.
     *
     * @param string $uri The URI to fix
     * @return string The fix uri, e.g. /foo could become /projectsecure/foo.
     */
    public function fixPrefix($uri, $secure)
    {
        try {
            $work = self::project()[$secure ? 'https' : 'http'];
        } catch (ErrorException $e) {
            // Not set, so ignore it.
            return $uri;
        }
        try {
            $parts = preg_split(
                '@^(https?://.*?)/(.*?)@',
                $work,
                null,
                PREG_SPLIT_DELIM_CAPTURE
            );
            $uri = "/{$parts[3]}$uri";
        } catch (ErrorException $e) {
        }
        return $uri;
    }

    /**
     * Get an URL by using a controller's menu->selection member.
     * This correlates with the URL for the current page.
     *
     * @param array $selection A monolyth\core\Controller::menu->selection array
     * @param integer $keep How many elements of the current URL to keep. Use a
     *                      negative numer to strip elements off the end.
     * @param mixed $arg,... Optional extra arguments to pass.
     * @return string The new URL.
     */
    public function getBySelection(array $selection, $keep = 99)
    {
        $args = func_get_args();
        $selection = array_shift($args);
        $keep = array_shift($args);
        $controller = $selection[0];
        if (strpos($controller, 'Controller') && $controller{0} == '\\') {
            $controller = substr($controller, 1);
        }
        $params = array_merge(
            [$controller],
            array_slice($selection[1], 0, $keep),
            $args
        );
        return call_user_func_array(
            [__CLASS__, 'get'],
            $params
        );
    }

    /**
     * Adds query_string arguments to a URI.
     *
     * Adds the specified arguments to the query_string, taking care to either
     * extend or create it.
     *
     * @param array $params Query string arguments in key/value pairs.
     * @return Link $self, for easy chaining.
     */
    public function addParams(array $params)
    {
        $start = strpos($this->current, '?') !== false ? '&amp;' : '?';
        foreach ($params as $key => &$param) {
            $param = $key.'='.urlencode($param);
        }
        $this->current .= $start.implode('&amp;', $params);
        return $this;
    }

    public function redir($url)
    {
        return $this->addParams(['redir' => $url]);
    }

    public function __toString()
    {
        return $this->current;
    }
}

