<?php

/**
 * A generic container for registering dependencies.
 *
 * @package monolyth
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2011, 2012, 2013
 */

namespace monolyth;
use Closure;
use SplObserver;
use SplSubject;

class DependencyContainer
{
    private $registered = [];
    private $namespace = '';

    /**
     * Register dependencies for '$what'. $dependencies is a hash with the keys
     * serving as shorthand dependency names, and the values as either direct
     * references to a dependency, or a callable returning it.
     *
     * @param mixed $what Classname, namespace or object. As of Monolyth 0.41,
     *                    you may also pass an array of any of the above.
     * @param array $dependencies Hash of dependencies for $what.
     * @param callable $callback Optional function to call on $what after it's
     *                           satisfied. Should return $what.
     * @return void
     */
    public function register($what, $dependencies, callable $callback = null)
    {
        if (is_object($what)) {
            $what = get_class($what);
        }
        if (!is_array($what)) {
            $what = [$what];
        }
        if (!is_array($dependencies)) {
            $dependencies = [$dependencies];
        }
        foreach ($what as $one) {
            $one = ($one == '' ?
                substr($this->namespace, 0, -1) :
                $this->namespace
            ).$one;
            if ($one{0} == '\\') {
                $one = substr($one, 1);
            }
            foreach ($dependencies as $name => $value) {
                if (!isset($this->registered[$one])) {
                    $this->registered[$one] = [];
                }
                $this->registered[$one][$name] = [$value, $callback];
            }
        }
    }

    public function using($namespace, callable $fn)
    {
        $this->setNamespace($namespace);
        $fn();
        $this->setNamespace('');
    }

    private function setNamespace($namespace)
    {
        if (strlen($namespace) && substr($namespace, -1) != '\\') {
            $namespace .= '\\';
        }
        $this->namespace = $namespace;
    }

    /**
     * Helper method to implement a common default pattern in dependencies,
     * where Foo_Access gets a Foo_Finder and a Foo_Model, and the Foo_Finder
     * also gets the Foo_Model.
     *
     * Apart from models and finders, anything in the current namespace also
     * automatically receives the corresponding config file (which may or may
     * not actually contain something, of course).
     *
     * @param array $classes Base-classnames to register. In our example this
     *                       would be ['Foo'].
     * @see monolyth\Config::get
     */
    public function defaults(array $classes)
    {
        $namespace = $this->namespace;
        if (strlen($namespace)) {
            $namespace = substr($namespace, 0, -1);
        }
        $this->register(
            '',
            ['config' => Config::get(substr(
                $namespace,
                0,
                strpos($namespace, '\\')
            ))]
        );
        foreach ($classes as $name) {
            $cname = $name;
            if (strlen($cname)) {
                $cname .= '_';
            }
            if (!strlen($name)) {
                $name = substr($namespace, strrpos($namespace, '\\') + 1);
            }
            $name = strtolower($name);
            $this->register(
                "{$cname}Finder",
                [$name => function() use($cname, $namespace) {
                    $class = "$namespace\\{$cname}Model";
                    return new $class();
                }]
            );
            $names = $this->multiple($name);
            $this->register(
                "{$cname}Access",
                [
                    $names => function() use($cname, $namespace) {
                        $class = "$namespace\\{$cname}Finder";
                        return new $class();
                    },
                    $name => function() use($cname, $namespace) {
                        $class = "$namespace\\{$cname}Model";
                        return new $class();
                    },
                ]
            );
        }
    }

    /**
     * Internal helper method to (hopefully) turn a name into a valid English
     * multiple-form. E.g., model > models and country > countries.
     *
     * @param string $string The string to "multiply".
     * @return string Its multiple form.
     */
    private function multiple($string)
    {
        switch (substr($string, -1)) {
            case 'y': return substr($string, 0, -1).'ies';
            default: return $string.'s';
        }
    }

    /**
     * Fill the object with its dependencies. This should *always* be used
     * instead of blindly creating an object with 'new' (a few exceptions
     * notwithstanding).
     *
     * @param object $what Any object which may have dependencies.
     * @param array $parents Parent objects - used internally to prevent
     *                       endless loops.
     * @return $object The object with its dependencies (hopefully) satisfied.
     */
    public function satisfy($what, array $parents = [])
    {
        static $satisfied = [];
        if (!is_object($what)) {
            return $what;
        }
        $class = get_class($what);
        // If $what was already satisfied before, short-circuit here.
        $hash = spl_object_hash($what);
        if (array_key_exists($hash, $satisfied)) {
            return $what;
        }
        $callbacks = [];
        $walker = function(array &$deps, $depname)
                  use(&$what, &$callbacks, $parents)
        {
            list($dep, $callback) = $deps;
            if ($dep instanceof Closure) {
                $old = $dep;
                $dep = $dep($what);
            }
            // Don't resatisfy ourselves with our own class.
            if (is_object($dep) && get_class($dep) == get_class($what)) {
                return;
            }
            $shortcircuit = false;
            foreach ($parents as $parent) {
                if (is_object($parent)
                    && is_object($dep)
                    && get_class($parent) == get_class($dep)
                ) {
                    $dep =& $parent;
                    $shortcircuit = true;
                    break;
                }
            }
            if (!$shortcircuit) {
                $dep = $this->satisfy(
                    $dep,
                    array_merge($parents, [$what])
                );
            }
            if (is_callable($callback) && !in_array($callback, $callbacks)) {
                $callbacks[] = $callback;
            }
            if ($what instanceof SplSubject && $dep instanceof SplObserver) {
                $what->attach($dep);
            }
            if (!isset($what->$depname)) {
                $what->$depname = $dep;
            }

            // Objects implementing the special Reinstantiate interface should
            // NOT be reused. Currently, this is only needed for Text_Models,
            // but it's extensible this way.
            if ($dep instanceof core\Reinstantiate) {
                $dep = $old;
            }
        };
        $work = explode('\\', substr($class, 0, strrpos($class, '\\')));
        $names = [];
        foreach ($work as $i => $namespace) {
            $names[] = implode('\\', array_slice($work, 0, $i + 1));
        }
        $names = array_merge(
            $names,
            array_reverse(class_implements($class, false))
        );
        $names = array_merge(
            $names,
            array_reverse(class_uses($class, false))
        );
        $parents = class_parents($class, false);
        $names = array_merge($names, $parents);
        foreach ($parents as $parent) {
            $names += class_uses($parent, false);
        }
        $names[] = $class;
        $names = array_reverse($names);
        foreach ($names as $name) {
            if (isset($this->registered[$name])) {
                array_walk($this->registered[$name], $walker);
            }
        }
        foreach ($callbacks as $callback) {
            $what = $callback($what);
        }
        $satisfied[$hash] = $what;
        return $what;
    }
}

