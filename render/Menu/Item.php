<?php

namespace monolyth\render;
use monad\Page_Model;
use monad\Menuitem_Model;
use ArrayObject;
use ErrorException;

class Item_Menu extends ArrayObject
{
    use Url_Helper;

    public $uri;

    const STATUS_HIDDEN = 1;
    const STATUS_HOME = Page_Model::STATUS_HOME;
    const STATUS_BLANK = Menuitem_Model::STATUS_BLANK;

    public function build(array $data)
    {
        $that = clone $this;
        $uri = null;
        if (isset($data['page'])) {
            if ($data['pagestatus'] & self::STATUS_HOME) {
                $uri = $this->url('');
            } else {
                $uri = $this->url(
                    'monad/static_page',
                    ['slug' => $data['slug'], 'language' => $data['language']]
                );
            }
        } elseif (isset($data['link'])) {
            $options = ['language' => $data['language']];
            try {
                if ($add = json_decode($data['i18nparams'], true)) {
                    $options += $add;
                }
            } catch (ErrorException $e) {
            }
            try {
                if ($add = json_decode($data['params'], true)) {
                    $options += $add;
                }
            } catch (ErrorException $e) {
            }
            if (!($uri = $this->url($data['link'], $options))) {
                // Fallback; assume literal link.
                $uri = $data['link'];
                if (isset($data['params'])) {
                    $uri .= $data['params'];
                }
                if (isset($data['i18nparams'])) {
                    $uri .= $data['i18nparams'];
                }
            }
        }
        $that->uri = $uri;
        $that->txt = $data['title'];
        $that->target = isset($data['status'])
            && $data['status'] & self::STATUS_BLANK ?
                ' target="_blank"' :
                '';
        $that->__construct($data);
        return $that;
    }

    public function __toString()
    {
        return $this->uri;
    }
}

