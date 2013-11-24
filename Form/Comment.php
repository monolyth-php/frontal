<?php

namespace monolyth;
use ErrorException;

class Comment_Form extends core\Post_Form implements User_Access
{
    use render\Url_Helper;

    public function prepare()
    {
        $this->addTextarea('comment', $this->text('./comment'))->isRequired();
        $this->addHidden('references')->isRequired();
        $this->addHidden('type')->isRequired();
        $this->addHidden('replyto');
        $this->addHidden('owner');
        $this->addHidden('status');
        $this->addText('name', $this->text('./name'));
        $this->addEmail('email', $this->text('./email'));
        $this->addUrl('homepage', $this->text('./homepage'));
        $this->addHidden('ip')->isRequired();
        $this->addButton(self::BUTTON_SUBMIT, $this->text('./submit'));
        if (!$this->user->loggedIn()) {
            $this['name']->isRequired();
        } else {
            $this['owner']->isRequired()->value = $this->user->id();
        }
        $_POST['ip'] = $_SERVER['REMOTE_ADDR'];
        return parent::prepare();
    }
}

