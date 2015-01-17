<?php

namespace monolyth\Form;

use Improse\Render\Html;

class View
{
    protected $form;

    public function __construct($language, $class)
    {
        $class = str_replace('-', '\\', $class);
        $this->form = new $class;
    }

    public function __invoke()
    {
        $html = new Html($_GET['view'].'.php');
        $form = new Html('monolyth/Form/form.php');
        ob_start();
        $html(['form' => $this->form]);
        $form(['form' => $this->form, 'content' => ob_get_clean()]);
    }
}

