<?php

namespace monolyth\core;
use monolyth\render\View;
use monolyth\utils\Translatable;
use monolyth\utils\Name_Helper;
use ErrorException;
use Exception as E;

abstract class Element
{
    use Translatable, Name_Helper;

    /** {{{ Default form-element errors. */
    /** Error: field is required. */
    const ERROR_MISSING = 'missing';
    /** Error: invalid value. */
    const ERROR_INVALID = 'invalid';
    /** Error: field should match something, but it doesn't. */
    const ERROR_NOMATCH = 'nomatch';
    /** Error: field should be different, but is the same. */
    const ERROR_MATCH = 'match';
    /** Error: field should be unique, but isn't. */
    const ERROR_EXISTS = 'exists';
    /** }}} */

    protected $name, $id, $basename, $label, $type, $options = [],
        $renderOptions = ['id', 'name', 'value', 'type'],
        $value, $original, $parent;
    private $tests = [];
    public $selfClosing = false, $expandAttributes = false, $_Label, $text,
           $language, $config;

    public function prepare($name, array $options = [])
    {
        $this->name = $name;
        $this->id = isset($options['id']) ?
            $options['id'] :
            self::nameToId($name);
        $options += ['name' => $name, 'id' => $this->id];
        $this->options = $options + $this->options;
    }

    public function prependFormname($id)
    {
        if (isset($this->options['id'])) {
            $this->id = $this->options['id'] = "$id-{$this->options['id']}";
        } else {
            $this->id = self::nameToId("{$id}[{$this->name}]");
        }
    }

    public function __get($name)
    {
        if ($name != 'value') {
            return null;
        }
        if (!(isset($this->options['required'])
            && $this->options['required']
        )) {
            if (is_scalar($this->value) && !strlen(trim($this->value))) {
                return null;
            }
            if (is_array($this->value) && !strlen(implode('', $this->value))) {
                return null;
            }
        }
        return $this->value;
    }

    public function __set($name, $value)
    {
        if ($name != 'value') {
            return null;
        }
        if (!(isset($this->options['required'])
            && $this->options['required']
        )) {
            if ((is_array($value) && !$value)
                || (!is_array($value) && !strlen(trim($value)))
            ) {
                $value = null;
            }
        }
        $this->original = $this->value;
        $this->value = $value;
        unset($this->options[$name]);
        return $value;
    }

    public static function nameToId($name)
    {
        $name = preg_replace('/[\W]+/', '-', $name);
        return trim(preg_replace('/[-]+/', '-', $name), '-');
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function disabled($set = true)
    {
        return $this->setOption('disabled', true);
    }

    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
        return $this;
    }

    public function getOption($name)
    {
        return isset($this->options[$name]) ?
            $this->options[$name] :
            null;
    }

    public function setClass($class)
    {
        $this->options['class'] = $class;
        $this->renderOptions[] = 'class';
        return $this;
    }

    public function addClass($class)
    {
        $classes = [];
        if (isset($this->options['class'])) {
            $classes = explode(' ', $this->options['class']);
        }
        $classes = array_unique(array_merge($classes, explode(' ', $class)));
        $this->options['class'] = implode(' ', $classes);
    }

    public function setPlaceholder($text)
    {
        $this->options['placeholder'] = $text;
        $this->renderOptions[] = 'placeholder';
        return $this;
    }

    public function setLabel($label)
    {
        if (!is_object($this->_Label)) {
            return $this;
        }
        $l = clone $this->_Label;
        if (substr($label, 0, 3) == ':t:') {
            $me = preg_replace('@\[.*?\]@', '', $this->name);
            $label = "./$me/".substr($label, 3);
            $label = $this->generate($this->parent, $label);
            $label = $this->text($label);
        }
        $l->prepare($this, $label);
        $this->label = $l;
        return $this;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setParent($form)
    {
        $this->parent = is_object($form) ? get_class($form) : $form;
    }

    public function setMessage($message)
    {
        $this->options['data-message'] = $message;
        return $this;
    }

    public function setTabindex($tabindex)
    {
        $this->options['tabindex'] = (int)$tabindex;
        $this->renderOptions[] = 'tabindex';
    }

    public function renderOptions()
    {
        foreach (array_unique($this->renderOptions) as $prop) {
            if (!isset($this->options[$prop])) {
                try {
                    $this->options[$prop] = $this->$prop;
                } catch (ErrorException $e) {
                    $this->options[$prop] = $prop;
                }
            }
        }
        $tmp = $this->options;
        foreach ($tmp as $name => $value) {
            if ($name == 'label') {
                unset($tmp[$name]);
                continue;
            }
            if ($name == 'type') {
                $value = array_shift(explode('/', $value));
            }
            if (is_bool($value) && $value) {
                if (!$this->expandAttributes) {
                    $tmp[$name] = $name;
                } else {
                    $tmp[$name] = sprintf('%s="%s"', $name, $name);
                }
                continue;
            }
            $tmp[$name] = sprintf('%s="%s"', $name, htmlspecialchars($value));
        }
        return implode(' ', $tmp);
    }

    public function addTest($fn)
    {
        $this->tests[] = $fn;
        return $this;
    }

    public function null()
    {
        $this->nullAllowed = true;
        return $this;
    }

    public function nullAllowed()
    {
        return !(isset($this->options['required'])
            && $this->options['required']);
    }

    public function isDisabled()
    {
        return isset($this->options['disabled']) && $this->options['disabled'];
    }

    /**
     * {{{ Some default tests, for use in Forms. These can all be chained,
     *     e.g. $element->isRequired()->isInteger()->isGreaterThanZero();
     *     The isSomething methods therefore all return $this.
     */

    /** This is a required field. */
    public function isRequired()
    {
        $this->options['required'] = true;
        $this->renderOptions[] = 'required';
        $error = self::ERROR_MISSING;
        return $this->addTest(function($value) use ($error) {
            if (is_array($value)) {
                return $value ? null : $error;
            }
            return strlen(trim($value)) ? null : $error;
        });
    }

    /** The field must contain an integer. */
    public function isInteger()
    {
        $error = self::ERROR_INVALID;
        return $this->addTest(function($value) use ($error) {
            return $value == (int)$value ? null : $error;
        });
    }

    /** The field must contain a number greater than zero. */
    public function isGreaterThanZero()
    {
        $error = self::ERROR_INVALID;
        return $this->addTest(function($value) use ($error) {
            return (float)$value > 0 ? null : $error;
        });
    }

    /** The field must equal the value supplied. */
    public function isEqualTo($test)
    {
        $error = self::ERROR_NOMATCH;
        return $this->addTest(function($value) use ($error, $test) {
            return $value == $test ? null : $error;
        });
    }

    /** The field must NOT equal the value supplied. */
    public function isNotEqualTo($test)
    {
        $error = self::ERROR_MATCH;
        return $this->addTest(function($value) use ($error, $test) {
            return $value != $test ? null : $error;
        });
    }

    /** The field must match another field in the supplied form. */
    public function matchesField(Form $form, $name)
    {
        $error = self::ERROR_NOMATCH;
        $this->options['data-equals'] = $form[$name]->getName();
        return $this->addTest(function($value) use ($error, $form, $name) {
            return $value == $form[$name]->value ? null : $error;
        });
    }

    /** The field shouldn't match another field in the supplied form. */
    public function differsFromField(Form $form, $name)
    {
        $error = self::ERROR_MATCH;
        $this->options['data-notequals'] = $form[$name]->getName();
        return $this->addTest(function($value) use ($error, $form, $name) {
            return $value != $form[$name]->value ? null : $error;
        });
    }

    /** The field must match the pattern supplied. */
    public function mustMatch($pattern)
    {
        $error = self::ERROR_INVALID;
        $this->options['pattern'] = $pattern;
        return $this->addTest(function($value) use ($error, $pattern) {
            if (!strlen(trim($value))) {
                return null;
            }
            return preg_match("@^$pattern$@", trim($value)) ? null : $error;
        });
    }

    /** The maximum length of the field. */
    public function maxLength($length, $size = null)
    {
        $error = self::ERROR_INVALID;
        if (!isset($size)) {
            $size = min(32, $length);
        }
        $this->options['maxlength'] = (int)$length;
        $this->options['size'] = (int)$size;
        return $this->addTest(function($value) use ($error, $length) {
            return mb_strlen(trim($value), 'UTF-8') <= (int)$length ?
                null :
                $error;
        });
    }
    /** }}} */

    public function hasMaxLength()
    {
        return isset($this->options['maxlength']) ?
            $this->options['maxlength'] :
            null;
    }

    public function __toString()
    {
        if (isset($this->value) && !is_scalar($this->value)) {
            $this->value = base64_encode(serialize($this->value));
        }
        try {
            $class = get_class($this);
            $namespace = substr($class, 0, strrpos($class, '\\'));
            $view = new View("$namespace\\slice/{$this->type}");
            $view->data(['o' => $this]);
            return $view();
        } catch (E $e) {
            return get_class($e)."\n".$e->getMessage()."\n";
        }
    }

    public function getErrors()
    {
        foreach ($this->tests as $test) {
            if ($error = $test($this->value)) {
                if ($error != 'missing' && !isset($this->value)) {
                    // Empty elements that aren't required shouldn't
                    // throw errors.
                    return null;
                }
                return $error;
            }
        }
        return null;
    }
}

