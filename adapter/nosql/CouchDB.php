<?php

namespace monolyth\adapter\nosql;
use ErrorException;

class CouchDB
{
    private $host = '127.0.0.1',
            $port = 5984,
            $db,
            $user = null,
            $pass = null;
    private static $stats = [], $time = 0;

    public function __construct($database, array $options = [])
    {
        $this->db = $database;
        foreach ($options as $key => $value) {
            $this->$key = $value;
        }
        $response = $this->send('GET', '/_all_dbs');
        $response = $this->send('PUT', "/{$this->db}");
        var_dump($response);
        var_dump($this->all());
    }

    public function all()
    {
        return $this->send('GET', "/{$this->db}/_all_docs");
    }

    private function send($method, $url, $post_data = null, $view = null)
    {
        $this->connect();
        $request = "$method $url HTTP/1.0\r\nHost: http://{$this->host}\r\n";
        if ($this->user) {
            $request .= "Authorization: Basic "
                .base64_encode("{$this->user}:{$this->pass}")."\r\n";
        }
        if ($post_data) {
            $post_data = json_encode($post_data);
            $request .= "Content-Length: ".strlen($post_data)."\r\n";
            $request .= "Content-Type: application/json\r\n\r\n";
            $request .= "$post_data\r\n";
        } else {
            $request .= "\r\n";
        }
        fwrite($this->handle, $request);
        $response = '';
        while (!feof($this->handle)) {
            $response .= fgets($this->handle);
        }
        list($headers, $body) = explode("\r\n\r\n", $response);
        $body = json_decode($body, true);
        if (isset($body['error'], $body['reason'])
            && $body['error'] == 'not_found'
            && $body['reason'] == 'missing'
        ) {
            throw new KeyNotFound_Exception($url);
        }
        return $body;
    }

    public function setup($dsn, $id)
    {
        $dsn = adapter\DB::parseDSN($dsn);
        $this->host = $dsn['hostspec'];
        $this->port = $dsn['port'];
        $this->user = $dsn['username'];
        $this->pass = $dsn['password'];
        $this->db = $dsn['database'];
    }

    public function get($key, $view = null)
    {
        return $this->send('GET', "/{$this->db}/$key", $view);
    }

    public function set($key, $value)
    {
        // Attempt to get the revision for this document.
        try {
            $curr = $this->get($key);
            $value['_rev'] = $curr['_rev'];
        } catch (KeyNotFound_Exception $e) {
            // New document; continue anyway.
        }
        return $this->send('PUT', "/{$this->db}/$key", $value);
    }

    public function delete($key)
    {
        return $this->send('DELETE', "/{$this->db}/$key");
    }

    public function stats()
    {
        return [
            'total' => self::$stats,
            'time' => self::$time,
        ];
    }

    public function createDatabase($name)
    {
        return $this->send('PUT', "/$name/");
    }

    private function connect()
    {
        try {
            $this->handle = fsockopen(
                $this->host,
                $this->port,
                $errno,
                $errstr
            );
            if ($errno) {
                throw new \ErrorException("$errno $errstr");
            }
        } catch (ErrorException $e) {
            echo $e->getMessage();
            die('connection failed');
        }
    }
}

