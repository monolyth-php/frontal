<?php

$examplecom = function() {
    return new monolyth\adapter\sql\MySQL(
        'mysql:host=localhost;dbname=examplecom',
        'user',
        'pass',
        [
            PDO::ATTR_PERSISTENT => true,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET names 'UTF8'",
        ]
    );
};
$_current = $examplecom();

