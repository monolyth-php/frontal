<?php

/**
 * @package monolyth
 * @subpackage core
 */

namespace monolyth\core;
use monolyth\Controller;
use monolyth\Session_Access;
use monolyth\DependencyContainer;
use monolyth\HTTP301_Exception;
use ErrorException;
use monolyth\Message;

abstract class Staged_Controller extends Controller
{
    protected static
        /** Available steps. */
        $stages = [],
        /** Current step. */
        $currentStage = 0;

    public function __construct()
    {
        if (isset($_POST['__stage'])) {
            if (isset($_POST['act_previous'])) {
                $_POST['__stage']--;
            }
            $stage = $_POST['__stage'];
        }
        if (!isset($stage) || $stage < 0) {
            $stage = 0;
        }
        parent::__construct(); 
        if (!self::session()->exists('Form')) {
            self::session()->set('Form', []);
        }
        $this->setStage($stage);
        $this->setForm($stage);
    }

    public function setForm($stage)
    {
        $stage = max(0, $stage);
        // Construct form name from the current stage.
        $name = ucfirst(static::$stages[$stage]);
        $me = get_class($this);
        $ns = substr($me, 0, strrpos($me, '\\'));
        $me = substr($me, 0, strrpos($me, '_Controller'));
        $me = substr($me, strrpos($me, '\\') + 1);
        $class = "$ns\\{$name}_{$me}_Form";
        $this->form = new $class;
        if (isset($_REQUEST['act_cancel'])) {
            $this->cancel();
        }
        $this->form->addHidden('__stage');
        $this->form['__stage']->value = static::$currentStage;
    }

    public function setStage($stage)
    {
        static::$currentStage = max(0, $stage);
    }

    protected function get(array $args)
    {
        $fn = static::$stages[static::$currentStage];
        if (method_exists($this, $fn)) {
            return call_user_func_array([$this, $fn], func_get_args());
        }
        $class = get_class($this);
        $class = substr($class, 0, strrpos($class, '_Controller'));
        $ns = substr($class, 0, strrpos($class, '\\'));
        $name = substr($class, strrpos($class, '\\') + 1);
        $name = strtolower(str_replace('_', '/', $name));
        return $this->view("$ns\\page/$name/$fn", ['form' => $this->form]);
    }

    protected function post(array $args)
    {
        $check = 'post'.ucfirst(static::$stages[static::$currentStage]);
        if (!isset($_POST['act_previous'])
            && !$this->form->errors()
            && (!method_exists($this, $check) || $this->$check($args))
        ) {
            self::session()->set(
                'Form',
                $this->form->getArrayCopy() + self::session()->get('Form')
            );
            $this->nextStage();
            $this->setForm(static::$currentStage);
        }
        return $this->get($args);
    }

    public function __call($fn, $args)
    {
        $class = get_class($this);
        // Remove namespace, it's auto-appended.
        $class = substr($class, strrpos($class, '\\') + 1);
        $parts = array_reverse(explode('_', strtolower($class)));
        array_shift($parts); // Remove Controller.
        $view = implode('/', $parts).'/';
        return $this->view("page/$view$fn");
    }

    public function getCurrentStage()
    {
        return static::$currentStage;
    }

    protected function nextStage($steps = 1)
    {
        $mod = $steps > 0 ? 1 : -1;
        try {
            static::$currentStage = (string)static::$currentStage;
            for ($i = 0; $i < abs($steps); $i++) {
                static::$currentStage += $mod;
            }
            static::$stages[(string)static::$currentStage];
            $this->form['__stage']->value = static::$currentStage;
        } catch (ErrorException $e) {
        }
    }

    protected function previousStage($steps = 1)
    {
        $this->nextStage(-$steps);
    }

    protected function cancel()
    {
        $this->text = $this->text;
        self::message()->add(Message::INFO, $this->text('./cancelled'));
        throw new HTTP301_Exception('/');
    }
}

