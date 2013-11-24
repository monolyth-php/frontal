<?php

/**
 * @package monolyth
 * @subpackage render
 */

namespace monolyth\render;

class Reversed_Paginator extends Paginator
{
    public function getCurrentPage()
    {
        return parent::getLastPage() - parent::getCurrentPage() + 1;
    }

    public function getPreviousPage()
    {
        $next = $this->getCurrentPage();
        foreach ($this->objects as $object) {
            if ($object->getPreviousPage()) {
                return $next + 1;
            }
        }
        return null;
    }

    public function getNextPage()
    {
        return parent::getPreviousPage();
    }

    public function getFirstPage()
    {
        return parent::getLastPage();
    }

    public function getLastPage()
    {
        return parent::getFirstPage();
    }
}

