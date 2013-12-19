<?php

/**
 * @package monolyth
 * @subpackage render
 */

namespace monolyth\render;
use monolyth\adapter\sql\Resultset;

class Paginator
{
    const QUERY = 1;
    const URL = 2;

    use Url_Helper {
        Url_Helper::url as helper;
    }
    
    protected $objects = [];
    private $currentPage;
    private $nextPage;
    private $lastPage;
    private $totalPages;
    private $url;
    private $args = [];
    private $params = [];

    public function init($url, array $args, Resultset $model, $params = null)
    {
        $this->url = $url;
        $this->args = $args;
        $this->params = isset($params) ? $params : [];
        $this->push($model);
        return $this;
    }

    public function push(Resultset $model)
    {
        $this->objects[] = $model;
    }

    public function current()
    {
        if (isset($this->currentPage)) {
            return $this->currentPage;
        }
        $this->currentPage = 0;
        foreach ($this->objects as $object) {
            $this->currentPage = max(
                $this->currentPage,
                $object->getCurrentPage()
            );
        }
        return $this->currentPage;
    }

    public function first()
    {
        return 1;
    }

    public function previous()
    {
        $current = $this->current();
        if ($current > 1) {
            return $current - 1;
        }
        return null;
    }

    public function next()
    {
        $next = $this->current();
        foreach ($this->objects as $object) {
            if ($object->getNextPage()) {
                return $next + 1;
            }
        }
        return null;
    }

    public function last()
    {
        if (isset($this->lastPage)) {
            return $this->lastPage;
        }
        $this->lastPage = 1;
        foreach ($this->objects as $object) {
            $this->lastPage = max(
                $this->lastPage,
                $object->getLastPage()
            );
        }
        return (int)$this->lastPage;
    }

    public function url(array $args = [], $page = 1, $format = self::QUERY)
    {
        $args += $this->args;
        $params = isset($this->params) ? $this->params : [];
        if ($format == self::QUERY) {
            $params['page'] = $page;
        } else {
            $args['page'] = $page;
        }
        $url = call_user_func([$this, 'helper'], $this->url, $args);
        if ($params) {
            $url .= strpos($url, '?') ? '&amp;' : '?';
            $url .= http_build_query($params, '', '&amp;');
        }
        return $url;
    }
}

