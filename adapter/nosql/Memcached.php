<?php

namespace monolyth\adapter\nosql;
use monolyth;
use Memcached as Base;
use monolyth\Project_Access;

class Memcached implements Cache, Adapter, Project_Access
{
    private static $stats = [], $time = 0, $prefix;

    public function __construct($prefix, array $settings)
    {
        $this->mc = new Base();
        $servers = [];
        foreach ($settings as $server) {
            $servers[$server[0]] = $server;
        }
        foreach ($this->mc->getServerList() as $server) {
            if (isset($servers[$server['host']])) {
                unset($servers[$server['host']]);
            }
        }
        $this->mc->addServers($servers);
        $this->prefix = $prefix;
    }

    private function key($key)
    {
        return "{$this->prefix}/$key";
    }

    public function get($key)
    {
        $key = $this->key($key);
        $start = microtime(true);
        $return = $this->mc->get($key);
        if ($return === false
            && $this->mc->getResultCode() == Base::RES_NOTFOUND
        ) {
            throw new KeyNotFound_Exception($key);
        }
        self::$time += (microtime(true) - $start);
        return $return;
    }

    public function set($key, $value, $expiration = 0)
    {
        $key = $this->key($key);
        return $this->mc->set($key, $value, $expiration);
    }

    public function delete($key)
    {
        $key = $this->key($key);
        $result = $this->mc->delete($key);
        if (!$result && $this->mc->getResultCode() == Base::RES_NOTFOUND) {
            throw new KeyNotFound_Exception($key);
        }
        return $result;
    }

    public function stats()
    {
        return [
            'total' => self::$stats,
            'time' => self::$time,
        ];
    }
}

