<?php

/**
 * @package monolyth
 */

namespace monolyth;

class Ajax_Redirect_Controller extends Redirect_Controller
{
    /**
     * Redirect to another location.
     *
     * Ajax scripts expecting a redirect can't use the Location header, since
     * that breaks the ajax-call. This controller instead returns a json-object
     * of the form {"redirect":"http://your-new-location"}. It's then up to
     * the handling script to do something useful with it :)
     *
     * @param string|null $url The URI to redirect to. Defaults to the current
     *                         location.
     * @param int $status The HTTP status code to pass.
     */
     protected function get(array $args)
     {
        $v = parent::get($args);
        header("HTTP/1.1 200 OK", true, 200);
        return $this->view('template/redirect/ajax', $v->data());
    }
}

