<?php

/**
 * Form for handling logins.
 *
 * @package monolyth
 * @subpackage account
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2009, 2010, 2011, 2012, 2013
 */

namespace monolyth\account;
use monolyth\HTTP_Access;
use monolyth\core\Post_Form;
use monolyth\render\Url_Helper;
use monolyth\utils\Translatable;

/**
 * Logins take a 'name' input and a 'pass' password,
 * and optionally a 'remember' checkbox input.
 */

class Login_Form extends Post_Form implements HTTP_Access
{
    use Translatable, Url_Helper;

    protected $attributes = ['data-history' => 0];

    public function prepare()
    {
        $this->class = 'nohistory';
        $this->addText('name', $this->text('./name'))
             ->isRequired()
             ->setPlaceholder($this->text('./name'));
        $this->addPassword('pass', $this->text('./pass'))
             ->isRequired()
             ->setPlaceholder($this->text('./pass'));
        $this->addCheckbox('remember', $this->text('./remember'));
        $this->views['remember'] = 'monolyth\render\form\slice/rowsingle';
        $this->addButton(self::BUTTON_SUBMIT, $this->text('./submit'));
        parent::prepare();
        $redir = $this->http->getRedir();
        if ($redir == $this->url('monolyth/account/login')
            || $redir == $this->url('monolyth/account/login', [], true)
        ) {
            $redir = $this->url('');
        }
        $this->action = $this->url('monolyth/account/login')
                       .'?redir='.urlencode($redir);
        $this->fieldsets = [$this->text('monolyth\account\login/title') => [
            'name',
            'pass',
            'remember',
        ]];
        return $this;
    }
}

