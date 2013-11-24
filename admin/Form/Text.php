<?php

namespace monolyth\admin;
use monad\core\I18n_Form;
use monad\admin\Language_Access;

class Text_Form extends I18n_Form implements Language_Access
{
    public static $IDENTIFIER = 'id';

    public function prepare()
    {
        $this->addText('id', $this->text('./id'))->disabled();
        $language = $this->projectlanguage->available[0];
        foreach ($this->projectlanguage->available as $lang) {
            $this->addTextarea("content[{$lang->id}]", $this->text('./content'))
                 ->setClass("language {$lang->code}");
        }
        $this->languagetabs = true;
        return parent::prepare();
    }
}

