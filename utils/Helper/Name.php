<?php

namespace monolyth\utils;
use InvalidArgumentException;

trait Name_Helper
{
    public function sanitize($from)
    {
        if (is_object($from)) {
            $from = get_class($from);
        }
        if (is_null($from)) {
            return null;
        }
        if (!is_string($from)) {
            throw new InvalidArgumentException(
                '$from must be a string or an object.'
            );
        }
        return $from;
    }

    public function stripKnownTypes($name)
    {
        return preg_replace(
            '@(_?(controller|form|model|finder|helper)$|inline_)@i',
            '',
            $name
        );
    }

    public function getNamespace($from, $use = null)
    {
        $from = $this->sanitize($from);
        if (is_null($from)) {
            return null;
        }
        if (strpos($from, '\\') === 0) {
            return null;
        }
        if (strpos($from, '\\') === false) {
            return $this->getNamespace($use);
        }
        $namespace = substr($from, 0, strrpos($from, '\\'));
        if (substr($namespace, -5) == '\core') {
            $namespace = substr($namespace, 0, -5);
        }
        return $namespace;
    }

    public function stripNamespace($from)
    {
        $from = $this->sanitize($from);
        if (strpos($from, '\\') === false) {
            return $from;
        }
        return substr($from, strrpos($from, '\\') + 1);
    }

    public function toFilename($from, $use = null, $format = '%s/%s.php')
    {
        if (substr($from, 0, 2) == './') {
            $from = substr($from, 2);
            $from = $this->merge($from, $use);
            $namespace = $this->getNamespace($from);
        } else {
            $namespace = $this->getNamespace($from, $use);
        }
        $filename = str_replace(
            ['\\', '/', '_'],
            DIRECTORY_SEPARATOR,
            sprintf($format, $namespace, strtolower($this->stripNamespace($from)))
        );
        $filename = str_replace(
            DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            $filename
        );
        if ($filename{0} == DIRECTORY_SEPARATOR) {
            $filename = substr($filename, 1);
        }
        return $filename;
    }

    public function merge($from, $use)
    {
        $parts = $this->stripKnownTypes($this->stripNamespace($use));
        $namespace = $this->getNamespace($from, $use);
        $parts = implode('_', array_reverse(explode('_', $parts)));
        $parts .= DIRECTORY_SEPARATOR
                  .$this->stripKnownTypes($this->stripNamespace($from));
        // Catch edge case:
        if ($parts{0} == DIRECTORY_SEPARATOR) {
            $parts = substr($parts, 1);
        }
        // When dealing with text IDs (especially when coming from forms),
        // make sure only valid characters are in the string.
        $parts = preg_replace("@[^A-Za-z0-9_\./-]+@", '', $parts);
        return "$namespace\\$parts";
    }

    public function generate($object, $id)
    {
        if (substr($id, 0, 2) == './') {
            $id = substr($id, 2);
            $id = $this->merge($id, $object);
            $namespace = $this->getNamespace($id);
            $id = "$namespace\\".$this->stripNamespace($id);
        } elseif ($id{0} != '\\') {
            $namespace = $this->getNamespace($id, $object);
            $id = "$namespace\\".$this->stripNamespace($id);
        }
        return strtolower(str_replace('_', DIRECTORY_SEPARATOR, $id));
    }
}

