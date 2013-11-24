<?php

namespace monolyth\adapter;

class Logger
{
    private static $logged = ['total' => [], 'time' => 0];

    public function log($statement, $time)
    {
        self::$logged['total'][] =sprintf('(%0.4f) ', $time).$statement;
        self::$logged['time'] += $time;
    }

    public function export()
    {
        return self::$logged;
    }
}

