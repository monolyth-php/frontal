<?php

/**
 * The following is needed to let HTMLPurifier keep Flash objects, which isn't
 * the default behaviour. Of course, Flash is evil and all, but sometimes it
 * can't be avoided, unfortunately.
 */

/**
 * Make sure the filter either gets autoloaded, of require it yourself. E.g.:
 * require_once 'monolyth/3rdparty/HTMLPurifier/Filter.php';
 * 
 * @see HTMLPurifier_Filter
 */

class HTMLPurifier_Filter_Flash extends HTMLPurifier_Filter
{
    public $name = 'YouTube';
    
    public function preFilter($html, $config, $context)
    {
        return preg_replace_callback(
            '@<object.*?</object>@ms',
            function($matches) {
                return '<span class="_tmp_flash">'.
                    base64_encode($matches[0]).'</span>';
            },
            $html
        );
     }

     public function postFilter($html, $config, $context)
     {
        return preg_replace_callback(
            '@<span class="_tmp_flash">(.*?)</span>@ms',
            function($matches) {
                return base64_decode($matches[1]);
            },
            $html
        );
     }
}

