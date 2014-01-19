<?php

/**
 * @package monolyth
 * @subpackage render
 */

namespace monolyth\render;
use Adapter_Access;
use monolyth\utils\HTML_Helper;
use monolyth\Project_Access;
use ErrorException;
use Closure;
use Mail;
use Mail_mime;

class Email
{
    use Url_Helper;
    use HTML_Helper;
    use Adapter_Access;
    use Static_Helper;

    const TYPE_HTML = 1;
    const TYPE_PLAIN = 2;

    private $headers = [];
    private $variables = [];
    private $content = [];

    /**
     * Constructor. It auto-includes variables from your CSS.
     * Note: callables aren't allowed here.
     */
    public function __construct()
    {
        try {
            $vars = call_user_func(function() {
                include 'output/css/variables.php';
                return get_defined_vars();
            });
            foreach ($vars as $key => $var) {
                if (is_callable($var)) {
                    unset($vars[$key]);
                }
            }
            $this->setVariables($vars);
        } catch (ErrorException $e) {
        }
        $this->parser = new Translate_Parser;
        /** @see PEAR::Mail */
        require_once 'Mail.php';
        /** @see PEAR::Mail_mime */
        require_once 'Mail/mime.php';
        $this->mail = new Mail_mime([
            'head_charset' => 'UTF-8',
            'html_charset' => 'UTF-8',
            'text_charset' => 'UTF-8',
            'html_encoding' => 'quoted-printable',
            'text_encoding' => 'quoted-printable',
            'eol' => "\n",
        ]);
        $this->send = Mail::factory('mail');
    }

    /**
     * Set variables in an array of key/value pairs.
     * The keys are replaced using {$key} markers in the mails.
     *
     * @param array $variables Array of key/value pairs
     */
    public function setVariables(array $variables = [])
    {
        foreach ($variables as $key => $value) {
            $this->variables[$key] = $value;
        }
        return $this;
    }

    public function getVariable($name)
    {
        try {
            return $this->variables[$name];
        } catch (ErrorException $e) {
            return null;
        }
    }

    /**
     * @see Mail_mime::addAttachment
     */
    public function addAttachment(
        $file,
        $c_type      = 'application/octet-stream',
        $name        = '',
        $isfile      = true,
        $encoding    = 'base64',
        $disposition = 'attachment',
        $charset     = '',
        $language    = '',
        $location    = '',
        $n_encoding  = null,
        $f_encoding  = null,
        $description = ''
    )
    {
        return $this->mail->addAttachment(
            $file, $c_type, $name, $isfile, $encoding, $disposition,
            $charset, $language, $location, $n_encoding, $f_encoding,
            $description
        );
    }

    public function headers(array $headers = [])
    {
        $this->headers = $headers + $this->headers;
        return $this;
    }

    public function setSource($mail)
    {
        try {
            $data = self::adapter()->row(
                "monolyth_mail m
                 LEFT JOIN monolyth_mail_template t ON t.id = m.template
                    AND t.language = m.language",
                ['m.*', 't.html AS thtml', 't.plain AS tplain'],
                [
                    'm.id' => $mail,
                    'm.language' => self::language()->current->id,
                ]
            );
            if (!$data['thtml']) {
                $data['thtml'] = '{$content}';
            }
            if (!$data['tplain']) {
                $data['tplain'] = '{$content}';
            }
            foreach ([
                self::TYPE_HTML => 'html',
                self::TYPE_PLAIN => 'plain',
            ] as $id => $type) {
                $d = $data[$type];
                if ($id == self::TYPE_PLAIN) {
                    $d = html_entity_decode($d, ENT_COMPAT, 'UTF-8');
                    $d = strip_tags($d);
                }
                $this->content[$id] = str_replace(
                    '{$content}',
                    $d,
                    $data["t$type"]
                );
            }
            $this->headers['Subject'] = strip_tags($data['subject']);
            $this->headers['From'] = $data['sender'];
            $this->variables['subject'] = $data['subject'];
            $this->variables['from'] = $data['sender'];
        } catch (adapter\sql\NoResults_Exception $e) {
            $this->content[self::TYPE_HTML] =
            $this->content[self::TYPE_PLAIN] = '{$content}';
        }
        return $this;
    }

    /**
     * Send out the mail using all specified values.
     *
     * @param string $to The address to send to.
     */
    public function send($to)
    {
        $variables = $this->variables;
        foreach ($variables as $key => $value) {
            if (!is_string($value) && is_callable($value)) {
                // PHP can display some weird behaviour when $value happens to
                // contain a string matching a function name.
                try {
                    $variables[$key] = $value($this, $to);
                } catch (ErrorException $e) {
                }
            }
        }
        foreach ($this->content as $type => $content) {
            switch ($type) {
                case self::TYPE_HTML: $fn = 'setHTMLbody'; break;
                case self::TYPE_PLAIN:
                    if (preg_match("@(hotmail|live|msn)\.(com|nl)$@i", $to)) {
                        continue 2;
                    }
                    $fn = 'setTXTbody';
                    foreach ($variables as &$variable) {
                        $variable = $this->purify($variable);
                        $variable = $this->stripSmart($variable);
                        $variable = (string)$variable;
                    }
                    break;
            }
            // This is a horrible kludge, but do variable replacement twice
            // to allow variables-in-variables.
            // TODO: think of saner way to handle this...
            for ($i = 0; $i < 2; $i++) {
                foreach ($variables as $name => $value) {
                    if ($value instanceof Closure) {
                        $value = $value();
                    }
                    $content = str_replace('{$'.$name.'}', $value, $content);
                    $this->headers['Subject'] = str_replace(
                        '{$'.$name.'}',
                        $value,
                        $this->headers['Subject']
                    );
                    $this->headers['From'] = str_replace(
                        '{$'.$name.'}',
                        $value,
                        $this->headers['From']
                    );
                }
            }
            $replace = function($matches) {
                return str_replace(
                    $matches[1],
                    $this->httpimg($matches[1]),
                    $matches[0]
                );
            };
            foreach ([
                '@src="(/.*?)"@ms',
                '@url\([\'"]?(/.*?)[\'"]?\)@ms',
            ] as $regex) {
                $content = preg_replace_callback($regex, $replace, $content);
            }
            $content = call_user_func($this->parser, $content);
            $this->mail->$fn($content);
        }
        $body = $this->mail->get();
        if (self::staticProject()['test']) {
            if (isset(self::staticProject()['testmail'])) {
                $to = self::staticProject()['testmail'];
            } else {
                $to = null;
            }
        }
        if (isset($to)) {
            $this->send->send(
                $to,
                $this->mail->headers($this->headers),
                $body
            );
        }
        return $this;
    }
}

