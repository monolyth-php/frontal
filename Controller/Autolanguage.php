<?php

/**
 * @package monolyth
 */

namespace monolyth;

class Autolanguage_Controller extends Controller
implements core\Autolanguage_Controller
{
    use core\Autolanguage;

    protected function guessLanguage(Language_Model $languages = null)
    {
        return $this->_guessLanguage($this->language);
    }
    
    protected function get(array $args)
    {
        return $this->_get($args);
    }
}

