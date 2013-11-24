<?php

/**
 * A (very basic) default controller to render the public view of a user's
 * account. Out of the box monolyth offers very little information (it's always
 * custom) so you'll want to override this in your project.
 */
namespace monolyth\account;
use monolyth\Controller;
use monolyth\model\Auth;
use monolyth\adapter\sql\NoResults_Exception;
use monolyth\HTTP404_Exception;

class Public_Controller extends Controller
{
    protected function get(array $args)
    {
        extract($args);
        try {
            $profile = Auth::getUserById($id);
        } catch (NoResults_Exception $e) {
            throw new HTTP404_Exception();
        }
        return $this->view('page/public', compact('profile'));
    }
}

