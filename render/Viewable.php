<?php

namespace monolyth\render;
use monolyth\core\Model;
use monolyth\Finder;
use monolyth\core\Parser;

trait Viewable
{
    /**
     * Load, initialise and return a view.
     *
     * Using this helper-method provides a number of benefits:
     * - $this is automatically passed when constructing the view
     * - The current namespace is appended if none is defined
     *
     * @param string $file The view file to load
     * @param mixed $args Optional data to attach (as in, shorthand).
     * @return View object
     */
    public function view($file, array $args = [])
    {
        $view = $this->container->satisfy(new View($file, $this));
        $tmp = [];
        foreach ($this as $key => $value) {
            if ($value instanceof Parser) {
                $view->addParser($value);
            } elseif ($value instanceof Finder) {
                continue;
            } elseif ($value instanceof Model) {
                if ($value->isLoaded()) {
                    $tmp[$key] = $value;
                }
            } elseif (is_object($value)) {
                $tmp[$key] = $value;
            }
        }
        $args += $view->data();
        $args += $this->attachments;
        $args += $tmp;
        $view->data($args);
        return $view;
    }
}

