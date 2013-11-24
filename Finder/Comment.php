<?php

namespace monolyth;
use monolyth\adapter\sql\NoResults_Exception;

class Comment_Finder implements Finder, adapter\Access
{
    public function since($reference, $since)
    {
        try {
            return $this->adapter->rows(
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
        $comment = $this->comment;
        $status = $comment::STATUS_DELETED | $comment::STATUS_HIDDEN;
        try {
            return $this->adapter->pages(
                "monolyth_comment c
                 JOIN $referer r ON r.comments = c.reference
                 LEFT JOIN monolyth_auth a ON a.id = c.owner",
                $fields,
                $where + [sprintf(
                    "c.status & '%d'",
                    $status
                ) => 0],
                $options
            );
        } catch (NoResults_Exception $e) {
            return null;
        }
    }
}

