<?php

/**
 * Abstract base model for I18n classes (Languages, countries, regions etc.).
 *
 * @package monolyth
 * @subpackage core
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2012
 */

namespace monolyth\core;
use StdClass;
use ErrorException;

abstract class I18n_Model
{
    protected $exception,
              $map = [],
              $order = [],
              $status = ['current' => 'nl', 'default' => 'nl'];
    public $available = [], $config;

    protected function build(array $rows)
    {
        foreach ($rows as $row) {
            $code = $row['code'];
            $this->$code = $this->add(
                $row['id'],
                $row['code'],
                $row['title'],
                isset($row['fallback']) ? $row['fallback'] : null
            );
            if (isset($row['is_default']) && $row['is_default']) {
                $this->status['default'] = $this->status['current']
                                         = $row['code'];
            }
            $this->order[] = $this->$code;
            if (!isset($row['sortorder']) || $row['sortorder']) {
                $this->available[] = $this->$code;
            }
        }
    }

    /**
     * Setup an i18n object for further use.
     *
     * @param int $id Integer identifying the object.
     * @param string $code A short code for easy reference. These should if
     *                     possible follow ISO-standards.
     * @param string $title A human-readable title.
     * @return StdClass The added object.
     */
    protected function add($id, $code, $title, $fallback)
    {
        $o = new StdClass;
        foreach (['id', 'code', 'title', 'fallback'] as $prop) {
            $o->$prop = $$prop;
        }
        $this->map[$o->id] = $o->code;
        return $o;
    }

    /**
     * Reflection method to get a property.
     */
    public function __get($what)
    {
        if (!isset($this->status[$what])) {
            return null;
        }
        if (!(
            isset($this->{$this->status[$what]}) &&
            in_array($this->{$this->status[$what]}, $this->available)
        )) {
            return null;
        }
        return $this->{$this->status[$what]};
    }

    /**
     * Reflection method to set a propery.
     */
    public function __set($what, $value)
    {
        if (is_object($value)) {
            $code = $value->code;
        } elseif (is_numeric($value) and $obj = $this->get($value)) {
            $code = $obj->code;
        } else {
            $code = $value;
        }
        if (isset($this->status[$what])) {
            $this->status[$what] = $code;
        } else {
            $this->$what = $value;
        }
    }

    /**
     * Check to see if the requested object is available.
     * <code>
     * $languageconfig = MonoLyth::get('LanguageConfig');
     * echo $languageconfig->is_available($languageconfig->en);
     * </code>
     *
     * @param mixed $languages The language object(s) to check. You can also
     *                         pass them as numeric IDs (e.g. self::EN) or
     *                         their string representations (e.g. 'en').
     *                         You may pass either a scalar or an array of
     *                         objects/integers/strings.
     * @return bool True if available, false if not.
     */
    public function isAvailable($languages)
    {
        if (!is_array($languages)) {
            $languages = [$languages];
        }
        foreach ($languages as $lan) {
            if (is_int($lan)) {
                $lan = $this->get($lan);
            } elseif (is_string($lan)) {
                $lan = $this->$lan;
            }
            if (in_array($lan, $this->available)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return the default listing order of languages.
     *
     * @return array An array of language objects.
     */
    public function order()
    {
        return $this->order;
    }

    /**
     * Get a language object by id.
     *
     * @param null|int $id The id to get. If set to null (the default) the current
     *                     language is returned. To specify, use a constant of the
     *                     form LanguageConfig::LANGUAGE.
     * @return StdClass|null A language object, or null on failure.
     */
    public function get($id = null)
    {
        if (!$id) {
            $id = $this->{$this->status['current']}->id;
        }
        if (!isset($this->map[$id])) {
            return null;
        }
        $code = $this->map[$id];
        return $this->$code;
    }

    /**
     * Set the current language by code.
     *
     * @param string $code The language to set, identified by shortcode.
     */
    public function set($code)
    {
        if (is_object($code)) {
            $code = $code->code;
        }
        if (is_numeric($code)) {
            try {
                $code = $this->map[$code];
            } catch (ErrorException $e) {
            }
        }
        if (!isset($this->$code)) {
            $exception = $this->exception;
            throw new $exception($code);
        }

        if ($this->status['current'] == $code) {
            return; // nothing's changed
        }
        $this->status['current'] = $code;
    }

    public function fallbacks($code)
    {
        $fallbacks = [$this->$code->id];
        $curr = $this->$code;
        do {
            if (isset($curr->fallback) && !is_null($curr->fallback)) {
                $curr = $this->get($curr->fallback);
                $fallbacks[] = $curr->id;
            } else {
                break;
            }
        } while (true);
        return $fallbacks;
    }
}

