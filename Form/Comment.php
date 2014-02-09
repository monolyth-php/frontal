<?php

namespace monolyth;
use ErrorException;

class Comment_Form extends core\Post_Form
{
    use render\Url_Helper;
    use User_Access;

    public function __construct()
    {
        parent::__construct();
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
        if (!self::user()->loggedIn()) {
            $this['name']->isRequired();
        } else {
            $this['owner']->isRequired()->value = self::user()->id();
        }
        $_POST['ip'] = $_SERVER['REMOTE_ADDR'];
        return parent::prepare();
    }
}

