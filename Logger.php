<?php

namespace monolyth;
use StdClass;

class Logger
{
    use core\Singleton;

    private $entries = [];
    private $start;

    protected function __construct()
    {
        $this->start = microtime(true);
    }

    public function log($msg, $start = null)
    {
        $logtime = microtime(true) - $this->start;
        $memory = memory_get_usage(true);
        $data = new StdClass;
        $data->msg = $msg;
        $data->time = sprintf('%0.4f', $logtime);
        $data->memory = sprintf('%0.2fmb', $memory / 1024 / 1024);
        $data->duration = isset($start) ?
            sprintf('%0.4f', $logtime - ($start - $this->start)) :
            '?';
        $this->entries[] = $data;
    }

    public function export()
    {
        return $this->entries;
    }
}

