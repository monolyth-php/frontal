<?php

namespace monolyth\core;
use ArrayObject;
use InvalidArgumentException;
use ErrorException;
use monolyth\render\FileNotFound_Exception;

abstract class Project extends ArrayObject
{
    use Singleton;

    /**
     * The static (secure) domain you want to use (e.g. staticexample.com).
     */
    protected $staticDomain, $staticSecureDomain;
    /**
     * The static (secure) http servers you want to use (e.g. ['www1', 'www2']
     * for www1.staticexample.com and www2.staticexample.com).
     */
    protected $staticServers = [], $staticSecureServers = [];
    /**
     * Default stylesheets and scripts.
     *
     * {{{
     */
    protected $styles = ['*' => ['project.css']];
    protected $scripts = [];
    /** }}} */
    /**
     * Compacter script to use (e.g. yui-compressor). Leave to null for
     * "no compacting".
     */
    protected $compacter = null;
    /**
     * Cat-command to use. On Linux systems, you should just use the binary
     * 'cat' command. Monolyth provides a fallback cat-ish script in ./bin.
     */
    protected $cat;
    /**
     * Variables to use in this Project. Defaults to ./output/%s/variables.php.
     */
    protected $variables;
    
    public function __construct()
    {
        if (!isset($this->cat)) {
            $this->cat = realpath(__DIR__).'/bin/cat';
        }
        /**
         * Project is optimized for mobile viewing (default: true).
         */
        $this['mobile-optimized'] = true;
        /**
         * Maximum length of generated URIs (slugs etc.). 255 is a sane default,
         * but can be overrided if necessary. Please note you should also change
         * table definitions in that case.
         */
        $this['maxUrlLength'] = 255;
        /**
         * Salt used for the auto-login cookie.
         * You'll probably want to override this for security reasons.
         */
        $this['autoLoginSecret'] = 'fg56u%$GREG45tfeed35$TFh78K^(MUYhe';
        /**
         * Whether to use "fancy" rewritten URLs. In its current version,
         * monolyth support for "unfancy" URLs is sketchy at best, though.
         */
        $this['useModRewrite'] = true;
        /**
         * True if we're using https (or at least, are pretending to).
         * Default to false, this gets set later on depending on what our router
         * thinks we're up to.
         */
        $this['secure'] = false;
        /**
         * True if we're running from the command line, otherwise false.
         * Default to false, this gets set dynamically later on.
         */
        $this['cli'] = false;
        /**
         * False if we're in production environment, true if in development.
         * Your own Project class should handle this, as every development
         * environment will be different.
         */
        $this['test'] = false;
        /**
         * Set to true to signal downtime of site (i.e., disable all database
         * operations).
         */
        $this['down'] = false;
        /**
         * Specify alternative domains for redirecting. For rewriting paths the
         * preferred method is to alias the controller in question.
         */
        $this['alternatives'] = [];
        /** Path to public root. */
        $this['public'] = null;
        /**
         * Path to private root (on simple setups, this will be the same as
         * Project::$public).
         */
        $this['private'] = null;
        /** Site name. */
        if (!isset($this['name'])) {
            $this['name'] = null;
        }
        /** Protocol for unsecure connections. */
        $this['protocol'] = 'http';
        /**
         * Protocol for secure connections. Though using a secure connection is
         * highly recommended for sensitive data (especially passwords), most
         * setups don't offer it by default.
         */
        $this['protocols'] = 'http';
        $this['cli'] = substr(PHP_SAPI, 0, 3) == 'cli';
        if (!$this['cli'] && isset($_SERVER['SERVER_NAME'])) {
            if (!isset($this['site'])) {
                $this['site'] = preg_replace(
                    '/(^secure\.|\W)/',
                    '',
                    $_SERVER['SERVER_NAME']
                );
            }
            $this['url'] = $_SERVER['SERVER_NAME'];
            /** Full name of http server. */
            $this['http'] = "{$this['protocol']}://{$_SERVER['SERVER_NAME']}";
            /** Full name of https server. */
            $this['https'] = "{$this['protocols']}://{$_SERVER['SERVER_NAME']}";
        }
        /**
         * Domain to use for setting cookies. The assumption is that we will
         * ALWAYS have at least one subdomain (www.example.com,
         * secure.example.com etc.) and we can just strip those.
         *
         * Of course, if your situation differs (e.g. on local test) your own
         * Project class should override this. Also, this will obviously fail
         * on example.co.uk style domains.
         */
        try {
            $parts = explode('.', $_SERVER['SERVER_NAME']);
            while (count($parts) > 2) {
                array_shift($parts);
            }
            $this['cookiedomain'] = '.'.implode('.', $parts);
        } catch (ErrorException $e) {
            $this['cookiedomain'] = 'localhost';
        }
        /**
         * Override this in your project class. Various notification mails will
         * be sent to this address, whether on test or on production.
         * At the moment, this happens mainly when there are database problems,
         * but of course this might change in the future :)
         */
        $this['notifymail'] = 'someone@example.com';
    }

    public function merge($input)
    {
        if (!(is_array($input) || $input instanceof ArrayObject)) {
            throw new InvalidArgumentException();
        }
        foreach ($input as $key => $value) {
            $this[$key] = $value;
        }
        return $this;
    }

    public function export()
    {
        $out = new ArrayObject((array)$this);
        foreach ([
            'staticDomain',
            'staticSecureDomain',
            'staticServers',
            'staticSecureServers',
            'compacter',
            'cat',
            'variables',
        ] as $prop) {
            $out->$prop = $this->$prop;
        }
        return $out;
    }

    /**
     * If you're running on a multiple frontend setup (e.g., behind a
     * load-balancer) you can configure this method to synchronize the static
     * files of type $ext.
     *
     * @param string $ext The type of files to synchronize (js, css etc.).
     */
    protected function synchronize($ext)
    {
    }

    public function setCountry($url, $country)
    {
        if (preg_match(
            "@^https?://([a-z]{2})\.([a-z0-9\.-]+)/@",
            $url,
            $match
        )) {
            $country->set($match[1]);
        }
    }

    public function setLanguage($url, $language)
    {
        try {
            if (preg_match(
                "@^https?://[a-z0-9\.-]_+/(\w{2})/?@",
                $url,
                $match
            )) {
                $language->set($match[1]);
            }
        } catch (LanguageNotFound_Exception $e) {
        }
    }
}

