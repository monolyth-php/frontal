<?php

/**
 * @package monolyth
 */

/**
 * Message queue. Objects should implement the Message_Access interface.
 *
 * The Message-class is greatly rewritten as of Monolyth 0.34 in order to work
 * with the new dependency injection framework.
 *
 * In Monolyth, messages are defined as snippets of information that can be
 * "thrown" in random places (a controller, a model, a parser etc.) and that
 * need to be retrieved on output. The traditional method would have been to
 * bung them in a superglobal, but of course we hate superglobals. :)
 *
 * Examples:
 * <code>
 * <?php
 *
 * class Foo implements monolyth\Message_Access
 * {
 *     public function doSomething()
 *     {
 *         $this->message->add(self::MESSAGE_SUCCESS, 'Done!');
 *     }
 *
 *     public function showMessages()
 *     {
 *         $flag = self::MESSAGE_SUCCESS | self::MESSAGE_ERROR;
 *         if ($this->message->has($flag)) {
 *             foreach ($this->message->get($flag) as $message) {
 *                 printf(
 *                     '<div class="'.$message->type.'">'.$message->body.'</div>',
 *                     $message->type,
 *                     $message->body
 *                 );
 *             }
 *         } else {
 *             echo '<div>Nothing to report...</div>';
 *         }
 *     }
 * }
 *
 * ?>
 * </code>
 */

namespace monolyth;
use StdClass;

class Message
{
    use Session_Access;
    use core\Singleton;

    const SUCCESS = 1;
    const INFO = 2;
    const WARNING = 4;
    const ERROR = 8;

    private static $messages = [];
    private $types = [
        self::SUCCESS => 'success',
        self::INFO => 'info',
        self::WARNING => 'warning',
        self::ERROR => 'error',
    ];

    protected function __construct()
    {
        static $inited = false;
        if ($inited) {
            return;
        }
        if (!(self::session()->exists('Messages')
            && is_array(self::session()->get('Messages'))
        )) {
            self::session()->set(
                'Messages',
                [
                    self::SUCCESS => [],
                    self::INFO => [],
                    self::WARNING => [],
                    self::ERROR => [],
                ]
            );
        }
        self::$messages = self::session()->get('Messages');
        $inited = true;
    }

    public function add($flag, $body)
    {
        $o =& self::$messages[$flag][];
        $o = new StdClass();
        $o->code = $flag;
        $o->type = $this->types[$flag];
        $o->body = $body;
        self::session()->set('Messages', self::$messages);
    }

    public function has($flag = null)
    {
        if (!isset($flag)) {
            $flag = self::SUCCESS | self::INFO | self::WARNING | self::ERROR;
        }
        foreach (self::$messages as $mcode => $messages) {
            if ($flag & $mcode && count($messages)) {
                return true;
            }
        }
        return false;
    }

    public function get($flag = null)
    {
        if (!isset($flag)) {
            $flag = self::SUCCESS | self::INFO | self::WARNING | self::ERROR;
        }
        $return = [];
        foreach (self::$messages as $mcode => &$messages) {
            if ($flag & $mcode) {
                $return = array_merge($return, $messages);
                $messages = [];
            }
        }
        self::session()->set('Messages', self::$messages);
        return $return;
    }
}

