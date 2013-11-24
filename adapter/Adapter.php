<?php

namespace monolyth\adapter;

interface Adapter
{
    /**
     * Constants for aiding in interval statements.
     * {{{
     */
    const YEAR = 1;
    const MONTH = 2;
    const WEEK = 3;
    const DAY = 4;
    const HOUR = 5;
    const MINUTE = 6;
    const SECOND = 7;
    /** }}} */

    public function field($table, $field, $where = null, $options = null);
    public function row($table, $field, $where = null, $options = null);
    public function rows($table, $field, $where = null, $options = null);
}

