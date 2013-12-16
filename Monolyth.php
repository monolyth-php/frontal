<?php

/**
 * This is the base for the rest of MonoLyth.
 * It defines the Monolyth-class, the autoloader,
 * as well as doing a few other required includes.
 *
 * @package monolyth
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2008, 2009, 2010, 2011, 2012
 */
namespace monolyth;
use monolyth\DependencyContainer;
use ErrorException;
use monolyth\core\Project;

/** Turn on all errors so we can catch exceptions. */
error_reporting(E_ALL & ~E_STRICT);
/** Define the generic error handler. */
set_error_handler(
    function($errno, $errstr, $errfile, $errline, $errcontext) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    },
    error_reporting()
);
/** Required to make PHP handle UTF8 in a sane way. */
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}
if (function_exists('mb_detect_order')) {
    mb_detect_order([
        'CP1251', 'CP1252', 'ISO-8859-1', 'UTF-8',
    ]);
}

/**
 * Correct REMOTE_ADDR if we're behind a proxy.
 * This code is by no means extensive; there's prolly 1.000 other
 * cases you'll want to handle.
 *
 * TODO: make this work for other servers besides Apache.
 */
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $_SERVER['REMOTE_ADDR'] = trim(array_shift(explode(
        ',',
        $_SERVER['HTTP_X_FORWARDED_FOR']
    )));
}

/**
 * Register monolyth autoloader. Note that it throws an Exception if the class
 * could not be loaded; hence, you'll probably want to define additional
 * autoloaders first, or with the prepend-argument set to true.
 */
spl_autoload_register(['monolyth\Monolyth', '__autoload']);

/**
 * The Monolyth base-class.
 *
 * The Monolyth base-class is defined as abstract and is just that.
 * So, it's not a 'base-class' in the sense that everything should extend it,
 * but rather a bootstrap.
 *
 * @package MonoLyth
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright Copyright MonoMelodies 2008, 2009, 2010, 2011, 2012
 */
abstract class Monolyth
{
    private static $renderTimes = [];

    /**
     * Default monolyth autoloader.
     *
     * Autoloading in monolyth works as follows:
     * - Take the namespace and replace \ with DIRECTORY_SEPARATOR.
     * - Take the classname and replace _ with DIRECTORY_SEPARATOR.
     * - Reverse the classname. Filenames have the most specific part last,
     *   while classnames should have them first. Consider a controller that
     *   works on creating something Foo: you would want to name this
     *   Create_Foo_Controller, but have it map to the file
     *   Controller/Foo/Create, etc.
     *
     * @param string $class The class name.
     * @return boolean True on success, false on failure. Though failure is
     *                 likely to trigger a fatal Class-not-found error anyway.
     */
    public static function __autoload($class)
    {
        $orig = $class;
        $file = '';
        $namespace = '';
        if ($lastpos = strrpos($class, '\\')) {
            $namespace = substr($class, 0, $lastpos);
            $class = substr($class, $lastpos + 1);
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $namespace)
                   .DIRECTORY_SEPARATOR;
        }
        $parts = explode('_', $class);
        $parts = array_reverse($parts);
        $file .= implode(DIRECTORY_SEPARATOR, $parts).'.php';
        try {
            include $file;
            if (!(class_exists($orig, false)
                || interface_exists($orig, false)
            )) {
                return false;
            }
            return true;
        } catch (ErrorException $e) {
            if (strpos(
                $e->getMessage(),
                "include($file): failed to open stream"
            ) === false) {
                throw $e;
            }
            return false;
        }
    }

    public static function setBookmark($id)
    {
        static $start;
        if (!self::$renderTimes) {
            $start = microtime(true);
        }
        self::$renderTimes[] = [
            $id,
            microtime(true) - $start,
            memory_get_usage(true),
        ];
    }
    
    public static function getBookmarks()
    {
        return self::$renderTimes;
    }

    /**
     * When done setting up, run MonoLyth.
     *
     * The main function. Called after setting up a few things.
     * This dispatches control of your site to a controller.
     * For CLI-scripts, this usually isn't called.
     */
    public static function run(Project $project, $theme = 'default')
    {
        try {
            $adapters = Config::get('adapters');
            $cache = null;
            foreach ($adapters as $adapter) {
                if ($adapter instanceof adapter\nosql\Cache) {
                    $cache = $adapter;
                    break;
                }
            }
            $language = new Language_Model($adapters->_current, $cache);
            $country = new Country_Model(
                $adapters->_current,
                $cache,
                $language
            );
            $router = call_user_func(
                require_once 'config/routing.php',
                new Advanced_Router($project, $language)
            );
            $uri = isset($_GET['path']) ?
                urldecode($_GET['path']) :
                $_SERVER['REQUEST_URI'];
            if (($strpos = strpos($uri, '?')) !== false) {
                $uri = substr($uri, 0, $strpos);
            }
            if ($match = $router->match($uri)) {
                if (!isset($match['controller'])) {
                    die();
                }
            }
            if (isset($match['language'])) {
                try {
                    $language->set($match['language']);
                } catch (LanguageNotFound_Exception $e) {
                }
            }
            $container = new DependencyContainer;
            $container->register('monolyth\Country_Access', compact('country'));
            $container->register(
                ['monolyth\Router_Access', 'monolyth\render\Url_Helper'],
                compact('router')
            );
            $container->register(
                ['monolyth\Language_Access', 'monolyth\render\Url_Helper'],
                compact('language')
            );
            $adapter = $adapters->_current;
            $container->register(
                'monolyth\adapter\Access',
                compact('adapter', 'adapters')
            );
            $container->register('monolyth\Project_Access', compact('project'));
            if ($match
                && (array_key_exists(
                    'monad\core\Controller',
                    class_parents($match['controller'])
                )
            )) {
                require 'monad/config/dependencies.php';
            } else {
                require 'config/dependencies.php';
            }
            if (!$match) {
                throw new HTTP404_Exception;
            }
            if ($match['controller'] instanceof Redirect) {
                throw new HTTP301_Exception("{$match['controller']}");
            }
            self::setBookmark('Found controller');
            $controller = $match['controller'];
            unset($match['controller']);
            $o = new $controller($container);
            return $o($_SERVER['REQUEST_METHOD'], $match);
            $output = $function($uri);
        } catch (adapter\sql\Exception $e) {
            if ($project['test']
                && !($e instanceof adapter\sql\ConnectionFailed_Exception)
            ) {
                throw $e;
            }
            mail(
                $project['notifymail'],
                "Database down for {$project['site']}",
                "Page: {$_SERVER['REQUEST_URI']}\n".$e->getMessage()
            );
            $e = new render\DatabaseDown_Controller(new DependencyContainer);
            $e->project = $project;
            $e('GET', []);
            unset($e);
        } catch (HTTP5xx_Exception $e) {
            $parts = explode('\\', strtolower(get_class($e)));
            $code = str_replace(
                ['http', '_exception'],
                '',
                array_pop($parts)
            );
            $class = get_class($e);
            while (true) {
                $namespace = substr($class, 0, strrpos($class, '\\'));
                try {
                    $e = "$namespace\\render\HTTP{$code}_Controller";
                    $e = new $e($container);
                    $e('GET', []);
                    unset($e);
                    break;
                } catch (ClassNotLoaded_Exception $e) {
                    if (!($class = get_parent_class($class))) {
                        throw new HTTPUndefined_Exception(
                            get_class($e)
                        );
                    }
                }
            }
        } catch (HTTP4xx_Exception $e) {
            $parts = explode('\\', strtolower(get_class($e)));
            $code = str_replace(
                ['http', '_exception'],
                '',
                array_pop($parts)
            );
            $class = get_class($e);
            while (true) {
                $namespace = substr($class, 0, strrpos($class, '\\'));
                try {
                    $e = "$namespace\\render\HTTP{$code}_Controller";
                    $e = new $e($container);
                    $e('GET', []);
                    unset($e);
                    break;
                } catch (ClassNotLoaded_Exception $e) {
                    if (!($class = get_parent_class($class))) {
                        throw new HTTPUndefined_Exception(
                            get_class($e)
                        );
                    }
                }
            }
        } catch (HTTP3xx_Exception $e) {
            $code = str_replace(
                ['monolyth\\', 'http', '_exception'],
                '',
                strtolower(get_class($e))
            );
            if (isset($o) && $o->http->isXMLHttpRequest()) {
                $o = new Ajax_Redirect_Controller($container);
            } else {
                $o = new Redirect_Controller($container);
            }
            session_write_close();
            $o(
                'GET',
                [
                    'url' => $e->getMessage(),
                    'code' => $code,
                ]
            );
        }
        session_write_close();
    }
}

Monolyth::setBookmark('Start [included Monolyth]');

if (isset($_REQUEST['repost'])) {
    $post = @unserialize(@base64_decode($_REQUEST['repost']));
    if ($post) {
        $_POST = array_merge($post, $_POST);
    }
}

/** Alias base class manually. */
class_alias('monolyth\Monolyth', 'Monolyth');

