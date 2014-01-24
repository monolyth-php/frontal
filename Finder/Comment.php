<?php

namespace monolyth;
use monolyth\adapter\sql\NoResults_Exception;
use Adapter_Access;

class Comment_Finder implements Finder
{
    use Adapter_Access;
    use core\Singleton;

    protected function __construct()
    {
        $this->config = Config::get('monolyth');
    }

    public function since($reference, $since)
    {
        try {
            return self::adapter()->rows(
                'monolyth_comment c',
                ['*'],
                [
                    'c.reference' => $reference,
                    'c.datecreated' => ['>' => $since]
                ]
            );
        }
        catch (NoResults_Exception $e) {
            return null;
        }
    }   
    
    public function all($id, $referer, $page = 1, $order = 'DESC')
    {
        $options = ['order' => "c.datecreated $order"];
        if ((int)$page < 1) {
            $page = 1;
        }
        if (isset($this->config, $this->config->comments_per_page)) {
            $options['limit'] = $this->config->comments_per_page;
            $options['offset'] = ($page - 1) * $options['limit'];
        }
        $where = ['reference' => $id];
        $fields = [
            'c.id AS commentid',
            'c.*',
            'a.*',
            'c.datecreated',
            'COALESCE(a.name, c.name) AS name',
            'r.*',
            'c.datecreated',
            'c.owner',
        ];
        try {
            return self::adapter()->pages(
                "monolyth_comment c
                 JOIN $referer r ON r.comments = c.reference
                 LEFT JOIN monolyth_auth a ON a.id = c.owner",
                $fields,
                $where + [sprintf(
                    "c.status & '%d'",
                    Comment_Model::STATUS_DELETED | Comment_Model::STATUS_HIDDEN
                ) => 0],
                $options
            );
        } catch (NoResults_Exception $e) {
            return null;
        }
    }
}

