<?php

/**
 * The base Controller that other controllers should extend.
 *
 * The base Controller provides default controller functionality.
 * Note that you should use custom controllers for your project;
 * e.g. MyController extends Controller.
 *
 * @package monolyth
 * @subpackage core
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2008, 2009, 2010, 2011, 2012, 2013, 2014
 */
namespace monolyth\core;
use monolyth\render\View;
use monolyth\render\FileNotFound_Exception;
use monolyth\DependencyContainer;
use monolyth\account\Logout_Controller;
use monolyth\Ajax_Required;
use monolyth\HTTP301_Exception;
use monolyth\HTTP400_Exception;
use monolyth\HTTP403_Exception;
use monolyth\HTTP404_Exception;
use monolyth\render\HTTP404_Controller;
use monolyth\HTTP405_Exception;
use monolyth\Redirect_Controller;
use monolyth\Message_Access;
use monolyth\User_Access;
use monolyth\Project_Access;
use monolyth\Language_Access;
use monolyth\Session_Access;
use monolyth\HTTP_Access;
use monolyth\render\Viewable;
use monolyth\utils\Translatable;
use monolyth\utils\Name_Helper;
use monolyth\render\Url_Helper;
use monolyth\account\Must_Activate_Controller;
use monolyth\account\Request_Re_Activate_Controller;
use monolyth\account\Auto_Login_Model;
use StdClass;
use ErrorException;
use monolyth\Monolyth;
use monolyth\Trait_Helper;
use monolyth\Message;
use monolyth\Text_Model;

abstract class Controller
{
    use Language_Access;
    use Translatable;
    use Viewable;
    use Name_Helper;
    use Url_Helper;
    use User_Access;
    use Message_Access;
    use Project_Access;
    use HTTP_Access;
    use Session_Access;

    private $end = false, $start = 0,
        $attachments = [], $requirements = [], $arguments = [];
    protected $template = null, $parentUrl;
    public $form = null;

    /**
     * Magic constructor; sets up a bunch of stuff. Note that extended
     * controllers with a custom constructor should always call this
     * manually (or stuff will break all over the place).
     */
    public function __construct()
    {
        Monolyth::setBookmark('Initialising controller');
        $traits = [];
        $accessMethods = [];
        foreach (class_parents($this) + ['me' => $this] as $parent) {
            $traits += class_uses($parent);
        }
        $methods = [];
        foreach ($traits as $trait) {
            $classmethods = get_class_methods($trait);
            $methods = array_merge($methods, $classmethods);
            if (substr($trait, -6) == 'Access') {
                $accessMethods = array_merge($accessMethods, $classmethods);
            }
        }
        $attach = [];
        foreach (get_class_methods($this) as $method) {
            if (in_array($method, $accessMethods)) {
                $attach += [$method => $this->$method()];
            } elseif (in_array($method, $methods)) {
                $attach += [$method => function() use($method) {
                    return call_user_func_array(
                        [$this, $method],
                        func_get_args()
                    );
                }];
            }
        }

        // Default to home page for parentUrl.
        $this->parentUrl = $this->url('');

        // Auto-attach the view method, as well as helpers that (for technical
        // reasons) are objects instead of traits.
        $attach['view'] = function($f, $a = [], $c = null) {
            return call_user_func($this->view($f, $a), $c);
        };
        foreach ($this as $key => $obj) {
            if (is_object($obj) && $obj instanceof Trait_Helper) {
                foreach (get_class_methods($obj) as $method) {
                    $attach[$method] = function() use($obj, $method) {
                        return call_user_func_array(
                            [$obj, $method],
                            func_get_args()
                        );
                    };
                }
            }
        }
        $this->attach($attach);

        $this->start = microtime(true);
        if (isset($_COOKIE['monolyth_persist'])
            && !self::user()->loggedIn()
            && !$this instanceof Logout_Controller
        ) {
            call_user_func(new Auto_Login_Model, $_COOKIE['monolyth_persist']);
        }
        if (isset($_GET['sid'])
            && !($this instanceof Redirect_Controller
             || $this instanceof HTTP404_Controller
        )) {
            $new = self::http()->url(true);
            $q = self::http()->query();
            $q = preg_replace("@&?sid={$_GET['sid']}@", '&', $q);
            if ($q == '&') {
                $q = '';
            }
            if ($q) {
                $new .= "?$q";
            }
            throw new HTTP301_Exception($new);
        }

        // Add default Monolyth requirements.
        $user = self::user();
        $project = self::project();
        $redir = self::http()->getRedir();
        $http = self::http();
        $this->addRequirement(
            'monolyth\Login_Required',
            $user->loggedIn(),
            function() use($project, $redir) {
                throw new HTTP301_Exception(
                    $this->url('monolyth/account/login')
                   .'?redir='.urlencode($redir)
                );
            }
        );
        $this->addRequirement(
            'monolyth\Nologin_Required',
            !$user->loggedIn(),
            function() { throw new HTTP301_Exception($this->url('')); }
        );
        $this->addRequirement(
            'monolyth\Logout_Required',
            !$user->loggedIn(),
            function() use($user) {
                $user->logout();
            }
        );
        $this->addRequirement(
            'monolyth\Active_Required',
            !($this instanceof Must_Activate_Controller
                || $this instanceof Request_Re_Activate_Controller
            )
                && !($user->status() & $user::STATUS_INACTIVE),
            function() use($redir, $user) {
                $page = $user->status() & $user::STATUS_ACTIVATE ?
                    'must_activate' :
                    'request_re_activate';
                throw new HTTP301_Exception(
                    $this->url("monolyth/account/$page")
                   .'?redir='.urlencode($redir)
                );
            }
        );
        $this->addRequirement(
            'monolyth\Inactive_Required',
            $user->loggedIn() && $user->status() & $user::STATUS_INACTIVE,
            function() use($user) {
                self::message()->add(
                    Message::INFO,
                    $this->text('./noneed')
                );
                throw new HTTP301_Exception($this->url('monolyth/account'));
            }
        );
        $this->addRequirement(
            'monolyth\Enabled_Required',
            !($user->status() & $user::STATUS_DISABLED),
            function() use($redir) {
                throw new HTTP301_Exception(
                    $this->url('monolyth/account/disabled')
                   .'?redir='.urlencode($redir)
                );
            }
        );
        $this->addRequirement(
            'monolyth\Ajax_Required',
            $http->isXMLHttpRequest(),
            function() {
                if (isset($this->parentUrl)) {
                    throw new HTTP301_Exception($this->parentUrl);
                } else {
                    throw new HTTP400_Exception;
                }
            }
        );
        $this->addRequirement(
            'monolyth\Test_Required',
            $project['test'],
            function() {
                throw new HTTP404_Exception;
            }
        );
    }

    public function arguments(array $override = [])
    {
        return $override + $this->arguments;
    }

    /**
     * "Requirements" are special interfaces extending monolyth\Required.
     * If $check is or returns false, $action is called by checkRequirements.
     */
    protected function addRequirement($implements, $check, $action)
    {
        $this->requirements[$implements] = [$check, $action];
    }

    /**
     * Check all registered requirements.
     */
    private function checkRequirements()
    {
        $implements = class_implements($this);
        foreach ($implements as $interface) {
            if (!array_key_exists(
                'monolyth\core\Required',
                class_implements($interface)
            )) {
                continue;
            }
            if (isset($this->requirements[$interface])) {
                if ((is_callable($this->requirements[$interface][0])
                        && !call_user_func($this->requirements[$interface][0])
                    )
                    || !$this->requirements[$interface][0]
                ) {
                    call_user_func($this->requirements[$interface][1]);
                }
            }
        }
    }

    /**
     * "Invoke" the controller. This kicks off the actual work by passing
     * the arguments on to the correct action-method (get, post, put or
     * delete). This might in turn throw a HTTP40xException if the current
     * controller does not support the method in question.
     *
     * @param string $method The method (GET, POST, PUT or DELETE).
     * @param array $arguments Arguments passed from the URI.
     */
    public function __invoke($method, array $arguments)
    {
        // Before we do ANYTHING, check our requirements...
        $this->arguments = $arguments;
        $this->checkRequirements();
        if (isset($arguments['language'])
            && self::language()->isAvailable($arguments['language'])
        ) {
            try {
                setcookie(
                    'language',
                    $arguments['language'],
                    time() + 60 * 60 * 24 * 365,
                    '/',
                    self::project()['cookiedomain']
                );
            } catch (ErrorException $e) {
            }
            $_COOKIE['language'] = $arguments['language'];
        }
        if (!(isset($this->template) || $this instanceof Ajax_Required)) {
            if (isset($_GET['html5history'])) {
                $this->template = $this->view(
                    'monolyth\template/history',
                    ['parse' => true]
                );
            } else {
                try {
                    $this->template = $this->view([
                        '\template/body',
                        'monolyth\template/page',
                    ]);
                } catch (FileNotFound_Exception $e) {
                    $this->template = $this->view([
                        'monolyth\template/body',
                        'monolyth\template/page',
                    ]);
                }
            }
        }
        switch (strtoupper($method)) {
            case 'POST':
//                if (!self::http()->isValidPost()) {
//                    throw new HTTP403_Exception();
//                }
                if (isset($this->form)
                    and $this->form
                    and !$this->form->cancelled()
                    and isset($_POST['act_submit'])
                    and $errors = $this->form->errors($this->form->validate())
                ) {
                    // Automatically add error messages based on form errors.
                    $test = [get_class($this->form), get_class($this)];
                    $self = $this;
                    $text = new Text_Model($this);
                    $fn = function($field, $err) use ($test, $text) {
                        $opts = [];
                        foreach ($test as $option) {
                            $try = strtolower(str_replace(
                                '_',
                                DIRECTORY_SEPARATOR,
                                $this->merge($field, $option)
                            ));
                            if ($text->exists($try)) {
                                $label = $text->get($try);
                            } else {
                                $label = $field;
                            }
                            $opts[] = "$try/error.$err";
                            $try = substr($try, 0, strrpos($try, '/'));
                            $opts[] = "$try/error.$err";
                        }
                        return $text->get($opts, $label);
                    };
                    foreach ($errors as $field => $err) {
                        self::message()->add(
                            Message::ERROR,
                            $fn($field, $err)
                        );
                    }
                }
            case 'GET':
            case 'PUT':
            case 'DELETE':
                break;
            default:
                throw new HTTP405_Exception;
        }
        $view = call_user_func([$this, $method], $arguments);
        if (strtoupper($method) == 'GET'
            and $view instanceof View
            and $time = $view->getModifiedTime($view->data())
            and !$this->checkExpiry(
                $time,
                md5($time.self::http()->url().serialize(self::session()->all()))
            )
        ) {
            return;
        }
        if ($template = $this->template and $template) {
            $redir = $this->url('');
            if (isset($_GET['redir'])) {
                $redir = urldecode($_GET['redir']);
            } elseif (isset($_SERVER['HTTP_REFERER'])) {
                $redir = $_SERVER['HTTP_REFERER'];
            }
            $html = $view();
            $data = ($view instanceof View ? $view->data() : []);
            if (!isset($data['keywords'])) {
                $data['keywords'] = [];
            } elseif (!is_array($data['keywords'])) {
                $data['keywords'] = explode(', ', $data['keywords']);
            }
            if (!isset($data['description'])) {
                $data['description'] = null;
            }
            if (!isset($data['title'])) {
                $data['title'] = [];
            }
            if (!is_array($data['title'])) {
                $data['title'] = [$data['title']];
            }
            $data['self'] = $this;
            if ($view instanceof View) {
                $view->data($data);
            }
            $template->data($data + ['parse' => true]);
            Monolyth::setBookmark('Rendering template');
            echo $template($html);
        } else {
            echo $view('', $view->data() + ['parse' => true]);
        }
    }

    /**
     * {{{
     * The following protected methods define actions to be taken when the
     * current controller is invoked.
     */

    /**
     * Called when the REQUEST_METHOD is GET.
     */
    protected function get(array $args)
    {
        $name = get_class($this);
        $name = substr($name, 0, strrpos($name, '_Controller'));
        $name = strtolower(implode(
            DIRECTORY_SEPARATOR,
            array_reverse(explode(
                '_',
                $this->stripNamespace($name)
            ))
        ));
        if (!$name) {
            $name = 'default';
        }
        try {
            return $this->view("page/$name");
        } catch (FileNotFound_Exception $e) {
            if ($this instanceof \Controller) {
                return $this->view('monolyth\page/default');
            }
            throw new HTTP404_Exception;
        }
    }

    /**
     * Called when the REQUEST_METHOD is POST. This defaults to simply
     * calling monolyth\core\Controller::get with the same arguments, since
     * usually a POST shouldn't generate an error if nothing happens. 
     */
    protected function post(array $args)
    {
        return call_user_func_array([$this, 'get'], func_get_args());
    }

    /**
     * Called when the REQUEST_METHOD is PUT. Throw an HTTP405_Exception (method
     * not allowed) by default.
     */
    protected function put(array $args)
    {
        throw new HTTP405_Exception;
    }

    /**
     * Called when the REQUEST_METHOD is DELETE. Throw an HTTP405_Exception
     * (method not allowed) by default.
     */
    protected function delete(array $args)
    {
        throw new HTTP405_Exception;
    }
    /** }}} */

    public function debug()
    {
        // todo: styling
        $this->end = microtime(true);
        print '<div>'.N;
        printf(
            "<p>Page generated in %0.4f seconds</p>\n",
            $this->end - $this->start
        );
        $stats = DB::stats();
        printf(
            "<p>Executed %d queries in %0.4f seconds</p>\n",
            $stats['total'],
            $stats['time']
        );
        print '</div>'.N;
    }

    protected function checkExpiry($date, $etag)
    {
        if (!isset($_SERVER['REQUEST_METHOD'])
            || $_SERVER['REQUEST_METHOD'] != 'GET'
        ) {
            header("Pragma: no-cache");
            header("Cache-Control: no-cache");
            return 1;
        }
        header("Etag: $etag");
        if (isset($_SERVER['HTTP_IF_NONE_MATCH'])
            && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag
        ) {
            header('HTTP/1.1 304 Not Modified', true, 304);
            return null;
        }
        header("Cache-Control: ".(60*60*24));
        header("Expires: ".date('r', strtotime('+1 day')), true);
        return 1;
    }

    protected function attach(array $args)
    {
        $this->attachments = $args + $this->attachments;
    }

    protected function getAttachment($name)
    {
        return $this->attachments[$name];
    }

    /**
     * Return all defined attachments. This is handy for when you're injecting
     * HTML generated not from a View (but from some other library) into a
     * Monolyth template-view.
     *
     * @return array An array of attachments.
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    protected function purgeAttachments($key)
    {
        foreach (func_get_args() as $key) {
            if (isset($this->attachments[$key])) {
                unset($this->attachments[$key]);
            }
        }
    }

    protected function scriptTexts(array $ids)
    {
        $texts = array_flip($ids);
        foreach ($texts as $id => &$text) {
            $text = [$id, $this->language->current->code];
        }
        $this->text->load($texts);
        foreach ($texts as $id => &$text) {
            $text = [
                $id,
                $this->text->retrieve($id, $this->language->current->code)
            ];
        }
        return array_values($texts);
    }
}

