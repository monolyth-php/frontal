<?php

/**
 * This is the base for the rest of MonoLyth.
 * It defines the Monolyth-class, the autoloader,
 * as well as doing a few other required includes.
 *
 * @package monolyth
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2008, 2009, 2010, 2011, 2012, 2014
 */
namespace monolyth;
use adapter\Access as Adapter_Access;
use ErrorException;
use monolyth\core\Project;
use monad\admin\Project as Monad_Project;

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
 * Since we don't have the autoloader here yet, we need to manually include the
 * Access adapters the Monolyth base class needs.
 */
require_once 'Access/Adapter.php';
require_once 'monolyth/core/Singleton.php';
require_once 'monolyth/Logger.php';
require_once 'monolyth/Access/Logger.php';
require_once 'monolyth/Access/Language.php';
require_once 'monolyth/render/Access/Router.php';

/**
 * The Monolyth base-class.
 *
 * The Monolyth base-class is defined as abstract and is just that.
 * So, it's not a 'base-class' in the sense that everything should extend it,
 * but rather a bootstrap.
 *
 * @package MonoLyth
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright Copyright MonoMelodies 2008, 2009, 2010, 2011, 2012, 2014
 */
abstract class Monolyth
{
    private static $project;
    private static $router;

    use Language_Access;
    use Logger_Access;

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

    /**
     * When done setting up, run MonoLyth.
     *
     * The main function. Called after setting up a few things.
     * This dispatches control of your site to a controller.
     * For CLI-scripts, this usually isn't called.
     */
    public static function run(Project $project, $theme = 'default')
    {
        static::$project = $project;
        try {
            $language = self::language();
            $router = call_user_func(
                require_once 'config/routing.php',
                self::router()
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
            if ($match
                && (array_key_exists(
                    'monad\core\Controller',
                    class_parents($match['controller'])
                )
            )) {
                static::$project = new Monad_Project($theme);
                static::$project['public'] = $project['public'];
            }
            if (!$match) {
                throw new HTTP404_Exception;
            }
            if ($match['controller'] instanceof Redirect) {
                throw new HTTP301_Exception("{$match['controller']}");
            }
            self::logger()->log('Found controller');
            $controller = $match['controller'];
            unset($match['controller']);
            $o = new $controller;
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
                    $e = new $e;
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
                    $e = new $e;
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
            if (isset($o) && $o::http()->isXMLHttpRequest()) {
                $o = new Ajax_Redirect_Controller;
            } else {
                $o = new Redirect_Controller;
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

    public function project()
    {
        return self::$project;
    }

    public function router()
    {
        if (!isset(self::$router)) {
            self::$router = new render\Router;
        }
        return self::$router;
    }
}

Monolyth::logger()->log('Start [included Monolyth]');

if (isset($_REQUEST['repost'])) {
    $post = @unserialize(@base64_decode($_REQUEST['repost']));
    if ($post) {
        $_POST = array_merge($post, $_POST);
    }
}

/**
 * Register monolyth autoloader. Note that it throws an Exception if the class
 * could not be loaded; hence, you'll probably want to define additional
 * autoloaders first, or with the prepend-argument set to true.
 */
spl_autoload_register(['monolyth\Monolyth', '__autoload']);

/** Alias base class manually. */
class_alias('monolyth\Monolyth', 'Monolyth');

