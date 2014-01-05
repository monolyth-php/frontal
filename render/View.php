<?php

/**
 * Generic base view class.
 *
 * @package monolyth
 * @subpackage render
 */
namespace monolyth\render;
use monolyth;
use monolyth\core;
use monolyth\utils\Name_Helper;
use monolyth\Project_Access;
use monolyth\User_Access;
use monolyth\Logger_Access;
use monolyth\adapter\sql;
use ErrorException;

final class View
{
    use Name_Helper;
    use User_Access;
    use Project_Access;
    use Logger_Access;

    private $controller, $files = [], $parsers = [];
    private static $data = [];

    public function __construct($file, core\Controller $controller = null)
    {
        $this->controller = $controller;
        if (!is_array($file)) {
            $file = [$file];
        }
        foreach ($file as $f) {
            $this->chain($f);
        }
    }

    public function getModifiedTime($data)
    {
        if (!isset($_SERVER['REQUEST_METHOD'])
            || $_SERVER['REQUEST_METHOD'] != 'GET'
        ) {
            return time();
        }
        $time = 0;
        if ($data instanceof sql\Resultset || is_array($data)) {
            try {
                foreach ($data as $key => $sub) {
                    if ($result = $this->getModifiedTime($sub)
                        and $result > $time
                    ) {
                        $time = $result;
                    }
                }
                return $time ? $time : null;
            } catch (ErrorException $e) {
                return null;
            }
        } elseif ($data instanceof sql\Result) {
            if (isset($data['datemodified'])) {
                return strtotime($data['datemodified']);
            }
            if (isset($data['datecreated'])) {
                return strtotime($model['datecreated']);
            }
            foreach ($data as $row) {
                if (!is_numeric($row) and false !== ($time = strtotime($row))) {
                    return $time;
                }
            }
        }
        return null;
    }

    public function __invoke($content = null, array $args = [])
    {
        if (isset($args['language'])
            && !($args['language'] instanceof Language_Model)
        ) {
            unset($args['language']);
        }
        if (!$args) {
            $args = self::$data;
        }
        if (!isset($args['self'])) {
            $args['self'] = $this->controller;
        }
        if (!isset($args['self'])) {
            $args['self'] = $this;
        }
        if (!isset($args['project'])) {
            $args['project'] = self::project();
        }
        if (!isset($args['form']) && isset($this->controller->form)) {
            $args['form'] = $this->controller->form;
        }
        foreach ($this->files as $file) {
            Monolyth::setBookmark("Rendering $file");
            $content = call_user_func(function() use($file, &$args, $content) {
                ob_start();
                $data = call_user_func(function($file, $args) use($content) {
                    extract($args);
                    return include $file;
                }, $file, $args);
                if ($data && is_array($data)) {
                    foreach ($data as $key => $value) {
                        if (array_key_exists($key, $args)
                            && is_null($args[$key])
                        ) {
                            unset($args[$key]);
                        }
                    }
                    $args = array_merge_recursive($args, $data);
                }
                return ob_get_clean();
            });
            self::$data = $args + self::$data;
        }
        return isset($args['parse']) && $args['parse'] ?
            $this->parse($content) :
            $content;
    }

    /**
     * Get or set internal variables to be exposed in the view.
     *
     * @param array $data Key/value hash of variables to pass.
     * @return array Hash of currently set variables.
     */
    public function data(array $data = [])
    {
        self::$data = $data + self::$data;
        return self::$data;
    }

    public function chain($file)
    {
        preg_match('@\.([a-z]+)$@', $file, $match);
        try {
            switch ($match[1]) {
                case 'php': case 'html': case 'js': case 'xml':
                    $ext = $match[1];
                    $file = str_replace($match[0], '', $file);
                    break;
                default:
                    throw new UnsupportedFiletype_Exception($match[1]);
            }
        } catch (ErrorException $e) {
            $ext = 'php';
        }
        try {
            list($type, $file) = explode(':', $file);
        } catch (ErrorException $e) {
            $type = 'html';
        }
        $file = $this->toFilename(
            $file,
            $this->controller,
            "%s/output/$type/%s.$ext"
        );
        try {
            file_get_contents($file, true);
        } catch (ErrorException $e) {
            throw new FileNotFound_Exception($file);
        }
        $this->files[] = $file;
        return $this;
    }

    public function files()
    {
        return $this->files;
    }

    /**
     * Call all defined parsers.
     *
     * The parsers are called at the end of outputting, and are meant to do
     * some last-minute generic replacement on your HTML.
     * Common operations include rewriting local URLs to use Controller::link,
     * replacing common words with links, escaping entities etc.
     * A number of default parsers are provided with Monolyth.
     * Modules may define additional parsers, and of course you can
     * define your own or override existing parsers by placing
     * identically named classes in a corresponding place
     * inside your include_path.
     *
     * Since parsers typically do a lot of regex-matching,
     * you should take care to not add too many of them.
     *
     * The parse-method also adds slices defined by the special
     * <slice:name> semi-HTML tags in your views.
     *
     * @param string $html The HTML to parse.
     * @return string Parsed HTML.
     * @see self::addParser
     */
    protected function parse($html)
    {
        Monolyth::setBookmark('Invoke all parsers');
        foreach ($this->parsers as $parser) {
            $p = $parser[0];
            $parser[0] = $html;
            $html = call_user_func_array([$p, '__invoke'], $parser);
        }
        // Remove superfluous whitespace, except within certain tags
        // (where whitespace is in fact significant).
        $html = preg_replace_callback(
            '@<(textarea|pre)(.*?)>(.*?)</\\1>@ms',
            function($match) {
                return sprintf(
                    '<%1$s%2$s>%3$s</%1$s>',
                    $match[1],
                    $match[2],
                    base64_encode($match[3]),
                    $match[1]
                );
            },
            $html
        );
        // The same goes for value attributes; leave these verbatim.
        $html = preg_replace_callback(
            '@<(.*?)(\w+?)="(.*?)"@ms',
            function($match) {
                return sprintf(
                    '<%1$s%2$s="%3$s"',
                    $match[1],
                    $match[2],
                    base64_encode($match[3])
                );
            },
            $html
        );
        $html = preg_replace('@[\040\t]{1,}@m', ' ', $html);
        $html = str_replace(["\n\r", "\r", "\n "], "\n", $html);
        $html = preg_replace('@\n{2,}@m', "\n", $html);
        $html = preg_replace_callback(
            '@<(textarea|pre)(.*?)>(.*?)</\\1>@ms',
            function($match) {
                return sprintf(
                    '<%1$s%2$s>%3$s</%1$s>',
                    $match[1],
                    $match[2],
                    base64_decode($match[3]),
                    $match[1]
                );
            },
            $html
        );
        $html = preg_replace_callback(
            '@<(.*?)(\w+?)="(.*?)"@ms',
            function($match) {
                return sprintf(
                    '<%1$s%2$s="%3$s"',
                    $match[1],
                    $match[2],
                    base64_decode($match[3])
                );
            },
            $html
        );
        $config = monolyth\Config::get('monolyth');
        if (self::project()['test']
            || (isset($_SERVER['REMOTE_ADDR'])
                && in_array($_SERVER['REMOTE_ADDR'], $config->debugIps)
                || self::user()->name() == 'root'
            )
        ) {
            Monolyth::setBookmark('End [finished outputting]');
            $debug = "\n<!--\n\n";
            foreach (Monolyth::getBookmarks() as $i => $bookmark) {
                if (!$i) {
                    $start = $bookmark[1];
                }
                $debug .= sprintf(
                    "%s: %0.4f seconds, %0.2fmb memory\n",
                    $bookmark[0],
                    $bookmark[1],
                    $bookmark[2] / 1024 / 1024
                );
            }
            $debug .= sprintf(
                "\n\nTOTAL: %0.4f seconds, %0.2fmb memory\n\n",
                $bookmark[1] - $start,
                $bookmark[2] / 1024 / 1024
            );
            $stats = self::logger()->export();
            $debug .= sprintf(
                "Queries: %d, time: %0.4f\n\n",
                count($stats['total']),
                $stats['time']
            );
            foreach ($stats['total'] as $sql) {
                $debug .= preg_replace("@\s+@m", ' ', $sql)."\n";
            }
            $debug .= "\n";
            $html = str_replace(
                '</body>',
                "$debug-->\n</body>",
                $html
            );
        }
        return preg_replace_callback(
            '@<title>(.*?)</title>@ms',
            function($match) {
                return '<title>'.strip_tags($match[1]).'</title>';
            },
            $html
        );
    }
    
    /**
     * Add a parser for later use.
     *
     * Parsers are objects that transform your HTML when publishing.
     * For instance, you could write a parser that transforms everything that
     * looks like an e-mail address into a mailto-link.
     *
     * @param Parser $parser The parser.
     * @param mixed $arg,... Optional arguments to pass to the parser.
     * @see monolyth\core\Parser
     */
    public function addParser(core\Parser $parser)
    {
        $parser->currentNamespace($this->getNamespace($this->controller));
        $this->parsers[] = func_get_args();
    }
}

