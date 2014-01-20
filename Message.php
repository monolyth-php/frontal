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
 * class Foo implements
 * {
 *     use Message_Access;
 *
 *     public function doSomething()
 *     {
 *         self::message()->add('success', 'Done!');
 *     }
 *
 *     public function showMessages()
 *     {
 *         if ($this->message->has('success', 'error')) {
 *             foreach ($this->message->get('success', 'error') as $message) {
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

    private static $messages = [];

    protected function __construct()
    {
        static $inited = false;
        if ($inited) {
            return;
        }
        if (!(self::session()->exists('Messages')
            && is_array(self::session()->get('Messages'))
        )) {
            self::session()->set('Messages', []);
        }
        self::$messages = self::session()->get('Messages');
        $inited = true;
    }

    public function add($type, $body)
    {
        if (!isset(self::$messages[$type])) {
            self::$messages[$type] = [];
        }
        $o =& self::$messages[$type][];
        $o = new StdClass();
        $o->type = $type;
        $o->body = $body;
        self::session()->set('Messages', self::$messages);
    }

    public function has($type = null)
    {
        $types = func_get_args();
        if (!$types) {
            foreach (self::$messages as $messages) {
                if (count($messages)) {
                    return true;
                }
            }
            return false;
        }
        return isset(self::$messages[$type]) && count(self::$messages[$type]);
    }

    public function get()
    {
        $types = func_get_args();
        $return = [];
        foreach (self::$messages as $type => &$messages) {
            if (!$types || in_array($type, $types)) {
                $return = array_merge($return, $messages);
                $messages = [];
            }
        }
        self::session()->set('Messages', self::$messages);
        return $return;
    }
}

