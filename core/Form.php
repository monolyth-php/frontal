<?php

namespace monolyth\core;
use ArrayObject;
use ErrorException;
use monolyth\utils\Translatable;
use monolyth\utils\Name_Helper;
use monolyth\render\UnknownElement_Exception;
use monolyth\render\form\File;
use monolyth\render\form\Radio;
use monolyth\render\form\Hidden;
use monolyth\render\form\Media;

abstract class Form extends ArrayObject
{
    use Name_Helper, Translatable {
        Translatable::text as _text;
    }

    const BUTTON_SUBMIT = 1;
    const BUTTON_RESET = 2;
    const BUTTON_CANCEL = 3;
    const BUTTON_BUTTON = 4;

    private $controller, $done = false;
    protected $method = 'post', $fieldsets = [], $buttons = [],
              $errors = [], $action = '', $class = null, $sources = [],
              $attributes = [], $placeholders = false;
    public $model,
           $id,
           $views = [
                'textarea' => 'monolyth\render\form\slice/rowvertical',
                'texthtml' => 'monolyth\render\form\slice/rowvertical',
           ];

    public function __construct($id = null)
    {
        if (!isset($this->id)) {
            $this->id = isset($id) ? $id : $this->getId();
        }
        $this->attributes += [
            'id' => $this->getId(),
            'method' => &$this->method,
            'action' => &$this->action,
        ];
    }

    public function usePlaceholders($use = true)
    {
        $this->placeholders = $use;
    }

    public function attributes()
    {
        $return = [];
        foreach ($this->attributes as $name => $value) {
            $return[] = sprintf('%s="%s"', $name, htmlentities($value));
        }
        return implode(' ', $return);
    }

    public function text($id)
    {
        $args = func_get_args();
        $id = array_shift($args);
        if (!is_array($id)) {
            $id = [$id];
        }
        $fallback = substr($id[0], strpos($id[0], '/') + 1);
        $fallback = $this->getNamespace($this)."\\column/$fallback";
        $id[] = $fallback;
        array_unshift($args, $id);
        return call_user_func_array([$this, '_text'], $args);
    }

    public function prepare()
    {
        if ($this->hasFiles()) {
            $this->attributes += ['enctype' => 'multipart/form-data'];
        }
        if ($class = $this->classname()) {
            $this->attributes += compact('class');
        }
        return $this->load();
    }

    public function load()
    {
        $data = [];
        $r = function(&$source, $keys, $key, &$values) use(&$r) {
            $keys[] = "[$key]";
            $skey = implode('', $keys);
            if (isset($this[$skey]) || !is_array($values)) {
                $source[$skey] = $values;
                return;
            }
            foreach ($values as $skey => $svalues) {
                $r($source, $keys, $skey, $svalues);
            }
        };
        foreach ($this->sources as $source) {
            if ($source != $_FILES) {
                foreach ($source as $name => $value) {
                    if (is_array($value)
                        && (!isset($this[$name])
                            || $this[$name] instanceof Media
                        )
                    ) {
                        $reduced = false;
                        foreach ($value as $key => $v) {
                            $reduced = true;
                            $r($source, [$name], $key, $v);
                        }
                        if ($reduced) {
                            unset($source[$name]);
                        }
                    }
                }
            }
            $data += $source;
        }
        foreach ($data as $name => $value) {
            if (!isset($this[$name])
                || !($this[$name] instanceof Element)
            ) {
                continue;
            }
            if ($this[$name] instanceof Radio) {
                if ($value == $this[$name]->__get('value')) {
                    $this[$name]->checked();
                } else {
                    $this[$name]->unchecked();
                }
            } else {
                $this[$name]->__set('value', $value);
            }
        }
        return $this;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function hasFiles()
    {
        foreach ($this as $key => $value) {
            if ($value instanceof File) {
                return true;
            }
        }
        return false;
    }

    public function addSource($source)
    {
        if (is_null($source)) {
            return $this;
        }
        if (is_callable($source)) {
            $source = $source();
        }
        if (!is_array($source) && is_object($source)) {
            $source = $source->getArrayCopy();
        }
        $this->sources[] = $source;
        return $this;
    }

    public function getId()
    {
        if (isset($this->id)) {
            return $this->id;
        }
        $name = get_class($this);
        if ($name == __CLASS__) {
            $name = get_class($this->model);
        }
        $name = strtolower($name);
        $name = preg_replace("@(\\\\)?(model|_?form|ui\\\\)@", '', $name);
        $this->id = str_replace('\\', '_', $name);
        return $this->id;
    }

    public function getMethod()
    {
        return $this->method;
    }

    protected function addField(Element $element, $label = null)
    {
        return $element;
    }

    protected function addButton($type, $text, $name = null)
    {
        switch ($type) {
            case self::BUTTON_SUBMIT:
                $class = 'monolyth\render\form\Submit_Button';
                if (!isset($name)) {
                    $name = 'act_submit';
                }
                break;
            case self::BUTTON_RESET:
                $class = 'monolyth\render\form\Reset_Button';
                if (!isset($name)) {
                    $name = 'act_reset';
                }
                break;
            case self::BUTTON_CANCEL:
                $class = 'monolyth\render\form\Cancel_Button';
                if (!isset($name)) {
                    $name = 'act_cancel';
                }
                break;
            case self::BUTTON_BUTTON:
                $class = 'monolyth\render\form\Button';
                if (!isset($name)) {
                    $name = 'act_custom';
                }
                break;
        }
        $this->buttons[] = new $class($this, $text, $name);
        return $this;
    }

    public function getButtons()
    {
        return array_unique($this->buttons);
    }

    public function clearButtons()
    {
        $names = func_get_args();
        if (!$names) {
            $this->buttons = [];
        } else {
            foreach ($this->buttons as $i => $b) {
                if (in_array($b->name(), $names)) {
                    unset($this->buttons[$i]);
                }
            }
        }
    }

    public function getFieldsets()
    {
        if ($this->done) {
            return $this->fieldsets;
        }
        foreach ($this->fieldsets as &$fieldset) {
            foreach ($fieldset as &$fields) {
                if (is_array($fields)) {
                    foreach ($fields as &$field) {
                        try {
                            $field = $this[$field];
                        } catch (ErrorException $e) {
                            $field = null;
                        }
                    }
                } else {
                    try {
                        $fields = $this[$fields];
                    } catch (ErrorException $e) {
                        $field = null;
                    }
                }
            }
        }
        $this->done = true;
        return $this->fieldsets;
    }

    public function getLabel(Model $m, $name, Element $o)
    {
        $id = strtolower(get_class($m));
        $ids = [];
        $ids[] = '\core\labels/'.substr($id, strrpos($id, '\model') + 7);
        $ids[] = str_replace('\model', '\model\labels', $id);
        $id = strtolower(get_class($o));
        $ids[] = str_replace(
            'monolyth\render\form\\',
            'monolyth\render\form\labels/',
            strtolower($id)
        );
        foreach ($ids as $id) {
            if ($this->text->exists($id)) {
                $label = $this->text->get($id);
                break;
            }
        }
        if (!isset($label)) {
            $label = $this->text->get($id);
        }
        return $label;
    }

    public function validate()
    {
        $errors = [];
        foreach ((array)$this as $name => $field) {
            if ($fielderrors = $field->getErrors()) {
                $errors[$name] = $fielderrors;
            }
        }
        return $errors ? $errors : null;
    }

    public function errors(array $errors = null)
    {
        if ($errors) {
            $this->errors = $errors;
        }
        return $this->errors;
    }

    public function getArrayCopy()
    {
        $return = [];
        foreach ($this as $key => $value) {
            $return[$key] = $value->value;
        }
        return $return;
    }

    public function getHiddenFields()
    {
        $fields = [];
        foreach ((array)$this as $key => $value) {
            if ($value instanceof Hidden) {
                $fields[$key] = $value;
            }
        }
        return $fields;
    }

    public function getPublicFields()
    {
        $fields = [];
        foreach ((array)$this as $key => $value) {
            if (!($value instanceof Hidden)) {
                $fields[$key] = $value;
            }
        }
        return $fields;
    }

    public function getView(Element $e, $default = null)
    {
        if (!isset($default)) {
            $default = 'monolyth\render\form\slice/row';
        }
        if (isset($this->views[$e->getName()])) {
            return $this->views[$e->getName()];
        }
        $class = strtolower(get_class($e));
        $class = substr($class, strrpos($class, '\\') + 1);
        if (isset($this->views[$class])) {
            return $this->views[$class];
        }
        return $default;
    }

    public function classname()
    {
        return $this->class;
    }

    public function __call($method, $args)
    {
        if (preg_match("@^add([A-Z]\w+)$@", $method, $match)) {
            $element = "_{$match[1]}";
            if (!(isset($this->$element)
                && $this->$element instanceof Element
            )) {
                throw new UnknownElement_Exception($match[1]);
            }
            $element = clone $this->$element;
            $element->setParent($this);
            if (!isset($args[1])) {
                $args[1] = null;
            }
            if (!is_null($args[1])) {
                if ($this->placeholders) {
                    $element->setPlaceholder($args[1]);
                } else {
                    $element->setLabel($args[1]);
                }
            }
            unset($args[1]);
            $args = array_values($args);
            call_user_func_array([$element, 'prepare'], $args);
            $element->prependFormname($this->getId());
            if ($element instanceof File) {
                $this->addSource($_FILES);
            }
            if ($element instanceof Radio
                && !array_key_exists($args[0], $this->sources[0])
                && (($this instanceof Get_Form && $_GET)
                    || ($this instanceof Post_Form && $_POST)
                )
            ) {
                $this->sources[0][$args[0]] = '';
            }
            $this[$element->getName()] = $element;
            return $element;
        }
    }
}

