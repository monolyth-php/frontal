<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
if (!(isset($form) && $form instanceof Login_Form)) {
    $form = new Login_Form;
}
echo $view(
    ['monolyth\render\form\table', 'monolyth\render\form\form'],
    compact('form')
);

return ['title' => $text('./title')];

