<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\core\Staged_Controller;
use monolyth\Nologin_Required;
use monolyth\adapter;

class Create_Controller extends Staged_Controller
implements Nologin_Required, adapter\Access
{
    protected static $stages = ['profile', 'accept', 'success'];

    protected function postProfile(array $args)
    {
        $success = true;
        if (array_key_exists('name', $this->form)) {
            try {
                $this->adapter->field(
                    'monolyth_auth',
                    1,
                    ['LOWER(name)' => strtolower($this->form['name']->value)]
                );
                $success = false;
                $this->message->add(
                    self::MESSAGE_ERROR,
                    $this->text('create/error.name')
                );
            } catch (adapter\sql\NoResults_Exception $e) {
            }
        }
        if (array_key_exists('email', $this->form)) {
            try {
                $this->adapter->field(
                    'monolyth_auth',
                    1,
                    ['LOWER(email)' => strtolower($this->form['email']->value)]
                );
                $success = false;
                $this->message->add(
                    self::MESSAGE_ERROR,
                    $this->text('create/error.email')
                );
            } catch (adapter\sql\NoResults_Exception $e) {
            }
        }
        return $success;
    }

    protected function postAccept(array $args)
    {
        if ($this->form['terms']->value != 1) {
            $this->message->add(
                self::MESSAGE_ERROR,
                $this->text('create/error.terms')
            );
            return false;
        }
        if ($error = call_user_func(
            $this->create,
            $this->form->getArrayCopy() + $this->session->get('Form')
        )) {
            $this->message->add(
                self::MESSAGE_ERROR,
                $this->text('create/error/'.str_replace(' ', '.', $error))
            );
            while (static::$currentStage) {
                $this->previousStage();
            }
            $this->setForm(0);
            return false;
        }
        return true;
    }
}

